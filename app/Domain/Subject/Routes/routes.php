
<?php

use App\Domain\Subject\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/subject', 'middleware' => 'auth:api'], function () {

    Route::get('/', [SubjectController::class, 'index']);

});
