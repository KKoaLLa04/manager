
<?php

use App\Domain\Student\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/student', 'middleware' => 'auth:api'], function () {
    // Lấy danh sách học sinh
    Route::get('/', [StudentController::class, 'index']);

    // Thêm mới học sinh
    Route::post('/store', [StudentController::class, 'store']);

    Route::get('/{id}', [StudentController::class, 'show']);
    Route::post('/update/{id}', [StudentController::class, 'update']);
    Route::post('/delete/{id}', [StudentController::class, 'delete']);

});
