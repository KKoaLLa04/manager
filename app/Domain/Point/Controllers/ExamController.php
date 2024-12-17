<?php

namespace App\Domain\Point\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\PaginateEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\Point\Repository\ExamRepository;
use App\Domain\Point\Requests\DeleteExamRequest;
use App\Domain\Point\Requests\GetExamRequest;
use App\Domain\Point\Requests\StoreExamRequest;
use App\Domain\Point\Requests\UpdateExamRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ExamController extends BaseController
{
    public function __construct(
        protected ExamRepository $examRepository
    ) {
        parent::__construct();
    }

    public function index(GetExamRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $search       = isset($request->search) ? $request->search : '';
        $page         = isset($request->page) ? (int)$request->page : PaginateEnum::PAGE->value;
        $size         = isset($request->size) ? (int)$request->size : PaginateEnum::MAX_SIZE->value;
        $schoolYearId = isset($request->school_year_id) ? (int)$request->school_year_id : 0;
        $exams         = $this->examRepository->getExam($search, $page, $size, $schoolYearId);
        if ($exams->count() > 0) {
            return $this->responseSuccess($this->examRepository->transformGetExam($exams));
        } else {
            return response()->json(['status' => 'success', 'data' => []]);
        }
    }

    public function store(StoreExamRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $dataInsert = [
            "name"           => $request->name ?? "",
            "school_year_id" => (int)$request->school_year_id,
            "point"          => (int)$request->point,
            "created_by"     => Auth::id(),
        ];
        $exam = $this->examRepository->storeExam($dataInsert);
        return $this->responseSuccess(["id" => $exam->id]);
    }

    public function update(UpdateExamRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $examPeriodId = $request->exam_id;
        $dataUpdate   = [
            "name"           => $request->name ?? "",
            "school_year_id" => (int)$request->school_year_id,
            "point"          => (int)$request->point,
            "updated_by"     => Auth::id(),
        ];
        $this->examRepository->updateExam($dataUpdate, $examPeriodId);
        return $this->responseSuccess([]);
    }

    public function delete(DeleteExamRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $examPeriodId = $request->exam_id;

        $this->examRepository->deleteExam($examPeriodId);
        return $this->responseSuccess([]);
    }

    public function subject(Request $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value && Auth::user()->access_type != AccessTypeEnum::TEACHER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            $subjects = $this->examRepository->getSubject();
        } else {
            $userId              = Auth::id();
            $classId             = $request->classId;
            $classSubjectTeacher = $this->examRepository->getClassSubjectTeacher($userId, $classId);
            if (is_null($classSubjectTeacher)) {
                return $this->responseSuccess([]);
            }
            $teacherType = $classSubjectTeacher->access_type;
            if ($teacherType == StatusTeacherEnum::MAIN_TEACHER->value) {
                $subjectIds = $this->examRepository->getSubjectIdsByClassId($classId);
                $subjects   = $this->examRepository->getSubject($subjectIds);
            } else {
                $subjectId = $this->examRepository->getSubjectIds($classSubjectTeacher);
                $subjects  = $this->examRepository->getSubject([$subjectId]);
            }
        }

        return $this->responseSuccess($this->examRepository->transformSubject($subjects));
    }

    public function subjectClass(Request $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value && Auth::user()->access_type != AccessTypeEnum::TEACHER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            $subjects = $this->examRepository->getSubject();
        } else {
            $userId              = Auth::id();
            $classId             = $request->classId;
            $classSubjectTeacher = $this->examRepository->getClassSubjectTeacher($userId, $classId);
            if (is_null($classSubjectTeacher)) {
                return $this->responseSuccess([]);
            }
            $teacherType = $classSubjectTeacher->access_type;
            if ($teacherType == StatusTeacherEnum::MAIN_TEACHER->value) {
                $subjectIds = $this->examRepository->getSubjectIdsByClassId($classId);
                $subjects   = $this->examRepository->getSubject($subjectIds);
            } else {
                $subjectId = $this->examRepository->getSubjectIds($classSubjectTeacher);
                $subjects  = $this->examRepository->getSubject([$subjectId]);
            }
        }

        return $this->responseSuccess($this->examRepository->transformSubject($subjects));
    }

}
