<?php

namespace App\Domain\SchoolYear\Controllers;

use App\Common\Repository\GetUserRepository;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Domain\SchoolYear\Repository\SchoolYearAddRepository;
use App\Domain\SchoolYear\Repository\SchoolYearEditRepository;
use App\Domain\SchoolYear\Repository\SchoolYearIndexRepository;
use App\Domain\SchoolYear\Repository\SchoolYearDetailRepository;
use App\Domain\SchoolYear\Repository\SchoolYearDeleteRepository;
use App\Domain\SchoolYear\Requests\SchoolYearAddRequest;
use App\Domain\SchoolYear\Requests\SchoolYearEditRequest;
use App\Models\User;

class SchoolYearController extends BaseController
{

    private $user;

    public function __construct(Request $request)
    {

        $this->user = new GetUserRepository();

        parent::__construct($request);
    }


    public function index(Request $request)
    {

        $request->validate([
            'user_id' => [
                'required',
                'integer'
            ],
            'type' => [
                'required',
                'integer'
            ],
        ]);

        $user_id = $request->user_id;
        $type = $request->type;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $SchoolYearIndexRepository = new SchoolYearIndexRepository();

        $check = $SchoolYearIndexRepository->handle();

        if ($check) {
            return response()->json(['status' => 'success', 'data' => $check->toArray()]);
        } else {
            return response()->json(['status' => 'error', 'data' => []]);
        }
    }


    public function detail(int $id, Request $request)
    {

        $request->validate([
            'user_id' => [
                'required',
                'integer'
            ],
            'type' => [
                'required',
                'integer'
            ],
        ]);

        $user_id = $request->user_id;
        $type = $request->type;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $SchoolYearDetailRepository = new SchoolYearDetailRepository();

        $check = $SchoolYearDetailRepository->handle($id);

        if ($check) {
            return response()->json(['status' => 'success', 'data' => $check->toArray()]);
        } else {
            return response()->json(['status' => 'error', 'data' => []]);
        }
    }


    public function delete(int $id, Request $request)
    {

        $request->validate([
            'user_id' => [
                'required',
                'integer'
            ],
            'type' => [
                'required',
                'integer'
            ],
        ]);

        $user_id = $request->user_id;
        $type = $request->type;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $SchoolYearDeleteRepository = new SchoolYearDeleteRepository();

        $check = $SchoolYearDeleteRepository->handle($id, $user_id);

        if ($check) {
            $data = SchoolYear::find($id);
            return response()->json(['message' => 'Xóa năm học ' . $data->name . ' thành công', 'status' => 'success']);
        } else {
            $data = SchoolYear::find($id);
            return response()->json(['message' => 'Xóa năm học ' . $data->name . ' thất bại', 'status' => 'error']);
        }
    }



    public function add(SchoolYearAddRequest $request)
    {


        $SchoolYearAddRepository = new SchoolYearAddRepository();
        $user_id = $request->user_id;
        $type = $request->type;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $SchoolYearAddRepository->handle($user_id, $request);

        if ($check) {
            $data = SchoolYear::all();
            $data = $data->last();
            return response()->json(['message' => 'Thêm năm học ' . $data->name . ' thành công', 'status' => 'success', 'data' => $data]);
            // return 1;
        } else {
            $data = SchoolYear::all();
            $data = $data->last();
            return response()->json(['status' => 'error', 'data' => [], 'message' => 'Thêm năm học ' . $data->name . ' thất bại']);
            // return 0;
        }
    }

    public function edit(int $id, SchoolYearEditRequest $request)
    {

        $SchoolYearEditRepository = new SchoolYearEditRepository();
        $user_id = $request->user_id;
        $type = $request->type;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $SchoolYearEditRepository->handle($id, $user_id, $request);

        if ($check) {
            $data = SchoolYear::find($id);
            return response()->json(['message' => 'Sửa năm học ' . $data->name . ' thành công', 'status' => 'success', 'data' => $data]);
        } else {
            $data = SchoolYear::find($id);
            return response()->json(['message' => 'Sửa năm học ' . $data->name . ' thất bại', 'status' => 'error', 'data' => []]);
            // return 0;
        }
    }
}
