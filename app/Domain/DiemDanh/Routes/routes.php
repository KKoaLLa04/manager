
<?php

use App\Domain\DiemDanh\Controllers\DiemDanhController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/khoabieu', 'middleware' => 'auth:api'], function () {

    Route::get('/', [DiemDanhController::class, 'danhsach']);
    Route::post('/tao-sua-khoa-bieu', [DiemDanhController::class, 'add_edit']);

});
