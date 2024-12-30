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

class TimetableTeacherRepository
{
    public function getTimetable(array $timetableIds): Collection
    {
        return Timetable::query()
            ->whereIn('id', $timetableIds)
            ->get();
    }

    public function getTeacherSubjectTimetable(int $userId,$categoryTimetableId): Collection
    {
        return TeacherSubjectTimetable::query()
            ->where('user_id', $userId)
            ->where('category_attendance_id', $categoryTimetableId)
            ->with(['subjectTimetable','class'])
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
    ) {
        $data = $timetables->map(function ($timetable) use ($teacherSubjectTimetables) {
            $teacherSubjectTimetable = $teacherSubjectTimetables->where('timetable_id', $timetable->id)->first();
            return [
                'timetable_id'        => $timetable->id,
                'timetable_day'       => $timetable->day,
                'timetable_time'      => $timetable->time,
                'timetable_period'    => $timetable->period,
                'timetable_from_time' => Carbon::parse($timetable->from_time)->translatedFormat('H:i'),
                'timetable_to_time'   => Carbon::parse($timetable->to_time)->translatedFormat('H:i'),
                'subject_name'        => !is_null($teacherSubjectTimetable->subjectTimetable) ? $teacherSubjectTimetable->subjectTimetable->name ?? "" : "",
                'class_id'            => !is_null($teacherSubjectTimetable->class) ? $teacherSubjectTimetable->class->id ?? 0 : "",
                'class_name'          => !is_null($teacherSubjectTimetable->class) ? $teacherSubjectTimetable->class->name ?? "" : "",
            ];
        });

        return [
            'timetables' => $data,
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
