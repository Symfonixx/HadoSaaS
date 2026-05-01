<?php

use Illuminate\Support\Facades\Route;
use Modules\Base\Models\Settings;

Route::get('/robots.txt', function () {
    $default = "User-agent: *\nDisallow:\n";
    $content = Settings::get('robots_txt', $default) ?: $default;

    return response($content, 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');
