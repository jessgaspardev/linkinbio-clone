<?php

use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::get('/gallery', [GalleryController::class, 'index']);

Route::post('/{username}/{slug}/report', [ReportController::class, 'store'])
    ->where('username', '[A-Za-z0-9_-]+')
    ->where('slug', '[A-Za-z0-9_-]+')
    ->middleware('throttle:5,1');