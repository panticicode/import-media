<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaImportController;

Route::post('/import', [MediaImportController::class, 'uploadDatabase']);

Route::get('{any?}', function() {
    return view('media');
})->where('any', '.*');
