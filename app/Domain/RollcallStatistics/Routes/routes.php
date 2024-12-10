
<?php

use App\Domain\RollcallStatistics\Controllers\RollcallStatisticsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/rollcallStatistics', 'middleware' => 'auth:api'], function () {

    Route::get('/', [RollcallStatisticsController::class, 'index']);
    Route::get('showclass/{classId}', [RollcallStatisticsController::class, 'showclassRollCall']);
    // Route::get('/showclassdetail/{classId}', [RollCallHistoryController::class, 'showRollCallHistoryDetails']);

});