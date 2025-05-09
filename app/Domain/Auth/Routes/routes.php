
<?php

use App\Domain\Auth\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
})->middleware('auth');

Route::post('/device_token', [AuthController::class, 'storeDeviceToken']);


