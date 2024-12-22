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

class TimetableTeacherRepository
{
    public function getTimetable(array $timetableIds): Collection
    {
        return Timetable::query()
            ->whereIn('id', $timetableIds)
            ->get();
    }

    public function getTeacherSubjectTimetable(array $classSubjectTeacherIds): Collection
    {
        return TeacherSubjectTimetable::query()
            ->whereIn('class_subject_teacher_id', $classSubjectTeacherIds)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function getClassSubjectTeachers(int $userId): Collection
    {
        return ClassSubjectTeacher::query()
            ->where('user_id', $userId)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereNull('end_date')
            ->with(
                [
                    'subject',
                    'class'
                ]
            )->get();
    }

    public function transform(
        Collection $timetables,
        Collection $teacherSubjectTimetables,
        Collection $classSubjectTeachers,
    ) {
        $data = $timetables->map(function ($timetable) use ($teacherSubjectTimetables, $classSubjectTeachers) {
            $teacherSubjectTimetable = $teacherSubjectTimetables->where('timetable_id', $timetable->id)->first();
            $classSubjectTeachers    = $classSubjectTeachers->where('id',
                $teacherSubjectTimetable->class_subject_teacher_id)->first();
            return [
                'timetable_id'        => $timetable->id,
                'timetable_day'       => $timetable->day,
                'timetable_time'      => $timetable->time,
                'timetable_period'    => $timetable->period,
                'timetable_from_time' => $timetable->from_time,
                'timetable_to_time'   => $timetable->to_time,
                'subject_name'        => $classSubjectTeachers->subject->name,
                'class_id'            => $classSubjectTeachers->class->id,
                'class_name'          => $classSubjectTeachers->class->name,
            ];
        });

        return [
            'timetables' => $data,
        ];
    }

}
