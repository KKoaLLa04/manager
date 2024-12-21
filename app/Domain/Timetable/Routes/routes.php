
<?php

use App\Domain\Timetable\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/timetable'], function () {
    Route::get('/', [TimetableController::class, 'index']);
    Route::get('/config', [TimetableController::class, 'indexConfig'])->name('manager.timetable.index');
    Route::post('/edit-config', [TimetableController::class, 'editConfig'])->name('manager.timetable.index');
});
