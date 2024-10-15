
<?php

use App\Domain\RollCall\Controllers\RollCallController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/rollcall'], function () {
    Route::get('/',[RollCallController::class,'index']);
});