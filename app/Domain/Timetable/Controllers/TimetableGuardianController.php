<?php

namespace App\Domain\Timetable\Controllers;

use App\Domain\Timetable\Repository\TimetableGuardianRepository;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class TimetableGuardianController extends BaseController
{
    public function __construct(
        Request                       $request,
        protected TimetableGuardianRepository $timetableRepository
    ) {
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $classId      = $request->classId;
        $timetables   = $this->timetableRepository->getTimetable();
        $timetableIds = $timetables->pluck('id')->toArray();

        $teacherSubjectTimetables = $this->timetableRepository->getTeacherSubjectTimetable($timetableIds, $classId);
        $classSubjectTeacherIds   = $teacherSubjectTimetables->pluck('class_subject_teacher_id')->toArray();
        $classSubjectTeacherIds   = array_unique($classSubjectTeacherIds);
        $classSubjectTeachers      = $this->timetableRepository->getClassSubjectTeachers($classSubjectTeacherIds,
            $classId);


        $data = $this->timetableRepository->transform(
            $timetables,
            $teacherSubjectTimetables,
            $classSubjectTeachers,
        );
        return $this->responseSuccess($data);
    }


}
