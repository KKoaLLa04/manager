<?php
namespace App\Domain\User\Controllers;

use App\Common\Repository\GetUserRepository;
use App\Domain\User\Repository\UserAddRepository;
use App\Domain\User\Repository\UserDeleteRepository;
use App\Domain\User\Repository\UserDetailRepository;
use App\Domain\User\Repository\UserEditRepository;
use App\Domain\User\Repository\UserIndexRepository;
use App\Domain\User\Requests\UserAddRequest;
use App\Domain\User\Requests\UserEditRequest;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends BaseController
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

        $IndexRepository = new UserIndexRepository();

        $check = $IndexRepository->handle($keyword);

        if ($check) {
            return $this->responseSuccess(['data' => $check->forPage($pageIndex, $pageSize)], trans('api.alert.together.index_success'));
        } else {
            return $this->responseError(trans('api.alert.together.index_failed'));
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

        $DetailRepository = new UserDetailRepository();

        $check = $DetailRepository->handle($id);

        if ($check) {
            return $this->responseSuccess(['data' => $check->toArray()], trans('api.alert.together.detail_success'));
        } else {
            return $this->responseError(trans('api.alert.together.detail_failed'));
        }


    }

    public function add(UserAddRequest $request)
    {

        $AddRepository = new UserAddRepository();
        $user_id = $request->user_id;
        $type = $request->type;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $AddRepository->handle($user_id, $request);

        if ($check) {
            $data = User::all();
            $data = $data->last();
            return $this->responseSuccess(['data' => $data->toArray()], trans('api.alert.together.add_success'));
        } else {
            return $this->responseError(trans('api.alert.together.add_failed'));
        }

    }


    public function edit(int $id, UserEditRequest $request)
    {

        $EditRepository = new UserEditRepository();
        $user_id = $request->user_id;
        $type = $request->type;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $EditRepository->handle($id, $user_id, $request);

        if ($check) {
            $data = User::find($id);
            return $this->responseSuccess(['data' => $data->toArray()], trans('api.alert.together.edit_success'));
        } else {
            return $this->responseError(trans('api.alert.together.edit_failed'));
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

        $DeleteRepository = new UserDeleteRepository();

        $check = $DeleteRepository->handle($id, $user_id);

        if ($check) {
            return $this->responseSuccess([], trans('api.alert.together.delete_success'));
        } else {
            return $this->responseError(trans('api.alert.together.delete_failed'));
        }



    }




}
