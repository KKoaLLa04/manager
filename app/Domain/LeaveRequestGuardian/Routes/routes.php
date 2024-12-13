
<?php

use App\Domain\LeaveRequestGuardian\Controllers\LeaveRequestGuardianController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'guardian/leaverequest', 'middleware' => 'auth:api'], function () {
    Route::get('/', [LeaveRequestGuardianController::class, 'index']);
    Route::post('/storeleave/{student_id}', [LeaveRequestGuardianController::class, 'store']);
    Route::post('/cancel/{leave_request_id}', [LeaveRequestGuardianController::class, 'cancel']);

    

});