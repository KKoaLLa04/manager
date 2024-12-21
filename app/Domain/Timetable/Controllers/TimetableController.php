<?php

namespace App\Domain\Timetable\Controllers;

use App\Domain\Timetable\Repository\TimetableRepository;
use App\Http\Controllers\BaseController;
use App\Models\SubjectTimetableConfig;
use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends BaseController
{
    public function __construct(
        Request                       $request,
        protected TimetableRepository $timetableRepository
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
        $classSubjectTeacher      = $this->timetableRepository->getClassSubjectTeachers($classSubjectTeacherIds,
            $classId);


        $classSubjectTeacherByClassId = $this->timetableRepository->getClassSubjectTeachersByClassId($classId);

        $data = $this->timetableRepository->transform(
            $timetables,
            $teacherSubjectTimetables,
            $classSubjectTeacher,
            $classSubjectTeacherByClassId
        );
        return $this->responseSuccess($data);
    }

    public function editTimetable(Request $request)
    {
        $classId               = $request->classId;
        $userId                = $request->userId;
        $timetableId           = $request->timetableId;
        $subjectId             = $request->subjectId;
        $classSubjectTeacherId = $request->classSubjectTeacherId;

        $classSubjectTeacher          = $this->timetableRepository->getClassSubjectTeachersByUserIdAndClassId($userId);
        $classSubjectTeachersId       = $classSubjectTeacher->pluck('id')->toArray();
        $teacherSubjectTimeTable      = $this->timetableRepository->checkUserExistTimetable($classSubjectTeachersId,
            $timetableId,
            $classId);
        $countTeacherSubjectTimetable = $this->timetableRepository->countUserTimetable($classSubjectTeachersId,
            $classId);

        $quantitySubjectConfig = $this->timetableRepository->subjectConfig($subjectId);
        if ($countTeacherSubjectTimetable >= $quantitySubjectConfig->quantity){
            return $this->responseError('Môn học đã đủ ' . $quantitySubjectConfig->quantity .' tiết');
        }
        if (!is_null($teacherSubjectTimeTable)) {
            return $this->responseError('Giáo viên đag có tiết dạy ở lớp: '.$teacherSubjectTimeTable->class->name);
        }
        $checkTeacherSubjectTimeTableExits = $this->timetableRepository->checkUserExistTimetableOfClass($timetableId,
            $classId);

        if ($checkTeacherSubjectTimeTableExits) {
            $this->timetableRepository->updateTeacherSubjectTeacher($classSubjectTeacherId, $timetableId, $classId);
        } else {
            $this->timetableRepository->createTeacherSubjectTeacher($classSubjectTeacherId, $timetableId, $classId);
        }

        return $this->responseSuccess([], 'Thục hiện thành công');
    }

    public function indexConfig()
    {
        $timetables = Timetable::query()->get();
        $timetables = $timetables->groupBy('time');
        $data       = [];
        foreach ($timetables as $key => $timetable) {
            $timetable  = $timetable->unique('period');
            $data[$key] = $timetable->map(function ($item) {
                return [
                    "period"    => $item->period,
                    "from_time" => $item->from_time,
                    "to_time"   => $item->to_time,
                ];
            })->toArray();
        }
        return $this->responseSuccess($data);
    }

    public function editConfig(Request $request)
    {
        $data = $request->data;
        foreach ($data as $item) {
            $dataUpdate = [
                "from_time" => $item['from_time'],
                "to_time"   => $item['to_time'],
            ];
            Timetable::query()->where('period', $item['period'])
                ->where('time', $item['time'])->update($dataUpdate);
        }

        return $this->responseSuccess($data);
    }

    public function getSubjectConfig()
    {
        $subjects = $this->timetableRepository->getSubject();

        $subjectTimetableConfigs = $this->timetableRepository->getSubjectTimeTableConfig();

        return $this->responseSuccess($this->timetableRepository->transformSubjectTimetableConfig($subjects,
            $subjectTimetableConfigs));
    }

    public function editSubjectConfig(Request $request)
    {
        $data = $request->data;
        foreach ($data as $item) {
            $dataUpdate = [
                "quantity" => $item['quantity'],
            ];
            SubjectTimetableConfig::query()->where('id', $item['id'])
                ->update($dataUpdate);
        }

        return $this->responseSuccess($data);
    }
}
