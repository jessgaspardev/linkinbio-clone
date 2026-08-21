<?php

use App\Http\Controllers\LinkController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('pages', PageController::class)->except(['create', 'edit']);
    Route::patch('/pages/{page}/visibility', [PageController::class, 'toggleVisibility']);
    Route::patch('/pages/{page}/listed', [PageController::class, 'toggleListed']);
    Route::patch('/pages/{page}/theme', [PageController::class, 'setTheme']);
    Route::post('/pages/{page}/links', [LinkController::class, 'store']);
    Route::patch('/pages/{page}/links/reorder', [LinkController::class, 'reorder']);
    Route::patch('/pages/{page}/links/{link}', [LinkController::class, 'update']);
    Route::delete('/pages/{page}/links/{link}', [LinkController::class, 'destroy']);
    Route::post('/subscribe', [SubscriptionController::class, 'upgrade']);
    Route::post('/billing-portal', [SubscriptionController::class, 'portal']);
});

require __DIR__.'/settings.php';

Route::get('/{username}/{slug}', [PublicPageController::class, 'show'])
    ->where('username', '[A-Za-z0-9_-]+')
    ->where('slug', '[A-Za-z0-9_-]+')
    ->middleware('throttle:60,1')
    ->name('public.page.show');