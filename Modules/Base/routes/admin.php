<?php

use Illuminate\Support\Facades\Route;
use Modules\Base\Http\Controllers\Admin\LogController;
use Modules\Base\Http\Controllers\Admin\MediaLibraryController;
use Modules\Base\Http\Controllers\Admin\SeoController;
use Modules\Base\Http\Controllers\Admin\SettingsController;

// Group for Settings Management
Route::middleware('can:Settings Management')->group(function () {
    Route::resource('settings', SettingsController::class)->only(['index', 'store']);
    Route::resource('seo', SeoController::class)->only(['index', 'store']);
});

Route::middleware('can:Media Library Management')->group(function () {
    Route::get('media-library/list', [MediaLibraryController::class, 'list'])->name('media_library.list');
    Route::post('media-library', [MediaLibraryController::class, 'store'])->name('media_library.store');
    Route::delete('media-library/delete-multi', [MediaLibraryController::class, 'deleteMulti'])->name('media_library.delete_multi');
    Route::delete('media-library/{media}', [MediaLibraryController::class, 'destroy'])->name('media_library.destroy');
    Route::get('media-library', [MediaLibraryController::class, 'index'])->name('media_library.index');
});

// Group for Logs Management
Route::middleware('can:Logs Management')->group(function () {
    Route::delete('logs/deleteMulti', [LogController::class, 'deleteMulti'])->name('logs.deleteMulti');
    Route::resource('logs', LogController::class)->only(['index', 'show']);
});
