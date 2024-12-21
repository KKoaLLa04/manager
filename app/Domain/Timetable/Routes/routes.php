
<?php

use App\Domain\Timetable\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/timetable'], function () {
    Route::get('/', [TimetableController::class, 'index']);
    Route::post('/edit', [TimetableController::class, 'editTimetable']);
    Route::get('/config', [TimetableController::class, 'indexConfig']);
    Route::get('/subject-config', [TimetableController::class, 'getSubjectConfig']);
    Route::post('/edit-subject-config', [TimetableController::class, 'editSubjectConfig']);
    Route::post('/edit-config', [TimetableController::class, 'editConfig'])->name('manager.timetable.index');
});
