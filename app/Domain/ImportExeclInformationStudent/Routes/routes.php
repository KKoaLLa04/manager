
<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'importexeclinformationstudent'], function () {
    Route::post('/', 'ImportExeclInformationStudentController@index');
});
