
<?php

use App\Domain\RollCall\Controllers\RollCallController;
use App\Domain\RollCall\Controllers\RollCallTeacherController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/rollcall', 'middleware' => 'auth:api'], function () {
    Route::post('/', [RollCallController::class, 'index']);
    Route::post('attendaced/student/{id}', [RollCallController::class, 'rollCall']);
    Route::get('student/{class_id}/{teacher_subject_timetable_id}', [RollCallController::class, 'studentInClass']);
    Route::post('attendaced/class', [RollCallController::class, 'getRowCallOfClass']);
    Route::put('update/attendaced/{class_id}', [RollCallController::class, 'updateByClass']);
});

Route::group(['prefix' => 'teacher/rollcall', 'middleware' => 'auth:api'], function () {
    Route::post('/', [RollCallTeacherController::class, 'index']);
    Route::get('/class', [RollCallTeacherController::class, 'getClass']);
});
