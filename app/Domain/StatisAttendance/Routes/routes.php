
<?php

use App\Domain\StatisAttendance\Controllers\StatisAttendanceController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/statisattendance', 'middleware' => 'auth:api'], function () {
    Route::get('day',[StatisAttendanceController::class, 'index']);
    Route::get('week',[StatisAttendanceController::class, 'getAllAttendanceOnWeek']);
    Route::get('month',[StatisAttendanceController::class, 'getAllAttendanceOnMonth']);
    Route::get('day-class',[StatisAttendanceController::class, 'getAllAttendanceOnDayWithClass']);
    Route::get('week-class',[StatisAttendanceController::class, 'getAllAttendanceOnWeekyWithClass']);
    Route::get('month-class',[StatisAttendanceController::class, 'getAllAttendanceOnMonthyWithClass']);
});