
<?php

use App\Domain\RollCall\Controllers\RollCallController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/rollcall', 'middleware' => 'auth:api'], function () {
    Route::get('/', [RollCallController::class, 'index']);
    Route::post('attendaced/student/{id}', [RollCallController::class, 'rollCall']);
    Route::put('update/attendaced/{class_id}', [RollCallController::class, 'updateByClass']);
});
