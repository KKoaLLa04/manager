<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/'], function () {
    $domains = collect(scandir(app_path('Domain')))->filter(function ($dir) {
        return !in_array($dir, ['.', '..']);
    })->values()->toArray();
    foreach ($domains as $domain) {
        if (file_exists(app_path('Domain').'/'.$domain.'/Routes/routes.php')) {
            require app_path('Domain').'/'.$domain.'/Routes/routes.php';
        }
    }
})->middleware('api');
