
<?php

use App\Domain\RollCallHistory\Controllers\RollCallHistoryController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/rollcallhistory', 'middleware' => 'auth:api'], function () {

    Route::get('/', [RollCallHistoryController::class, 'index']);
    Route::get('showclass/{classId}', [RollCallHistoryController::class, 'showRollCallHistories']);
    Route::get('/showclassdetail/{classId}', [RollCallHistoryController::class, 'showRollCallHistoryDetails']);

});