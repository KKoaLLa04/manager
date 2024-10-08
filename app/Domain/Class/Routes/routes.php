
<?php

use App\Domain\Class\Controllers\ClassController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/class', 'middleware' => 'auth:api'], function () {
    Route::get('/', [ClassController::class, 'index'])->name('class.index');
    Route::get('/form', [ClassController::class, 'form']);
    Route::post('/create', [ClassController::class, 'create']);
    Route::post('/update', [ClassController::class, 'update']);
    Route::post('/delete', [ClassController::class, 'delete']);
    Route::post('/formAssignTeacher', [ClassController::class, 'formAssignTeacher']);
});
