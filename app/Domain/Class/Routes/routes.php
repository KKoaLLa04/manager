
<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'class', 'middleware' => 'auth:api'], function () {
});
