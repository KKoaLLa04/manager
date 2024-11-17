
<?php

use App\Domain\TeacherStudent\Controllers\TeacherStudentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'teacher/student', 'middleware' => 'auth:api'], function () {

    Route::get('/parents', [TeacherStudentController::class, 'showParents']);
    Route::post('/detach-parent/{student_id}', [TeacherStudentController::class, 'detachParent']);
    Route::post('/assign-parent/{student_id}', [TeacherStudentController::class, 'assignParent']);
    Route::get('/{class_id}', [TeacherStudentController::class, 'index']);
    Route::get('/show/{id}', [TeacherStudentController::class, 'show']);
    Route::post('/store', [TeacherStudentController::class, 'store']);
    Route::post('/update/{id}', [TeacherStudentController::class, 'update']);
    // Lấy danh sách phụ huynh

});
