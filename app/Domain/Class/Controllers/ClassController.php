<?php

namespace App\Domain\Class\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\CheckAcademicExitsRepository;
use App\Common\Repository\GetSchoolYearRepository;
use App\Common\Repository\GetUserRepository;
use App\Common\Repository\GradeRepository;
use App\Domain\Class\Repository\ClassRepository;
use App\Domain\Class\Repository\CreateClassRepository;
use App\Domain\Class\Repository\DeleteClassRepository;
use App\Domain\Class\Repository\UpdateClassRepository;
use App\Domain\Class\Requests\AssignMainTeacherRequest;
use App\Domain\Class\Requests\CreateClassRequest;
use App\Domain\Class\Requests\DeleteClassRequest;
use App\Domain\Class\Requests\DetailClassRequest;
use App\Domain\Class\Requests\GetClassRequest;
use App\Domain\Class\Requests\UpdateClassRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ClassController extends BaseController
{
    public function __construct(
        Request                                $request,
        protected ClassRepository              $classRepository,
        protected GetSchoolYearRepository      $schoolYearRepository,
        protected CreateClassRepository        $createClassRepository,
        protected CheckAcademicExitsRepository $checkAcademicExitsRepository,
        protected GetUserRepository            $getUserRepository,
        protected GradeRepository              $gradeRepository,
        protected UpdateClassRepository        $updateClassRepository,
        protected DeleteClassRepository        $deleteClassRepository
    ) {
        parent::__construct($request);
    }

    public function index(GetClassRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $checkSchoolYearId = $this->schoolYearRepository->checkSchoolYearId($request->school_year_id);
        if (!$checkSchoolYearId) {
            return $this->responseError(trans('api.error.not_found'));
        }

        list($totalPage, $page, $size, $classes) = $this->classRepository->getClasses($request);
        return $this->responseSuccess($this->classRepository->transform($page, $totalPage, $size, $classes));
    }

    public function detail(DetailClassRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $class = $this->classRepository->detailClass($request->class_id);
        if(is_null($class)){
            return $this->responseSuccess();
        }

        $students = $this->classRepository->getStudentOfClass($request->class_id);
    }

    public function form()
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $grades       = $this->classRepository->getGrades();
        $academicYear = $this->classRepository->getAcademicYear();
        $schoolYear   = $this->schoolYearRepository->getSchoolYear();
        $teachers     = $this->classRepository->getTeachers();
        return $this->responseSuccess($this->classRepository->transformDataCreate($grades, $academicYear, $schoolYear,
            $teachers));
    }

    public function create(CreateClassRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $checkSchoolYear = $this->schoolYearRepository->checkSchoolYearId($request->school_year_id);
        if (!$checkSchoolYear) {
            return $this->responseError(trans('api.error.not_found'));
        }

        $checkAcademic = $this->checkAcademicExitsRepository->checkAcademicId($request->academic_id);
        if (!$checkAcademic) {
            return $this->responseError(trans('api.error.not_found'));
        }

        $checkTeacher = $this->getUserRepository->getUser($request->teacher_id, AccessTypeEnum::TEACHER->value);
        if (!$checkTeacher) {
            return $this->responseError(trans('api.error.not_found'));
        }

        $checkGrade = $this->gradeRepository->checkGradeExits($request->grade_id);
        if (!$checkGrade) {
            return $this->responseError(trans('api.error.not_found'));
        }

        $statusCreateClass = $this->createClassRepository->createClass($request);
        if ($statusCreateClass) {
            $classId = $statusCreateClass->id;
            $this->createClassRepository->createClassTeacherSubject($classId,
                $request->teacher_id);

            return $this->responseSuccess();
        }
        return $this->responseError();
    }

    public function update(UpdateClassRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }


        $checkTeacher = $this->getUserRepository->getUser($request->teacher_id, AccessTypeEnum::TEACHER->value);
        if (!$checkTeacher) {
            return $this->responseError(trans('api.error.not_found'));
        }

        $checkGrade = $this->gradeRepository->checkGradeExits($request->grade_id);
        if (!$checkGrade) {
            return $this->responseError(trans('api.error.not_found'));
        }

        $statusCreateClass = $this->updateClassRepository->UpdateClass($request);
        if ($statusCreateClass) {
            $classId = $request->class_id;
            $this->updateClassRepository->createClassTeacherSubject($classId,
                $request->teacher_id);

            return $this->responseSuccess();
        }
        return $this->responseError();
    }

    public function delete(DeleteClassRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $statusCreateClass = $this->deleteClassRepository->deleteClass($request);
        if ($statusCreateClass) {
            $classId                         = $request->class_id;
            $statusCreateClassSubjectTeacher = $this->deleteClassRepository->updateClassTeacherSubject($classId);
            if ($statusCreateClassSubjectTeacher) {
                return $this->responseSuccess();
            }
            return $this->responseError();
        }
        return $this->responseError();
    }

    public function formAssignMainTeacher()
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $teachers = $this->classRepository->getTeachers();

        return $this->responseSuccess($this->classRepository->transformDataAssign($teachers));
    }

    public function assignMainTeacher(AssignMainTeacherRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $this->updateClassRepository->createClassTeacherSubject($request->class_id,
            $request->teacher_id);
        return $this->responseSuccess();
    }
}
