
<?php

use App\Domain\Timetable\Controllers\TimetableController;
use App\Domain\Timetable\Controllers\TimetableGuardianController;
use App\Domain\Timetable\Controllers\TimetableTeacherController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/timetable'], function () {
    Route::get('/', [TimetableController::class, 'index']);
    Route::post('/edit', [TimetableController::class, 'editTimetable']);
    Route::get('/config', [TimetableController::class, 'indexConfig']);
    Route::get('/subject-config', [TimetableController::class, 'getSubjectConfig']);
    Route::post('/edit-subject-config', [TimetableController::class, 'editSubjectConfig']);
    Route::post('/edit-config', [TimetableController::class, 'editConfig'])->name('manager.timetable.index');
});

Route::group(['prefix' => 'teacher/timetable'], function () {
    Route::get('/', [TimetableTeacherController::class, 'index']);
});

Route::group(['prefix' => 'guardian/timetable'], function () {
    Route::get('/', [TimetableGuardianController::class, 'index']);
});
