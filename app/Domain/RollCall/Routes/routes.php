
<?php

use App\Domain\RollCall\Controllers\RollCallController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/rollcall', 'middleware' => 'auth:api'], function () {
    Route::get('/', [RollCallController::class, 'index']);
    Route::post('attendaced/student/{id}', [RollCallController::class, 'rollCall']);
    Route::get('student/{class_id}/{diemdanh_id}', [RollCallController::class, 'studentInClass']);
    Route::post('attendaced/class', [RollCallController::class, 'getRowCallOfClass']);
    Route::put('update/attendaced/{class_id}', [RollCallController::class, 'updateByClass']);
});
