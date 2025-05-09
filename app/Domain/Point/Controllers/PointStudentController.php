<?php

namespace App\Domain\Point\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\PaginateEnum;
use App\Domain\Class\Repository\ClassRepository;
use App\Domain\Point\Repository\ExamRepository;
use App\Domain\Point\Repository\PointStudentRepository;
use App\Domain\Point\Requests\DeleteExamRequest;
use App\Domain\Point\Requests\GetExamRequest;
use App\Domain\Point\Requests\GetPointStudentRequest;
use App\Domain\Point\Requests\StoreExamRequest;
use App\Domain\Point\Requests\StorePointStudentRequest;
use App\Domain\Point\Requests\UpdateExamRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PointStudentController extends BaseController
{
    public function __construct(
        protected PointStudentRepository $pointStudentRepository,
        protected ClassRepository        $classRepository
    ) {
        parent::__construct();
    }

    public function index(GetPointStudentRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value && Auth::user()->access_type != AccessTypeEnum::TEACHER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $schoolYearId = isset($request->school_year_id) ? (int)$request->school_year_id : 0;
        $classId      = isset($request->class_id) ? (int)$request->class_id : 0;
        $subjectId    = isset($request->subject_id) ? (int)$request->subject_id : 0;

        $exams = $this->pointStudentRepository->getExamBySchoolYearId($schoolYearId);

        $examPeriods   = $this->pointStudentRepository->getExamPeriodByIds($exams->pluck('id')->toArray());
        $examPeriodIds = $examPeriods->pluck('id')->toArray();
        $examPeriods   = $examPeriods->groupBy('exam_id');

        $students    = $this->classRepository->getStudentOfClass($classId);
        $studentsIds = $students->pluck('id')->toArray();

        $class        = $this->pointStudentRepository->getClassById($classId);
        $subject      = $this->pointStudentRepository->getSubjectById($subjectId);
        $pointStudent = $this->pointStudentRepository->getPointStudent($classId, $examPeriodIds, $studentsIds,
            $subjectId);
//        if ($examPeriods->count() > 0) {
//            return $this->responseSuccess($this->examRepository->transformGetExam($examPeriods));
//        } else {
//            return response()->json(['status' => 'success', 'data' => []]);
//        }
        $data = $this->pointStudentRepository->transform($class, $subject, $students, $pointStudent, $examPeriods, $exams);

        return $this->responseSuccess($data);
    }

    public function store(StorePointStudentRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value && Auth::user()->access_type != AccessTypeEnum::TEACHER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $classId   = $request->classId;
        $subjectId = $request->subjectId;
        $data      = $request->data;

        $this->pointStudentRepository->storePointStudent($classId, $subjectId, $data);

        return $this->responseSuccess([]);
    }


}
