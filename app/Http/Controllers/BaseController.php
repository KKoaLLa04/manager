<?php

namespace App\Http\Controllers;

class BaseController extends Controller
{
    public function __construct()
    {
    }
    public function responseSuccess($data = [], $message = 'success')
    {
        return response()->json([

        ]);
    }
}
