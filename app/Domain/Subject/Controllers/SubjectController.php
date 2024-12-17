<?php
namespace App\Domain\Subject\Controllers;

use App\Common\Enums\DeleteEnum;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Common\Repository\GetUserRepository;
use App\Domain\Subject\Models\Subject;
use App\Domain\Subject\Repository\SubjectClassNoHasSubjectRepository;
use App\Domain\Subject\Repository\SubjectCurrentClassRepository;
use App\Domain\Subject\Repository\SubjectIndexRepository;
use App\Domain\Subject\Repository\SubjectMixSubjectForClassReqository;
use App\Models\ClassSubject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
class SubjectController extends BaseController
{

    private $user;

    public function __construct(Request $request)
    {

        $this->user = new GetUserRepository();
        parent::__construct($request);
    }


    public function index(Request $request)
    {

        $user_id = Auth::user()->id;

        if (!$this->user->getManager($user_id)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $IndexRepository = new SubjectIndexRepository();

        $check = $IndexRepository->handle();

        // if ($check) {
        //     return $this->responseSuccess(['data' => $check->toArray()], trans('api.alert.together.index_success'));
        // } else {
        //     return $this->responseError(trans('api.alert.together.index_failed'));
        // }

        if ($check) {

            return response()->json([
                'msg' => trans('api.alert.together.index_success'),
                'data' => $check->toArray(),
                'total' => $check->count()
            ]);

        }else{

            return response()->json([
                'msg' => trans('api.alert.together.index_success'),
                'data' => [],
                'total' => 0
            ]);

        }

    }


    public function mixSubjectForClass (Request $request) {

        $user_id = Auth::user()->id;

        if(!$this->user->getManager($user_id)){
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $validator = Validator::make($request->all(), [
            'classId' => 'required|integer',
        ], [
            'integer' => trans('api.error.integer'),
            'required' => trans('api.error.required'),
        ]);

        $errors = $validator->errors()->messages();

        $checkError = false;

        if(empty($request->subjects) || !is_array($request->subjects)){
            $errors['subjects'] = [
                trans('api.error.subject.subjects_array_required')
            ];
            $checkError = true;
        }else{
            if(!empty($request->classId)){
                foreach ($request->subjects as $sub) {
                    $exists = ClassSubject::where('class_id', $request->classId)->where('subject_id', $sub)->first();
                    if($exists){
                        $errors['subjects'] = [
                            trans('api.error.subject.have_subject_class')
                        ];
                        $checkError = true;
                    }
                }
            }
        }

        // Nếu có lỗi trong validation hoặc lỗi tùy chỉnh
        if ($validator->fails() || $checkError) {
            // Tùy chỉnh mảng lỗi để trả về
            $customErrors = [];
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $customErrors[$field] = [
                        $message,
                    ];
                }
            }

            // Trả về phản hồi JSON với lỗi
            return $this->responseValidate($customErrors);

        }

        $reqository = new SubjectMixSubjectForClassReqository();

        $check = $reqository->handle($user_id, $request->subjects, $request);

        if($check){
            return $this->responseSuccess([], trans('api.alert.together.add_success'));
        }else{
            return $this->responseError(trans('api.alert.together.add_failed'));
        }


    }


    public function currentClass (Request $request) {

        $user_id = Auth::user()->id;

        if (!$this->user->getManager($user_id)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $request->validate([
            "schoolYearId" => 'required'
        ], [
            'required' => trans('api.error.required')
        ]);

        $reqository = new SubjectCurrentClassRepository();

        $check = $reqository->handle($request->schoolYearId);

        if ($check) {

            return response()->json([
                'msg' => trans('api.alert.together.index_success'),
                'data' => $check->toArray(),
                'total' => $check->count()
            ]);

        }else{

            return response()->json([
                'msg' => trans('api.alert.together.index_success'),
                'data' => [],
                'total' => 0
            ]);

        }

    }


    public function classNoHasSubject (Request $request) {

        $user_id = Auth::user()->id;

        if (!$this->user->getManager($user_id)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $request->validate([
            "classId" => 'required'
        ], [
            'required' => trans('api.error.required')
        ]);


        $reqository = new SubjectClassNoHasSubjectRepository();

        $check = $reqository->handle($request->classId);


        if ($check) {

            return response()->json([
                'msg' => trans('api.alert.together.index_success'),
                'data' => array_filter($check->toArray()),
                'total' => $check->count()
            ]);

        }else{

            return response()->json([
                'msg' => trans('api.alert.together.index_success'),
                'data' => [],
                'total' => 0
            ]);

        }


    }


    public function create(Request $request)
    {
        $user_id = Auth::user()->id;

        if (!$this->user->getManager($user_id)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }


        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $subject = Subject::create([
                'name' => $validated['name'],
                'is_deleted' => DeleteEnum::NOT_DELETE->value,
                'created_user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Môn học được tạo thành công!',
                'data' => [],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo môn học.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $subject = Subject::find($id);

    if (!$subject) {
        return response()->json([
            'success' => false,
            'message' => 'Môn học không tồn tại.',
        ], 404);
    }

    try {
        $subject->update([
            'name' => $validated['name'],
            'modified_user_id' => auth()->id(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Môn học được cập nhật thành công!',
            'data' => [],
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi cập nhật môn học.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function delete($id)
{
    $subject = Subject::find($id);

    if (!$subject) {
        return response()->json([
            'success' => false,
            'message' => 'Môn học không tồn tại.',
        ], 404);
    }

    try {
        // $subject->update([
        //     'is_deleted' => DeleteEnum::DELETED->value, // Đánh dấu đã xóa (soft delete)
        //     'modified_user_id' => auth()->id(),
        //     'updated_at' => now(),
        // ]);

        $subject->is_deleted = DeleteEnum::DELETED->value;
        $subject->modified_user_id = Auth::id();
        $subject->updated_at = Carbon::now();
        $subject->save();


        return response()->json([
            'success' => true,
            'message' => 'Môn học đã được xóa thành công!',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi xóa môn học.',
            'error' => $e->getMessage(),
        ], 500);
    }
}





}
