<?php

namespace App\Domain\Timetable\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Subject\Models\Subject;
use App\Models\CategoryAttendance;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\SubjectTimetableConfig;
use App\Models\TeacherSubjectTimetable;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimetableGuardianRepository
{
    public function getTimetable(): Collection
    {
        return Timetable::query()
            ->get();
    }

    public function getTeacherSubjectTimetable(array $timetableIds, $classId,$categoryTimetableId): Collection
    {
        return TeacherSubjectTimetable::query()
            ->whereIn('timetable_id', $timetableIds)
            ->where('class_id', $classId)
            ->where('category_attendance_id', $categoryTimetableId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with([
                'subjectTimetable',
                'teacher',
            ])
            ->get();
    }

    public function getClassSubjectTeachers(array $ids, int $classId): Collection
    {
        return ClassSubjectTeacher::query()
            ->whereIn('id', $ids)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereNull('end_date')
            ->where('class_id', $classId)
            ->with(
                [
                    'teacher',
                    'subject'
                ]
            )->get();
    }

    public function transform(
        Collection $timetables,
        Collection $teacherSubjectTimetables,
    ) {
        $dataTimetable = $timetables->map(function ($timetable) use ($teacherSubjectTimetables) {
            $teacherSubjectTimetable   = $teacherSubjectTimetables->where('timetable_id', $timetable->id)->first();
            $classSubjectTeacherId     = !is_null($teacherSubjectTimetable) ? $teacherSubjectTimetable->class_subject_teacher_id : 0;
            $teacherSubjectTimetableId = !is_null($teacherSubjectTimetable) ? $teacherSubjectTimetable->id : 0;
            $class_subject_teacher_id = "";
            $user_id = "";
            $user_name = "";
            $subject_id = "";
            $subject_name = "";
            if (!is_null($teacherSubjectTimetable)) {
                $class_subject_teacher_id = $classSubjectTeacherId;
                $user_id      = isset($teacherSubjectTimetable->teacher) ? $teacherSubjectTimetable->teacher->id : "";
                $user_name    = isset($teacherSubjectTimetable->teacher) ? $teacherSubjectTimetable->teacher->fullname ?? "" : "";
                $subject_id   = isset($teacherSubjectTimetable->subjectTimetable) ? $teacherSubjectTimetable->subjectTimetable->id : "";
                $subject_name = isset($teacherSubjectTimetable->subjectTimetable) ? $teacherSubjectTimetable->subjectTimetable->name ?? "" : "";
            }
            return [
                'id'                           => $timetable->id,
                'day'                          => $timetable->day,
                'time'                         => $timetable->time,
                'period'                       => $timetable->period,
                'from_time'                    => Carbon::parse($timetable->from_time)->translatedFormat('H:i'),
                'to_time'                      => Carbon::parse($timetable->to_time)->translatedFormat('H:i'),
                'teacher_subject_timetable_id' => $teacherSubjectTimetableId,
                'class_subject_teacher_id'     => $class_subject_teacher_id,
                'user_id'                      => $user_id,
                'user_name'                    => $user_name,
                'subject_id'                   => $subject_id,
                'subject_name'                 => $subject_name,
            ];
        });
        $dataTimetables = $dataTimetable->groupBy('time');

        $data = [];
        foreach ($dataTimetables as $key => $timetable) {
            $days     = $timetable->groupBy('day');
            $dayValue = [];
            foreach ($days as $keyDay => $day) {
                $dayValue[] = [
                    'day'    => $keyDay,
                    'period' => $day->sortBy('period')->toArray(),
                ];
            }
            $data[] = [
                'time' => $key,
                'days' => $dayValue,
            ];
        }

        return [
            'timetables'       => $data,
        ];
    }

    public function getCategoryTimetable(Carbon $date)
    {
        return CategoryAttendance::query()
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('from_date','<=', $date->toDateString())
            ->where('to_date','>=', $date->toDateString())
            ->first();
    }


}
