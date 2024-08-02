<?php

namespace App\Jobs;

use App\Models\Media;
use App\Events\ImportMedia;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    /**
     * Create a new job instance.
     */
    public function __construct($path)
    {
        $this->path = storage_path('app/' . $path);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        $data = array_map('str_getcsv', file($this->path));
      
        $header = array_map(function ($item) {
            return Str::snake($item);
        }, array_shift($data));

        $summary = [
            'total'   => count($data),
            'success' => 0,
            'fail'    => 0,
            'media'   => []
        ];

        foreach ($data as $index => $row) 
        {
            $items = array_combine($header, $row);
            $validationResult = $this->validateRow($items, $index);

            if (!empty($validationResult)) 
            {   
                $summary['errors'][] = $validationResult;
                $summary['fail']++;
                $summary['media'][] = [
                    'row' => $index + 1,
                    'status' => 'Import failed',
                ];
                //Log::error('Validation failed for row ' . ($index + 1) . ': ' . implode('; ', $validationResult['messages']));

                 continue;
            }
            
            try {
                // Check for specific condition to set 'Clone imported' status
                $cloneImported = $this->checkForCloneImport($items);

                if ($cloneImported) 
                {
                    $summary['success']++;
                    $summary['media'][] = [
                        'row' => $index + 1,
                        'status' => 'Clone imported',
                    ];
                } 
                else 
                {
                    Media::create($items);
                    $summary['success']++;
                    $summary['media'][] = [
                        'row' => $index + 1,
                        'status' => 'Imported',
                    ];
                }
            } catch (\Exception $e) {
                $summary['fail']++;
                $summary['media'][] = [
                    'row' => $index + 1,
                    'status' => 'Import failed Log',
                ];
                Log::error('Error inserting row ' . ($index + 1) . ': ' . $e->getMessage());
            }
        }
        // Emit event with import summary
        Broadcast::channel('import-media', function () {
            return true;
        });
        broadcast(new ImportMedia($summary));
    }
    /**
     * Validate a single row of data.
     */
    protected function validateRow(array $row, int $index): array
    {
        // Clean up the 'category' field from extra spaces
        $row['category']   = trim($row['category']);

        $validator = Validator::make($row, [
            'media'     => 'required|string',
            'publisher' => 'required|string',
            'email'     => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/',
            'category'  => 'required|string|in:' . $this->getAllowedCategories(),
        ], $this->messages());

        $errors = $validator->errors()->toArray();

        if ($errors) 
        {
            return [
                'row' => $index + 1,  // Adding 1 to make row numbers start from 1
                'messages' => array_map(function($msgs) {
                    return implode('; ', $msgs);
                }, $errors)
            ];
        }

        return [];
    }
    /**
    * Get custom validation messages.
    */
    protected function messages(): array
    {
        return [
            'email.required'    => 'The :attribute is required.',
            'email.email'       => 'The :attribute must be a valid email address.',
            'email.regex'       => 'The :attribute must be a valid email address.',
            'category.required' => 'The :attribute field is required.',
            'category.in'       => 'Each row must have exactly one valid :attribute from the following list: ' . $this->getAllowedCategories(),
        ];
    }
    /**
     * Retrieve the list of allowed categories for validation.
     *
     * This method returns a formatted string of categories, where each category is enclosed in double quotes 
     * and separated by commas. The categories are predefined and must match exactly with the values provided 
     * in the import data.
     *
     * @return string A formatted string of allowed categories.
     */
    private function getAllowedCategories(): String
    {   
        $categories = array_map('trim', [
            'Arts, Culture and Events',
            'Auto and Moto',
            'Beauty, Cosmetics, Pharmaceuticals',
            'Economy, Business and Banking',
            'Food and Gastronomy'
        ]);
        return $this->formatCategories($categories);
    }
    /**
     * Format the allowed categories by enclosing each category in double quotes
     * and then joining them into a single string separated by commas.
     *
     * @param array $categories An array of categories to be formatted.
     * @return string A formatted string of categories enclosed in double quotes and separated by commas.
     */
    private function formatCategories(array $categories): string
    {
        // Add double quotes around each category
        $result = array_map(function($category) {
            return '"' . $category . '"';
        }, $categories);

        // Combine into a single string separated by commas
        return implode(', ', $result);
    }
    /**
     * Check if the item qualifies as 'Clone imported'.
     *
     * @param array $items The item to check.
     * @return bool True if the item is considered as 'Clone imported', false otherwise.
     */
    private function checkForCloneImport(array $items): bool
    {
        // Define the columns to check for duplicates
        $columns = ['media', 'publisher', 'name', 'email', 'info_for_admin', 'category'];

        // Build a query to check if a record with the same values already exists
        $query = Media::query();

        foreach ($columns as $column) {
            if (isset($items[$column])) {
                $query->where($column, $items[$column]);
            }
        }

        // Execute the query to see if any matching record exists
        return $query->exists();
    }
}
