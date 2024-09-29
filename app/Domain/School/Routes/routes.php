
<?php
use Illuminate\Support\Facades\Route;
use App\Domain\School\Controllers\SchoolController;
use GuzzleHttp\Middleware;

Route::group(['prefix' => 'manager/school', 'middleware' => 'auth:api'], function () {
    // Route để lấy danh sách trường học
    Route::get('/', [SchoolController::class, 'index']);
    
    Route::get('/{id}', [SchoolController::class, 'show']);
    Route::post('/update/{id}', [SchoolController::class, 'update']); 
});
