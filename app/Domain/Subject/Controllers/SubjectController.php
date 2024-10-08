<?php
namespace App\Domain\Subject\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Common\Repository\GetUserRepository;
use App\Domain\Subject\Repository\SubjectIndexRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer',
        ], [
            'integer' => trans('api.error.integer'),
            'required' => trans('api.error.required'),
        ]);

        $errors = $validator->errors()->messages();

        $checkError = false;

        if(empty($request->subjects) ){
            $errors['subjects'] = [
                trans('api.error.school_year.start_date_not_equal_end_date_before')
            ];
            $checkError = true;
        }


        if($validator->fails() || $checkError) return back()->withErrors($errors)->withInput();

    }


}
