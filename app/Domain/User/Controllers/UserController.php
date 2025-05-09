<?php

namespace App\Domain\User\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Subject\Models\Subject;
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
use App\Models\TeacherSubject;
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
        $user_id = Auth::user()->id;
        if (!$this->user->getUser($user_id, AccessTypeEnum::MANAGER->value)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $teacher = User::where('id', $id)
        ->where('access_type', AccessTypeEnum::TEACHER->value)
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->first();
            if (!$teacher) {
                return $this->responseError(trans('Người dùng không phải là giáo viên hoặc không tồn tại.'));
            }

            $subject = Subject::query()->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereNotIn('id', [16, 17])
            ->whereNotIn('name', ['Chào cờ', 'Sinh hoạt'])
            ->where('id', $request->subject_id)
            ->first();


        if (!$subject) {
            return $this->responseError(trans('Môn học không tồn tại'));
        }
        // Danh sách ID hoặc tên các môn học mặc định
        $defaultSubjects = ['Chào cờ', 'Sinh hoạt'];

        ClassSubjectTeacher::where('user_id', $id)
            ->whereNotIn('class_subject_id', function ($query) use ($defaultSubjects) {
                $query->select('id')
                    ->from('class_subject')
                    ->whereIn('subject_id', function ($subQuery) use ($defaultSubjects) {
                        $subQuery->select('id')
                                ->from('subjects')
                                ->whereIn('name', $defaultSubjects);
                    });
            })
            ->update([
                'status' => StatusEnum::UN_ACTIVE->value,
                'end_date' => now()
            ]);

        // Lấy bản ghi cũ của giáo viên
        $oldRecord = ClassSubjectTeacher::where('user_id', $id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->latest('start_date')
            ->first();
            
        // Nếu không tìm thấy bản ghi cũ, tạo bản ghi mới
        if (!$oldRecord) {
            $newRecord = ClassSubjectTeacher::create([
                'subject_id'      => $subject->id,
                'class_id'        => $request->class_id ?? null,
                'user_id'         => $id,
                'access_type'     => AccessTypeEnum::TEACHER->value,
                'status'          => StatusEnum::ACTIVE->value,
                'start_date'      => now(),
                'end_date'        => null,
                'is_deleted'      => DeleteEnum::NOT_DELETE->value,
                'created_user_id' => $user_id,
            ]);
            return $this->responseSuccess($newRecord, trans('Tạo mới bản ghi thành công.'));
        }

        // Tạo bản ghi mới
        ClassSubjectTeacher::create([
            'subject_id' => $subject->id,
            'class_id'      => $oldRecord->class_id,
            'user_id'    => $id,
            'access_type'   => $oldRecord->access_type,
            'status'     => StatusEnum::ACTIVE->value,
            'start_date' => now(),
            'end_date'   => null,
            'is_deleted' => DeleteEnum::NOT_DELETE->value,
            'created_user_id' => $user_id,
        ]);

        // Xử lý gán môn học trong TeacherSubject
        $existingAssignment = TeacherSubject::where('user_id', $id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->first();

        if ($existingAssignment) {
            $existingAssignment->update(['is_deleted' => DeleteEnum::DELETED->value]);
        }

        $data = [
            'subject_id'      => $subject->id,
            'user_id'         => $id,
            'status'          => StatusEnum::ACTIVE->value,
            'is_deleted'      => DeleteEnum::NOT_DELETE->value,
            'created_user_id' => $user_id,
        ];


        $result = TeacherSubject::create($data);

        if ($result) {
            return $this->responseSuccess([], trans('Gán môn học cho giáo viên thành công'));
        } else {
            return $this->responseError(trans('Giáo viên đã được gán vào môn học hoặc lỗi xảy ra.'));
        }

    }








}
