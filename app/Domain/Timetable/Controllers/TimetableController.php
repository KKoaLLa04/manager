<?php

namespace App\Domain\Timetable\Controllers;

use App\Domain\Timetable\Repository\TimetableRepository;
use App\Http\Controllers\BaseController;
use App\Models\SubjectTimetableConfig;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $classId             = $request->classId;
        $timetables          = $this->timetableRepository->getTimetable();
        $categoryTimetableId = $request->categoryTimetableId;
        $timetableIds        = $timetables->pluck('id')->toArray();

        $teacherSubjectTimetables = $this->timetableRepository->getTeacherSubjectTimetable($timetableIds, $classId,
            $categoryTimetableId);


        $classSubjectTeacherByClassId = $this->timetableRepository->getClassSubjectTeachersByClassId($classId);

        $data = $this->timetableRepository->transform(
            $timetables,
            $teacherSubjectTimetables,
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
        $categoryTimetableId   = $request->categoryTimetableId;

        $teacherSubjectTimeTable      = $this->timetableRepository->checkUserExistTimetable($userId, $subjectId,
            $categoryTimetableId,
            $timetableId,
            $classId);
        $countTeacherSubjectTimetable = $this->timetableRepository->countUserTimetable($subjectId,
            $categoryTimetableId, $classId);
        $quantitySubjectConfig        = $this->timetableRepository->subjectConfig($subjectId);
        if ($subjectId != 0 && $countTeacherSubjectTimetable >= $quantitySubjectConfig->quantity) {
            return $this->responseError('Môn học đã đủ '.$quantitySubjectConfig->quantity.' tiết');
        }
        if ($userId != 0 && !is_null($teacherSubjectTimeTable)) {
            return $this->responseError('Giáo viên đag có tiết dạy ở lớp: '.$teacherSubjectTimeTable->class->name);
        }
        $checkTeacherSubjectTimeTableExits = $this->timetableRepository->checkUserExistTimetableOfClass($timetableId,
            $classId, $categoryTimetableId);

        if ($checkTeacherSubjectTimeTableExits) {
            $this->timetableRepository->updateTeacherSubjectTeacher($classSubjectTeacherId, $timetableId, $classId,
                $userId, $subjectId, $categoryTimetableId);
        } else {
            $this->timetableRepository->createTeacherSubjectTeacher($classSubjectTeacherId, $timetableId, $classId,
                $userId, $subjectId, $categoryTimetableId);
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

    public function import(Request $request)
    {
        $data                = $request->all();
        $userId              = Auth::user()->id;
        $classId             = $request->class_id;
        $time                = $request->time;
        $categoryTimetableId = $request->category_attendance;
        $periods             = isset($data['periods']) ? $data['periods'] : [];
        if (count($periods) > 5) {
            return $this->responseError('Dữ liệu bảng sai cấu trúc');
        }
        $message = [];

        foreach ($periods as $key => $period) {
            $periodId = $period['period'];
            foreach ($period['subjects'] as $subject) {

                $name = $subject['name'];
                $day = $subject['day'];
                $subject = $this->timetableRepository->getSubjectByName($name);
                if (is_null($subject)) {
                    $message[] = 'tên môn học không tồn tại ' . $name;
                    continue;
                }
                $subjectId = $subject->id;
                $classSubjectTeacher = $this->timetableRepository->getClassSubjectTeachersByClassIdAndSubjectId($classId, $subjectId);
                if (is_null($classSubjectTeacher)) {
                    $message[] = 'Môn học: '.$name.' chưa được gán cho giáo viên dạy. ';
                    continue;
                }

                $classSubjectTeacherId = $classSubjectTeacher->id;
                $timetable = $this->timetableRepository->getTimetableByDayAndTime($time,$day, $periodId);
                if (is_null($timetable)) {
                    $message[] = 'Tiết hoặc ngày không tồn tại ';
                    continue;
                }
                $timetableId = $timetable->id;


                $teacherSubjectTimeTable      = $this->timetableRepository->checkUserExistTimetable($userId, $subjectId,
                    $categoryTimetableId,
                    $timetableId,
                    $classId);
                $countTeacherSubjectTimetable = $this->timetableRepository->countUserTimetable($subjectId,
                    $categoryTimetableId, $classId);
                $quantitySubjectConfig        = $this->timetableRepository->subjectConfig($subjectId);
                if ($subjectId != 0 && $countTeacherSubjectTimetable >= $quantitySubjectConfig->quantity) {
                    $message[] = 'Môn học: '. $name .' đã đủ '.$quantitySubjectConfig->quantity.' tiết vào thứ ' . $day + 1 . ' tiet: '.$periodId;
                    continue;
                }
                if ($userId != 0 && !is_null($teacherSubjectTimeTable)) {
                    $message[] = 'Giáo viên đag có tiết dạy ở lớp: '.$teacherSubjectTimeTable->class->name . 'của môn học ' . $name . ' vào thứ ' . $day + 1 . ' tiet: '.$periodId;
                    continue;
                }
                $checkTeacherSubjectTimeTableExits = $this->timetableRepository->checkUserExistTimetableOfClass($timetableId,
                    $classId, $categoryTimetableId);
                if ($checkTeacherSubjectTimeTableExits) {
                    $this->timetableRepository->updateTeacherSubjectTeacher($classSubjectTeacherId, $timetableId, $classId,
                        $userId, $subjectId, $categoryTimetableId);
                } else {
                    $this->timetableRepository->createTeacherSubjectTeacher($classSubjectTeacherId, $timetableId, $classId,
                        $userId, $subjectId, $categoryTimetableId);
                }

            }

        }
        return $this->responseSuccess($message);
    }
}
