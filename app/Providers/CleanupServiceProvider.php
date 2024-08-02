<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\MigrationEvent;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Support\Facades\Log;

class CleanupServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(MigrationsStarted::class, function (MigrationsStarted $event) {
            $this->clearImportsFolder();
        });
    }

    /**
     * Clean imports folder.
     * This function locates the 'imports' folder within the 'storage/app/public' path.
     * If the folder exists, it retrieves all files within that folder.
     * It then iterates over each file and deletes it, effectively clearing the folder.
     * @return void
     */
    protected function clearImportsFolder(): void
    {
        Log::info('Clearing Imports folder...');

        $folders = ['imports'];

        foreach ($folders as $folder) 
        {
            $path = storage_path("app/public/$folder/");

            if (is_dir($path)) 
            {
                $files = glob($path . '/*'); 

                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                
                Log::info('Imports folder cleared.');
            }
            else 
            {
                Log::info('Imports folder does not exist.');
            }
        }
    }
}
