
<?php

use App\Domain\Guardian\Controllers\GuardianController;
use App\Domain\Guardian\Controllers\GuardianOfTeacherController;
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

Route::group(['prefix' => 'teacher/guardian', 'middleware' => 'auth:api'], function () {
    Route::put('/update/{id}',[GuardianController::class,'update']);
    Route::put('lock/{id}',[GuardianOfTeacherController::class,'LockGuardian']);
    Route::put('unlock/{id}',[GuardianOfTeacherController::class,'UnLockGuardian']);
    Route::put('change/{id}',[GuardianOfTeacherController::class,'ChangePasswordGuardian']);
});
