<?php

namespace App\Domain\SchoolYear\Controllers;

use App\Common\Enums\AccessTypeEnum;
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
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;



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


        $keyword = "";

        if(!empty($request->keyword)){
            $keyword = $request->keyword;
        }

        $pageIndex = 1;

        if(!empty($request->pageIndex)){
            $pageIndex = $request->pageIndex;
        }

        $pageSize = 15;

        if(!empty($request->pageSize)){
            $pageSize = $request->pageSize;
        }

        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $SchoolYearIndexRepository = new SchoolYearIndexRepository();

        $check = $SchoolYearIndexRepository->handle($keyword);

        if ($check) {
            // return $this->responseSuccess(['data' => $check->forPage($pageIndex, $pageSize)], trans('api.alert.school_year.index_success'));
            return response()->json([
                'msg' => trans('api.alert.school_year.index_success'),
                'data' => $check->forPage($pageIndex, $pageSize),
                'total' => $check->count()
            ], ResponseAlias::HTTP_OK);
        } else {
            // return $this->responseError(trans('api.alert.school_year.index_failed'));
            return response()->json([
                'msg' => trans('api.alert.school_year.index_success'),
                'data' => [],
                'total' => 0
            ], ResponseAlias::HTTP_OK);
        }

    }


    public function detail(int $id, Request $request)
    {

        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $SchoolYearDetailRepository = new SchoolYearDetailRepository();

        $check = $SchoolYearDetailRepository->handle($id);

        if ($check) {
            // return $this->responseSuccess(['data' => $check->toArray()], trans('api.alert.school_year.detail_success'));
            return response()->json([
                'msg' => trans('api.alert.school_year.detail_success'),
                'data' => $check,
            ], ResponseAlias::HTTP_OK);
        } else {
            // return $this->responseError(trans('api.alert.school_year.detail_failed'));
            return response()->json([
                'msg' => trans('api.alert.school_year.detail_failed'),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }



    }


    public function delete(int $id, Request $request)
    {

        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $SchoolYearDeleteRepository = new SchoolYearDeleteRepository();

        $check = $SchoolYearDeleteRepository->handle($id, $user_id);

        if ($check) {
            // return $this->responseSuccess([], trans('api.alert.school_year.delete_success'));
            return response()->json([
                'msg' => trans('api.alert.school_year.delete_success'),
                'data' => []
            ], ResponseAlias::HTTP_OK);
        } else {
            // return $this->responseError(trans('api.alert.school_year.delete_failed'));
            return response()->json([
                'msg' => trans('api.alert.school_year.delete_failed'),
                'data' => []
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    public function add(SchoolYearAddRequest $request)
    {

        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $SchoolYearAddRepository = new SchoolYearAddRepository();
        $check = $SchoolYearAddRepository->handle($user_id, $request);

        if ($check) {
            // $data = SchoolYear::all();
            // $data = $data->last();
            // return $this->responseSuccess(['data' => $data->toArray()], trans('api.alert.school_year.add_success'));
            return response()->json([
                'msg' => trans('api.alert.school_year.add_success'),
                'data' => []
            ], ResponseAlias::HTTP_OK);
        } else {
            // return $this->responseError(trans('api.alert.school_year.add_failed'));
            return response()->json([
                'msg' => trans('api.alert.school_year.add_failed'),
                'data' => []
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function edit(int $id, SchoolYearEditRequest $request)
    {

        $user_id = Auth::user()->id;

        $SchoolYearEditRepository = new SchoolYearEditRepository();

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $SchoolYearEditRepository->handle($id, $user_id, $request);

        if ($check) {
            // $data = SchoolYear::find($id);
            // return $this->responseSuccess(['data' => $data->toArray()], trans('api.alert.school_year.edit_success'));
            return response()->json([
                'msg' => trans('api.alert.school_year.edit_success'),
                'data' => []
            ], ResponseAlias::HTTP_OK);
        } else {
            // return $this->responseError(trans('api.alert.school_year.edit_failed'));
            return response()->json([
                'msg' => trans('api.alert.school_year.edit_failed'),
                'data' => []
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
