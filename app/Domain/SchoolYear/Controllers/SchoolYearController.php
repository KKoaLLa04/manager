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

        // $user_id = $request->user_id;
        // $type = $request->type;

        // if (!$this->user->getUser($user_id, $type)) {
        //     return $this->responseError(trans('api.error.user_not_permission'));
        // }

        $SchoolYearIndexRepository = new SchoolYearIndexRepository();

        $check = $SchoolYearIndexRepository->handle($keyword);

        if ($check) {
            return $this->responseSuccess(['data' => $check->forPage($pageIndex, $pageSize)], trans('api.alert.school_year.index_success'));
        } else {
            return $this->responseError(trans('api.alert.school_year.index_failed'));
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
            return $this->responseSuccess(['data' => $check->toArray()], trans('api.alert.school_year.detail_success'));
        } else {
            return $this->responseError(trans('api.alert.school_year.detail_failed'));
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
            return $this->responseSuccess([], trans('api.alert.school_year.delete_success'));
        } else {
            return $this->responseError(trans('api.alert.school_year.delete_failed'));
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
            return $this->responseSuccess(['data' => $data->toArray()], trans('api.alert.school_year.add_success'));
        } else {
            return $this->responseError(trans('api.alert.school_year.add_failed'));
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
            return $this->responseSuccess(['data' => $data->toArray()], trans('api.alert.school_year.edit_success'));
        } else {
            return $this->responseError(trans('api.alert.school_year.edit_failed'));
        }

    }
}
