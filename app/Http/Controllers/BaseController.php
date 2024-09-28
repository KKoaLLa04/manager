<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BaseController extends Controller
{
    public function __construct()
    {
    }

    public function responseSuccess($data = [], $message = 'success'): JsonResponse
    {
        return response()->json($data, ResponseAlias::HTTP_OK);
    }

    protected function responseError(string $message = '', $status = ResponseAlias::HTTP_BAD_REQUEST): JsonResponse
    {
        return Response::json(
            [
                'msg'  => !empty($message) ? $message : '',
                'data' => null,
            ],
            $status
        );
    }
}
