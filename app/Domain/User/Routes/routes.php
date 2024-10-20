
<?php

use App\Domain\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/user', 'middleware' => 'auth:api'], function () {

    Route::get('/', [UserController::class, 'index']);
    Route::get('/chooseClassToMainTearch', [UserController::class, 'chooseClassToMainTearch']);
    Route::post('/change_password/{id}', [UserController::class, 'changePassword']);
    Route::get('/{id}', [UserController::class, 'detail']);
    Route::post('/add', [UserController::class, 'add']);
    Route::post('/edit/{id}', [UserController::class, 'edit']);
    Route::post('/delete/{id}', [UserController::class, 'delete']);

});
