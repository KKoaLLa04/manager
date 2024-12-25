
<?php

use App\Domain\ParentSubject\Controllers\ParentSubjectController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'guardian/parentsubject', 'middleware' => 'auth:api'], function () {
    Route::get('/', [ParentSubjectController::class, 'index']);

});
