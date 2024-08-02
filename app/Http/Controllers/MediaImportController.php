<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ImportMediaJob;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;
use Illuminate\Support\Facades\Validator;

class MediaImportController extends Controller
{
    public function uploadDatabase(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('file')->store('public/imports');

        ImportMediaJob::dispatch($path);

        return response()->json(['message' => 'File uploaded successfully and import process started']);
    }
}
