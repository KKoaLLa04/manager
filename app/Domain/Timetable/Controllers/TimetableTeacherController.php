<?php

namespace App\Domain\Timetable\Controllers;

use App\Domain\Timetable\Repository\TimetableRepository;
use App\Domain\Timetable\Repository\TimetableTeacherRepository;
use App\Http\Controllers\BaseController;
use App\Models\SubjectTimetableConfig;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimetableTeacherController extends BaseController
{
    public function __construct(
        Request                       $request,
        protected TimetableTeacherRepository $timetableRepository
    ) {
        parent::__construct($request);
    }

    public function index()
    {
        $userId = Auth::user()->id;
        $classSubjectTeachers = $this->timetableRepository->getClassSubjectTeachers($userId);
        $classSubjectTeacherIds = $classSubjectTeachers->pluck('id')->toArray();

        $teacherSubjectTimetables = $this->timetableRepository->getTeacherSubjectTimetable($classSubjectTeacherIds);
        $timetableIds = $teacherSubjectTimetables->pluck('timetable_id')->toArray();


        $timetables = $this->timetableRepository->getTimetable($timetableIds);
        $data = $this->timetableRepository->transform(
            $timetables,
            $teacherSubjectTimetables,
            $classSubjectTeachers,
        );
        return $this->responseSuccess($data);
    }


}
