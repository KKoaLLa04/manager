<?php
namespace App\Domain\Subject\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Common\Repository\GetUserRepository;
use App\Domain\Subject\Repository\SubjectIndexRepository;
use App\Domain\Subject\Repository\SubjectMixSubjectForClassReqository;
use App\Models\ClassSubject;
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

        if ($check) {
            return $this->responseSuccess(['data' => $check->toArray()], trans('api.alert.together.index_success'));
        } else {
            return $this->responseError(trans('api.alert.together.index_failed'));
        }


    }

    public function mixSubjectForClass (Request $request) {

        $user_id = Auth::user()->id;

        if(!$this->user->getManager($user_id)){
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer',
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
            if(!empty($request->class_id)){
                foreach ($request->subjects as $sub) {
                    $exists = ClassSubject::where('class_id', $request->class_id)->where('subject_id', $sub)->first();
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


}
