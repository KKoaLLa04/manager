
<?php

use App\Domain\Class\Controllers\ClassController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/class', 'middleware' => 'auth:api'], function () {
    Route::get('/', [ClassController::class, 'index'])->name('class.index');
});
