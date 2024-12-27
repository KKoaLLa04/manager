
<?php

use App\Domain\LeaveRequest\Controllers\LeaveRequestController;
use App\Domain\LeaveRequest\Controllers\LeaveRequestTeacherController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/leaverequest','middleware' => 'auth:api'], function () {
    Route::get('/',[LeaveRequestController::class,'index']);
    Route::get('/show/{id}',[LeaveRequestController::class,'showRequest']);
    Route::put('/accept/{id}',[LeaveRequestController::class,'acceptRequest']);
    Route::put('/reject/{id}',[LeaveRequestController::class,'rejectRequest']);
});


Route::group(['prefix' => 'teacher/leaverequest','middleware' => 'auth:api'], function () {
    Route::get('/',[LeaveRequestTeacherController::class,'index']);
    Route::get('/show/{id}',[LeaveRequestTeacherController::class,'showRequest']);
    Route::put('/accept/{id}',[LeaveRequestTeacherController::class,'acceptRequest']);
    Route::put('/reject/{id}',[LeaveRequestTeacherController::class,'rejectRequest']);
});