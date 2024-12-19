
<?php
use App\Domain\ParentRollCallHistory\Controllers\ParentRollCallHistoryController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'guardian/parentrollcallhistory', 'middleware' => 'auth:api'], function () {

    Route::get('/', [ParentRollCallHistoryController::class, 'index']);

    // Route::get('/showclass/{id}', [ParentRollCallHistoryController::class, 'showclass']);

    // Route::get('/showclassdetail/{classId}', [TeacherRollCallHistoryController::class, 'showClassTeacher']);

    // //tai khoan
    // Route::match(['get', 'post'], '/profile', [TeacherRollCallHistoryController::class, 'showTeacherProfile']);

    

});