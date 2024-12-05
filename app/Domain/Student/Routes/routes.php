
<?php

use App\Domain\Student\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/student', 'middleware' => 'auth:api'], function () {
    // Lấy danh sách học sinh
    Route::get('/', [StudentController::class, 'index']);
    Route::post('/class_by_year', [StudentController::class, 'classByYear']);
    Route::get('/detail_class_current', [StudentController::class, 'getDetailClassCurrent']);
    Route::get('/student_by_class', [StudentController::class, 'getStudentByClass']);
    Route::get('/change_class_for_student', [StudentController::class, 'changeClassForStudent']);

    // Thêm mới học sinh
    Route::post('/store', [StudentController::class, 'store']);
    Route::get('/show/{id}', [StudentController::class, 'show']);
    Route::post('/update/{id}', [StudentController::class, 'update']);
    Route::post('/delete/{id}', [StudentController::class, 'delete']);
    Route::post('/assign-parent/{student_id}', [StudentController::class, 'assignParent'])->whereNumber('student_id');
    Route::post('/detach-parent/{student_id}', [StudentController::class, 'detachParent']);
    Route::post('/up_grade', [StudentController::class, 'upGrade']);

      // Lấy danh sách phụ huynh
      Route::get('/parents', [StudentController::class, 'showParents']);


});
