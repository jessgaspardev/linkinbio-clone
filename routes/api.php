<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GalleryController;

Route::get('/ping', function() {
    return response()->json(['message' => 'pong']);
});
Route::get('/gallery', [GalleryController::class, 'index']);