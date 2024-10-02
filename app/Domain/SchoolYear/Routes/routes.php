
<?php

use App\Domain\SchoolYear\Controllers\SchoolYearController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/schoolyear', 'middleware' => 'auth:api'], function () {

    Route::get('/', [SchoolYearController::class, 'index']);
    Route::get('/{id}', [SchoolYearController::class, 'detail']);
    Route::post('/add', [SchoolYearController::class, 'add']);
    Route::post('/edit/{id}', [SchoolYearController::class, 'edit']);
    Route::post('/delete/{id}', [SchoolYearController::class, 'delete']);

});
