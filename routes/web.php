<?php

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
    $client = new Client();

    $cookies = [
        'laravel_session' => 'XDw3rSn7ipZocrQTQOYxheTOvGVO2BPLJJC9Iqpv',
        '_gcl_au' => '1.1.307401310.1685096321',
        '_gid' => 'GA1.2.1786782073.1685096321',
        '_fbp' => 'fb.1.1685096322884.1341401421',
        '__zi' => '2000.SSZzejyD3jSkdl-krWqVtYU9zQ-T61wH9TthuPC0NCqtr_NpqH9AtJY9_VMSN4xGC8Bx_P0PJzSyol__dXnArJCoDG.1',
        'redirectLogin' => 'https://vietteltelecom.vn/dang-ky',
        '_ga_VH8261689Q' => 'GS1.1.1685096321.1.1.1685096380.1.0.0',
        '_ga' => 'GA1.2.1385846845.1685096321',
        '_gat_UA-58224545-1' => '1',
        'XSRF-TOKEN' => 'eyJpdiI6Im4zUUJSaGRYRlJtaFNcL210cjdvQmJ3PT0iLCJ2YWx1ZSI6IkZKdHppMVJIU2xGU2l3RmFUeEpqM1Y5ZHFra0tnQjFCMVREMlwvUXpneENEd1VyMjI0aHQ4eWlVXC83a2VycmlCdCIsIm1hYyI6IjNmYTg4YThhOGNkZmQzZTQ4MGQ1MDBjMWVmMWNmYTAxNzYxNWMxM2NjZDY1MmZmYjFlYzViOTUyOTUxMmRiNWYifQ%3D%3D',
    ];

    $headers = [
        'Accept' => 'application/json, text/plain, */*',
        'Accept-Language' => 'vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5',
        'Connection' => 'keep-alive',
        'Content-Type' => 'application/json;charset=UTF-8',
        'Origin' => 'https://vietteltelecom.vn',
        'Referer' => 'https://vietteltelecom.vn/dang-nhap',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36',
        'X-CSRF-TOKEN' => 'dS0MwhelCkb96HCH9kVlEd3CxX8yyiQim71Acpr6',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-XSRF-TOKEN' => 'eyJpdiI6Im4zUUJSaGRYRlJtaFNcL210cjdvQmJ3PT0iLCJ2YWx1ZSI6IkZKdHppMVJIU2xGU2l3RmFUeEpqM1Y5ZHFra0tnQjFCMVREMlwvUXpneENEd1VyMjI0aHQ4eWlVXC83a2VycmlCdCIsIm1hYyI6IjNmYTg4YThhOGNkZmQzZTQ4MGQ1MDBjMWVmMWNmYTAxNzYxNWMxM2NjZDY1MmZmYjFlYzViOTUyOTUxMmRiNWYifQ==',
    ];

    $json_data = [
        'phone' => "0388634534",
        'type' => '',
    ];

    $response = $client->post('https://vietteltelecom.vn/api/get-otp-login', [
        'headers' => $headers,
        'cookies' => $cookies,
        'json' => $json_data,
    ]);

    $body = $response->getBody()->getContents();

    return response()->json(json_decode($body));
});
