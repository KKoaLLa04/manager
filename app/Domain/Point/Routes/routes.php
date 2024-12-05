<?php

use App\Domain\Point\Controllers\ExamController;
use App\Domain\Point\Controllers\ExamPeriodController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/exam', 'middleware' => 'auth:api'], function () {
    Route::get('/', [ExamController::class, 'index']);
    Route::post('/store', [ExamController::class, 'store']);
    Route::post('/update', [ExamController::class, 'update']);
    Route::post('/delete', [ExamController::class, 'delete']);
});

Route::group(['prefix' => 'manager/exam-period', 'middleware' => 'auth:api'], function () {
    Route::get('/', [ExamPeriodController::class, 'index']);
    Route::post('/store', [ExamPeriodController::class, 'store']);
    Route::post('/update', [ExamPeriodController::class, 'update']);
    Route::post('/delete', [ExamPeriodController::class, 'delete']);
});
