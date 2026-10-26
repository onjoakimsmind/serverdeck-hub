<?php

use App\Http\Controllers\Api\IngestController;
use Illuminate\Support\Facades\Route;

Route::post('/ingest', [IngestController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('api.ingest');
