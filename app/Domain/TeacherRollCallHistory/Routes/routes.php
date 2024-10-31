
<?php

use App\Domain\TeacherRollCallHistory\Controllers\TeacherRollCallHistoryController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'teacher/teacherrollcallhistory', 'middleware' => 'auth:api'], function () {

    Route::get('/', [TeacherRollCallHistoryController::class, 'index']);

    Route::get('/showclass/{id}', [TeacherRollCallHistoryController::class, 'showclass']);

    Route::get('/showclassdetail/{classId}', [TeacherRollCallHistoryController::class, 'showClassTeacher']);

    //tai khoan
    Route::match(['get', 'post'], '/profile', [TeacherRollCallHistoryController::class, 'showTeacherProfile']);

    

});