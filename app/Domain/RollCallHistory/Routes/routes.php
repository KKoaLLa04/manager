
<?php

use App\Domain\RollCallHistory\Controllers\RollCallHistoryController;
use App\Domain\RollCallHistory\Controllers\RollCallHistoryTeacherController;
use App\Domain\RollCallTeacher\Controllers\RollCallTeacherController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/rollcallhistory', 'middleware' => 'auth:api'], function () {

    Route::get('/', [RollCallHistoryController::class, 'index']);
    Route::get('showclass/{classId}', [RollCallHistoryController::class, 'showRollCallHistories']);
    Route::get('student/{class_id}/{teacher_subject_timetable_id}', [RollCallHistoryController::class, 'studentInClass']);
});

Route::group(['prefix' => 'teacher/rollcallhistory', 'middleware' => 'auth:api'], function () {
    Route::get('/', [RollCallHistoryTeacherController::class, 'getClass']);
    Route::get('showclass/{classId}', [RollCallHistoryTeacherController::class, 'showRollCallHistories']);
    Route::get('student/{class_id}/{teacher_subject_timetable_id}', [RollCallHistoryTeacherController::class, 'studentInClass']);
});
