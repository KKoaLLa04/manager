<?php

namespace App\Domain\Timetable\Controllers;

use App\Domain\Timetable\Repository\TimetableGuardianRepository;
use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimetableGuardianController extends BaseController
{
    public function __construct(
        Request                               $request,
        protected TimetableGuardianRepository $timetableRepository
    ) {
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $classId      = $request->classId;
        $date         = isset($request->date) ? Carbon::parse($request->date) : Carbon::now();
        $categoryTimetable = $this->timetableRepository->getCategoryTimetable($date);
        if (is_null($categoryTimetable)){
            return $this->responseSuccess();
        }
        $timetables   = $this->timetableRepository->getTimetable();
        $timetableIds = $timetables->pluck('id')->toArray();

        $teacherSubjectTimetables = $this->timetableRepository->getTeacherSubjectTimetable($timetableIds, $classId,
            $categoryTimetable->id);



        $data = $this->timetableRepository->transform(
            $timetables,
            $teacherSubjectTimetables,
        );
        return $this->responseSuccess($data);
    }


}
