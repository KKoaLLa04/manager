<?php

namespace App\Domain\Timetable\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Subject\Models\Subject;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\SubjectTimetableConfig;
use App\Models\TeacherSubjectTimetable;
use App\Models\Timetable;
use Illuminate\Support\Collection;

class TimetableGuardianRepository
{
    public function getTimetable(): Collection
    {
        return Timetable::query()
            ->get();
    }

    public function getTeacherSubjectTimetable(array $timetableIds, $classId): Collection
    {
        return TeacherSubjectTimetable::query()
            ->whereIn('timetable_id', $timetableIds)
            ->where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
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
        Collection $classSubjectTeachers,
    ) {
        $dataTimetable = $timetables->map(function ($timetable) use ($teacherSubjectTimetables, $classSubjectTeachers) {
            $teacherSubjectTimetable   = $teacherSubjectTimetables->where('timetable_id', $timetable->id)->first();
            $classSubjectTeacherId     = !is_null($teacherSubjectTimetable) ? $teacherSubjectTimetable->class_subject_teacher_id : 0;
            $teacherSubjectTimetableId = !is_null($teacherSubjectTimetable) ? $teacherSubjectTimetable->id : 0;
            $classSubjectTeacher       = $classSubjectTeachers->where('id', $classSubjectTeacherId)->first();
            $class_subject_teacher_id = "";
            $user_id = "";
            $user_name = "";
            $subject_id = "";
            $subject_name = "";
            if (!is_null($classSubjectTeacher)) {
                $class_subject_teacher_id = $classSubjectTeacherId;
                $user_id = is_null($classSubjectTeacher->user) ? 0 : $classSubjectTeacher->user_id;
                $user_name = is_null($classSubjectTeacher->user) ? "" : $classSubjectTeacher->user->fullname;
                $subject_id = is_null($classSubjectTeacher->subject) ? 0 : $classSubjectTeacher->subject->id;
                $subject_name = is_null($classSubjectTeacher->subject) ? "" : $classSubjectTeacher->subject->name;
            }
            return [
                'id'                           => $timetable->id,
                'day'                          => $timetable->day,
                'time'                         => $timetable->time,
                'period'                       => $timetable->period,
                'from_time'                    => $timetable->from_time,
                'to_time'                      => $timetable->to_time,
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


}
