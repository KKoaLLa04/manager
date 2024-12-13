<?php

namespace App\Domain\Point\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\PaginateEnum;
use App\Domain\Class\Repository\ClassRepository;
use App\Domain\Point\Repository\ExamRepository;
use App\Domain\Point\Repository\GuardianPointStudentRepository;
use App\Domain\Point\Repository\PointStudentRepository;
use App\Domain\Point\Requests\DeleteExamRequest;
use App\Domain\Point\Requests\GetExamRequest;
use App\Domain\Point\Requests\GetPointStudentGuardianRequest;
use App\Domain\Point\Requests\GetPointStudentRequest;
use App\Domain\Point\Requests\StoreExamRequest;
use App\Domain\Point\Requests\StorePointStudentRequest;
use App\Domain\Point\Requests\UpdateExamRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GuardianPointStudentController extends BaseController
{
    public function __construct(
        protected GuardianPointStudentRepository $pointStudentRepository,
        protected ClassRepository                $classRepository
    ) {
        parent::__construct();
    }

    public function index(GetPointStudentGuardianRequest $request)
    {
//        if (Auth::user()->access_type != AccessTypeEnum::GUARDIAN->value) {
//            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
//        }
        $schoolYearId = isset($request->school_year_id) ? (int)$request->school_year_id : 0;
        $studentId    = isset($request->student_id) ? (int)$request->student_id : 0;

        $exams = $this->pointStudentRepository->getExamBySchoolYearId($schoolYearId);

        $examPeriods   = $this->pointStudentRepository->getExamPeriodByIds($exams->pluck('id')->toArray());
        $examPeriodIds = $examPeriods->pluck('id')->toArray();
        $examPeriods   = $examPeriods->groupBy('exam_id');
        $pointStudents = $this->pointStudentRepository->getPointStudent($studentId, $examPeriodIds);

        $classIds   = $pointStudents->pluck('class_id')->toArray();
        $subjectIds = $pointStudents->pluck('subject_id')->toArray();

        $classes       = $this->pointStudentRepository->getClassesByIds($classIds);
        $subjects      = $this->pointStudentRepository->getSubjectsByIds($subjectIds);
        $pointStudents = $pointStudents->groupBy('class_id');

        $data = $this->pointStudentRepository->transform($classes, $subjects, $pointStudents, $examPeriods, $exams);
        return $this->responseSuccess($data);
    }


}
