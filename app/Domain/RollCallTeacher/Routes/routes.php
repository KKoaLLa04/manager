
<?php

use App\Domain\RollCallTeacher\Controllers\RollCallTeacherController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'teacher/rollcallteacher', 'middleware' => 'auth:api'], function () {
    Route::get('/', [RollCallTeacherController::class, 'index']);
    Route::get('student/{class_id}', [RollCallTeacherController::class, 'studentInClass']);
    Route::post('attendaced/student/{id}', [RollCallTeacherController::class, 'rollCall']);
    Route::put('update/attendaced/{class_id}', [RollCallTeacherController::class, 'updateByClass']);
});