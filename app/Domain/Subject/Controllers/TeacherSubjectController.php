<?php

namespace App\Domain\Subject\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Domain\Subject\Repository\TeacherSubjectRepository;
use App\Http\Controllers\BaseController;
use App\Models\SubjectTimetableConfig;
use Database\Seeders\SubjectConfigSeeder;
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

class TeacherSubjectController extends BaseController
{

    private $user;

    public function __construct(Request $request)
    {
        $this->user = new GetUserRepository();
        parent::__construct($request);
    }


    public function index(Request $request)
    {
        $classId = $request->class_id;
        $userId  = Auth::id();
        if (Auth::user()->access_type != AccessTypeEnum::TEACHER->value) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }


        $teacherSubjectRepository = new TeacherSubjectRepository();

        $getClassMainTeacher = $teacherSubjectRepository->getClassMainTeacher($classId, $userId);
        if (!is_null($getClassMainTeacher)) {
            $classSubjects = $teacherSubjectRepository->getClassSubject($classId, $userId, true);
            $data          = $classSubjects->map(function ($classSubject) use ($getClassMainTeacher) {
                $classSubjectIds = $getClassMainTeacher->pluck('class_subject_id')->toArray();
                $mainTeacher = (in_array($classSubject->id, $classSubjectIds)  ? 1 : 0);
                return [
                    'subject_id'   => !is_null($classSubject->subject) ? $classSubject->subject->id : "",
                    'subject_name' => !is_null($classSubject->subject) ? $classSubject->subject->name : "",
                    'is_teach'     => $mainTeacher,
                    'main_teacher' => 1,
                ];
            })->toArray();
        } else {
            $classSubjects = $teacherSubjectRepository->getClassSubject($classId, $userId, false);
            $data          = $classSubjects->map(function ($classSubject) {
                return [
                    'subject_id'   => !is_null($classSubject->subject) ? $classSubject->subject->id : "",
                    'subject_name' => !is_null($classSubject->subject) ? $classSubject->subject->name : "",
                    'is_teach'     => 1,
                    'main_teacher' => 0,
                ];
            })->toArray();
        }
        return  $this->responseSuccess($data);
    }


}
