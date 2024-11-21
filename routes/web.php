<?php

use App\Domain\Student\Controllers\StudentController;
use App\Jobs\CreateNotification;
use App\jobs\NotificationJob;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test', function () {
//    $dataSendNoti = [
//        "title"    => "Học sinh Huy đã đến lớp rồi mẹ nhé",
//        "title_en" => "Học sinh Huy đã đến lớp rồi mẹ nhé",
//        "class_id" => 1,
//        "time"     => now()
//    ];
//    $data         = [
//        [
//            'user_id'      => 1,
//            'item_id'      => 1,
//            'type'         => 1,
//            'is_read'      => 0,
//            'is_send'      => 0,
//            'is_convert' => \App\Common\Enums\ConvertEnum::NOT_CONVERT->value,
//            'data'         => json_encode($dataSendNoti),
//            'date'         => now(),
//            'created_at'   => now(),
//            'updated_at'   => now()
//       ]
//    ];
//    event(new \App\Events\CreateNotification($data));
    $rollCall = \App\Domain\RollCall\Models\RollCall::query()->first();
    CreateNotification::dispatch($rollCall);
});
Route::get('/export-excel', [StudentController::class, 'excel']);
