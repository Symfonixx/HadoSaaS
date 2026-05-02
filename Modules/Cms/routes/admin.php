<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\Admin\BlogCategoryController;
use Modules\Cms\Http\Controllers\Admin\BlogController;
use Modules\Cms\Http\Controllers\Admin\FaqController;
use Modules\Cms\Http\Controllers\Admin\PageController;

Route::middleware('can:CMS Management')->group(function () {

    Route::delete('pages/deleteMulti', [PageController::class, 'deleteMulti'])->name('pages.deleteMulti');
    Route::resource('pages', PageController::class)->except(['destroy', 'show']);

    Route::delete('blogs/deleteMulti', [BlogController::class, 'deleteMulti'])->name('blogs.deleteMulti');
    Route::resource('blogs', BlogController::class)->except(['destroy', 'show']);

    Route::delete('blogs_categories/deleteMulti', [BlogCategoryController::class, 'deleteMulti'])->name('blogs_categories.deleteMulti');
    Route::resource('blogs_categories', BlogCategoryController::class)->except(['destroy', 'show']);

    Route::delete('faqs/deleteMulti', [FaqController::class, 'deleteMulti'])->name('faqs.deleteMulti');
    Route::resource('faqs', FaqController::class)->except(['destroy', 'show']);
});
