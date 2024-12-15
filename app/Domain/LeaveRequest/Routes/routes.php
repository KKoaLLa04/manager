
<?php

use App\Domain\LeaveRequest\Controllers\LeaveRequestController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/leaverequest','middleware' => 'auth:api'], function () {
    Route::get('/',[LeaveRequestController::class,'index']);
    Route::get('/show/{id}',[LeaveRequestController::class,'showRequest']);
    Route::put('/accept/{id}',[LeaveRequestController::class,'acceptRequest']);
    Route::put('/reject/{id}',[LeaveRequestController::class,'rejectRequest']);
});