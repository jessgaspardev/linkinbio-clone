<?php

use App\Http\Controllers\LinkController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('pages', PageController::class)->except(['create', 'edit']);

    Route::post('/pages/{page}/links', [LinkController::class, 'store']);
    Route::patch('/pages/{page}/links/reorder', [LinkController::class, 'reorder']);
    Route::patch('/pages/{page}/links/{link}', [LinkController::class, 'update']);
    Route::delete('/pages/{page}/links/{link}', [LinkController::class, 'destroy']);
});

require __DIR__.'/settings.php';