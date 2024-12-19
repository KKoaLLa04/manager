
<?php

use App\Domain\Subject\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/subject', 'middleware' => 'auth:api'], function () {

    Route::get('/', [SubjectController::class, 'index']);
    Route::get('/currentClass', [SubjectController::class, 'currentClass']);
    Route::get('/classNoHasSubject', [SubjectController::class, 'classNoHasSubject']);
    Route::post('/mix_subject_for_class', [SubjectController::class, 'mixSubjectForClass']);
    Route::post('/create', [SubjectController::class, 'create']);
    Route::post('/update/{id}', [SubjectController::class, 'update']);
    Route::post('/delete/{id}', [SubjectController::class, 'delete']);
});
