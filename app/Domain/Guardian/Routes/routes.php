
<?php

use App\Domain\Guardian\Controllers\GuardianController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/guardian', 'middleware' => 'auth:api'], function () {
    Route::get('/',[GuardianController::class,'index']);
    Route::get('/student',[GuardianController::class,'getStudent']);
    Route::post('/add',[GuardianController::class,'create']);
    Route::get('/show/{id}',[GuardianController::class,'show']);
    Route::put('/update/{id}',[GuardianController::class,'update']);
    Route::put('lock/{id}',[GuardianController::class,'LockGuardian']);
    Route::put('unlock/{id}',[GuardianController::class,'UnLockGuardian']);
    Route::put('change/{id}',[GuardianController::class,'ChangePasswordGuardian']);
    Route::post('add/{guardianId}/assign-student', [GuardianController::class, 'assignStudent']);
    Route::post('delete/{guardianId}/unassign-student', [GuardianController::class, 'unassignStudent']);
});