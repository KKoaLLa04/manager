
<?php
use Illuminate\Support\Facades\Route;
use App\Domain\AcademicYear\Controllers\AcademicYearController;

Route::group(['prefix' => 'manager/academicyear', 'middleware' => 'auth:api'], function () {
    Route::get('/', [AcademicYearController::class, 'index']);
    Route::post('/add', [AcademicYearController::class, 'store']);
    Route::get('show/{id}', [AcademicYearController::class, 'show']);
    Route::put('/update/{id}', [AcademicYearController::class, 'update']);
    Route::delete('/delete/{id}', [AcademicYearController::class, 'delete']);
});
