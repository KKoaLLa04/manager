<?php

use App\Domain\Point\Controllers\ExamController;
use App\Domain\Point\Controllers\ExamPeriodController;
use App\Domain\Point\Controllers\GuardianPointStudentController;
use App\Domain\Point\Controllers\PointStudentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'exam', 'middleware' => 'auth:api'], function () {
    Route::get('/', [ExamController::class, 'index']);
    Route::post('/store', [ExamController::class, 'store']);
    Route::post('/update', [ExamController::class, 'update']);
    Route::post('/delete', [ExamController::class, 'delete']);
    Route::get('/subject', [ExamController::class, 'subject']);
});

Route::group(['prefix' => 'exam-period', 'middleware' => 'auth:api'], function () {
    Route::get('/', [ExamPeriodController::class, 'index']);
    Route::post('/store', [ExamPeriodController::class, 'store']);
    Route::post('/update', [ExamPeriodController::class, 'update']);
    Route::post('/delete', [ExamPeriodController::class, 'delete']);
});

Route::group(['prefix' => 'point-student', 'middleware' => 'auth:api'], function () {
    Route::get('/', [PointStudentController::class, 'index']);
    Route::post('/store-point', [PointStudentController::class, 'store']);
});

Route::group(['prefix' => 'guardian/point-student', 'middleware' => 'auth:api'], function () {
    Route::get('/', [GuardianPointStudentController::class, 'index']);
});

