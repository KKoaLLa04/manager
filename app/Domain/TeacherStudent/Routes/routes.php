
<?php

use App\Domain\TeacherStudent\Controllers\TeacherStudentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'teacher/student', 'middleware' => 'auth:api'], function () {

    Route::get('/{class_id}', [TeacherStudentController::class, 'index']);

});
