<?php

namespace App\Domain\Timetable\Controllers;

use App\Domain\Timetable\Repository\TimetableRepository;
use App\Domain\Timetable\Repository\TimetableTeacherRepository;
use App\Http\Controllers\BaseController;
use App\Models\SubjectTimetableConfig;
use App\Models\Timetable;
use Carbon\Carbon;
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

    public function index(Request $request)
    {
        $userId = Auth::user()->id;
        $date = isset($request->date) ? Carbon::parse($request->date) : Carbon::now();
        $categoryTimetable = $this->timetableRepository->getCategoryTimetable($date);
        if (is_null($categoryTimetable)){
            return $this->responseSuccess(['timetables' => []]);
        }
        $teacherSubjectTimetables = $this->timetableRepository->getTeacherSubjectTimetable($userId,$categoryTimetable->id);
        $timetableIds = $teacherSubjectTimetables->pluck('timetable_id')->toArray();


        $timetables = $this->timetableRepository->getTimetable($timetableIds);
        $data = $this->timetableRepository->transform(
            $timetables,
            $teacherSubjectTimetables,
        );
        return $this->responseSuccess($data);
    }


}
