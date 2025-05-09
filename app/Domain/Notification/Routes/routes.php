
<?php

use App\Domain\Notification\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'guardian/notification', 'middleware' => 'auth:api'], function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notification.index');
});
