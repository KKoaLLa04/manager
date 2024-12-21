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

class TimetableRepository
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
        Collection $classSubjectTeacherByClassId
    ) {
        $dataTimetable = $timetables->map(function ($timetable) use ($teacherSubjectTimetables, $classSubjectTeachers) {
            $teacherSubjectTimetable   = $teacherSubjectTimetables->where('timetable_id', $timetable->id)->first();
            $classSubjectTeacherId     = !is_null($teacherSubjectTimetable) ? $teacherSubjectTimetable->class_subject_teacher_id : 0;
            $teacherSubjectTimetableId = !is_null($teacherSubjectTimetable) ? $teacherSubjectTimetable->id : 0;
            $classSubjectTeacher       = $classSubjectTeachers->where('id', $classSubjectTeacherId)->first();
            $teacherSubject            = [];
            if (!is_null($classSubjectTeacher)) {
                $teacherSubject = [
                    'class_subject_teacher_id' => $classSubjectTeacher->id,
                    'user_id'                  => $classSubjectTeacher->user_id,
                    'subject_id'               => $classSubjectTeacher->subject->id,
                    'subject_name'             => $classSubjectTeacher->subject->name,
                ];
            }
            return [
                'id'                           => $timetable->id,
                'day'                          => $timetable->day,
                'time'                         => $timetable->time,
                'period'                       => $timetable->period,
                'from_time'                    => $timetable->from_time,
                'to_time'                      => $timetable->to_time,
                'teacher_subject_timetable_id' => $teacherSubjectTimetableId,
                'teacher_subject'              => $teacherSubject,
            ];
        });
//        $dataTimetable = $dataTimetable->sortBy('day');
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

        $subjectClass = $classSubjectTeacherByClassId->map(function ($teacherSubjectTeacher) {
            return [
                'class_subject_teacher_id' => $teacherSubjectTeacher->id,
                'user_id'                  => $teacherSubjectTeacher->user_id,
                'subject_id'               => $teacherSubjectTeacher->subject->id,
                'subject_name'             => $teacherSubjectTeacher->subject->name,
            ];
        })->toArray();

        return [
            'timetables'       => $data,
            'subject_teachers' => $subjectClass,
        ];
    }

    public function getClassSubjectTeachersByClassId(int $classId): Collection
    {
        return ClassSubjectTeacher::query()
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

    public function getClassSubjectTeachersByUserIdAndClassId(int $userId, int $classId = 0): Collection
    {
        $query = ClassSubjectTeacher::query()
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereNull('end_date')
            ->where('user_id', $userId);
        if ($classId != 0) {
            $query->where('class_id', $classId);
        }
        return $query->get();
    }

    public function checkUserExistTimetable($classSubjectTeacherIds, $timetableId, $classId)
    {
        return TeacherSubjectTimetable::query()
            ->whereIn('class_subject_teacher_id', $classSubjectTeacherIds)
            ->where('timetable_id', $timetableId)
            ->whereNot('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with('class')
            ->first();
    }

    public function checkUserExistTimetableOfClass($timetableId, $classId): bool
    {
        return TeacherSubjectTimetable::query()
            ->where('timetable_id', $timetableId)
            ->where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->exists();
    }

    public function updateTeacherSubjectTeacher($classSubjectTeacherId, $timetableId, $classId): void
    {
        TeacherSubjectTimetable::query()
            ->where('timetable_id', $timetableId)
            ->where('class_id', $classId)
            ->update([
                'class_subject_teacher_id' => $classSubjectTeacherId
            ]);
    }

    public function createTeacherSubjectTeacher(int $classSubjectTeacherId, int $timetableId, int $classId): void
    {
        TeacherSubjectTimetable::query()
            ->create(
                [
                    'class_subject_teacher_id' => $classSubjectTeacherId,
                    'timetable_id'             => $timetableId,
                    'class_id'                 => $classId,
                ]
            );
    }

    public function getSubject(): Collection
    {
        return Subject::query()
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function getSubjectTimeTableConfig(): Collection
    {
        return SubjectTimetableConfig::query()
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function transformSubjectTimetableConfig(
        Collection $subjects,
        Collection $subjectTimetableConfigs
    ): Collection {
        return $subjects->map(function ($subject) use ($subjectTimetableConfigs) {
            $subjectTimetableConfig = $subjectTimetableConfigs->where('subject_id', $subject->id)->first();
            return [
                'subjectTimetableConfigId' => $subjectTimetableConfig->id,
                'subject_id'               => $subject->id,
                'subject_name'             => $subject->name,
                'quantity'                 => $subjectTimetableConfig->quantity,
            ];
        });
    }
}
