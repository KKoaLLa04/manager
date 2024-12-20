<?php

namespace App\Domain\User\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\User\Repository\ChooseClassToMainTearchRepository;
use App\Domain\User\Repository\UserAddRepository;
use App\Domain\User\Repository\UserChangePasswordRepository;
use App\Domain\User\Repository\UserDeleteRepository;
use App\Domain\User\Repository\UserDetailRepository;
use App\Domain\User\Repository\UserEditRepository;
use App\Domain\User\Repository\UserIndexRepository;
use App\Domain\User\Requests\UserAddRequest;
use App\Domain\User\Requests\UserEditRequest;
use App\Domain\User\Requests\UserRequest;
use App\Http\Controllers\BaseController;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class UserController extends BaseController
{
    protected $classSubjectTeacherRepository;
    private $user;

    public function __construct(Request $request,  ChooseClassToMainTearchRepository $classSubjectTeacherRepository)
    {
        // dd(Auth::user());
        $this->classSubjectTeacherRepository = $classSubjectTeacherRepository;
        $this->user = new GetUserRepository();

        parent::__construct($request);
    }


    public function index(Request $request)
    {
        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $keyword = "";

        if (!empty($request->keyword)) {
            $keyword = $request->keyword;
        }

        $pageIndex = 1;

        if (!empty($request->pageIndex)) {
            $pageIndex = $request->pageIndex;
        }

        $pageSize = 15;

        if (!empty($request->pageSize)) {
            $pageSize = $request->pageSize;
        }

        $IndexRepository = new UserIndexRepository();

        $check = $IndexRepository->handle($keyword);
        if ($check) {
            // return $this->responseSuccess(['data' => $check->forPage($pageIndex, $pageSize)], trans('api.alert.together.index_success'));
            return response()->json([
                'msg'   => trans('api.alert.together.index_success'),
                'data'  => $check->forPage($pageIndex, $pageSize)->values(),
                'total' => $check->count()
            ], ResponseAlias::HTTP_OK);
        } else {
            // return $this->responseError(trans('api.alert.together.index_failed'));
            return response()->json([
                'msg'   => trans('api.alert.together.index_success'),
                'data'  => [],
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

        $DetailRepository = new UserDetailRepository();

        $check = $DetailRepository->handle($id);

        if ($check) {
            return $this->responseSuccess(['data' => $check], trans('api.alert.together.detail_success'));
        } else {
            return $this->responseError(trans('api.alert.together.detail_failed'));
        }
    }

    public function add(UserAddRequest $request)
    {
        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $AddRepository = new UserAddRepository();

        $check = $AddRepository->handle($user_id, $request);

        if ($check) {
            // $data = User::all();
            // $data = $data->last();
            // return $this->responseSuccess(['data' => $data->infoMainTearchWithClass()], trans('api.alert.together.add_success'));
            return response()->json([
                'msg'  => trans('api.alert.together.add_success'),
                'data' => [],
            ], ResponseAlias::HTTP_OK);
        } else {
            // return $this->responseError(trans('api.alert.together.add_failed'));
            return response()->json([
                'msg'  => trans('api.alert.together.add_failed'),
                'data' => [],
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function edit(int $id, UserEditRequest $request)
    {
        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $EditRepository = new UserEditRepository();

        $check = $EditRepository->handle($id, $user_id, $request);

        if ($check) {
            // $data = User::find($id);
            // return $this->responseSuccess(['data' => $data->infoMainTearchWithClass()], trans('api.alert.together.edit_success'));
            return response()->json([
                'msg'  => trans('api.alert.together.edit_success'),
                'data' => [],
            ], ResponseAlias::HTTP_OK);
        } else {
            // return $this->responseError(trans('api.alert.together.edit_failed'));
            return response()->json([
                'msg'  => trans('api.alert.together.edit_failed'),
                'data' => [],
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function delete(int $id, Request $request)
    {
        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
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


    public function chooseClassToMainTearch(Request $request)
    {
        $request->validate([
            'schoolYearId' => 'required'
        ]);

        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $ChooseClassToMainTearchRepository = new ChooseClassToMainTearchRepository();

        $check = $ChooseClassToMainTearchRepository->handle($request->schoolYearId);

        return $this->responseSuccess($check, trans('api.alert.together.index_success'));
    }


    public function changePassword(int $id, Request $request)
    {
        $user_id = Auth::user()->id;

        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $request->validate([
            'userPassword' => 'required|min:3|max:255'
        ], [
            'min'      => trans('api.error.min'),
            'max'      => trans('api.error.max'),
            'required' => trans('api.error.required'),
        ]);

        $repository = new UserChangePasswordRepository();

        $check = $repository->handle($id, $user_id, $request);

        if ($check) {
            return $this->responseSuccess([], trans('api.alert.together.edit_success'));
        } else {
            return $this->responseError(trans('api.alert.together.edit_failed'));
        }
    }

    public function assignTeacher(UserRequest $request, $id)
    {
        // Kiểm tra quyền của user
        $user_id = Auth::user()->id;
        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Kiểm tra xem môn học có tồn tại không
        $classSubject = ClassSubject::where('subject_id', $request->subject_id)  // Sử dụng 'subject_id' thay vì 'class_subject_id'
            ->where('is_deleted', 0)
            ->first();

        if (!$classSubject) {
            return $this->responseError(trans('api.error.class_subject_not_found'));
        }

        // Chuẩn bị dữ liệu cho việc gán giáo viên
        $data = [
            'class_subject_id' => $classSubject->id,  // Sử dụng ID môn học tìm được
            'class_id'         => null, // Lớp học là null vì chỉ gán môn học cho giáo viên
            'user_id'          => $id, // Lấy ID giáo viên từ tham số route
            'start_date'       => now(), // Ngày bắt đầu là ngày hiện tại
            'end_date'         => null, // Ngày kết thúc là null
            'status'           => 1, // Giáo viên đang dạy
            'access_type'      => AccessTypeEnum::TEACHER->value, // Quyền giáo viên
            'is_deleted'       => 0, // Giáo viên chưa bị xóa
            'created_user_id'  => $user_id, // Người tạo
        ];

        // Gọi repository để gán giáo viên vào môn học
        $result = $this->classSubjectTeacherRepository->assignTeacherToSubject($data);

        if ($result) {
            return $this->responseSuccess([], trans('api.alert.together.assign_success'));
        } else {
            return $this->responseError(trans('giáo viên đã được gán môn học'));
        }
    }








}
