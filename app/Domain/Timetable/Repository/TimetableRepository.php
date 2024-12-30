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
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimetableRepository
{
    public function getTimetable(): Collection
    {
        return Timetable::query()
            ->get();
    }

    public function getTeacherSubjectTimetable(array $timetableIds, $classId, $categoryTimetableId): Collection
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
            ->whereNotNull('class_subject_id')
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
        Collection $classSubjectTeacherByClassId,
    ) {
        $dataTimetable = $timetables->map(function ($timetable) use ($teacherSubjectTimetables) {
            $teacherSubjectTimetable   = $teacherSubjectTimetables->where('timetable_id', $timetable->id)->first();
            $teacherSubjectTimetableId = !is_null($teacherSubjectTimetable) ? $teacherSubjectTimetable->id : 0;
            $class_subject_teacher_id  = "";
            $user_id                   = "";
            $user_name                 = "";
            $subject_id                = "";
            $subject_name              = "";
            if (!is_null($teacherSubjectTimetable)) {
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
                'user_id'                  => is_null($teacherSubjectTeacher->user) ? 0 : $teacherSubjectTeacher->user_id,
                'user_name'                => is_null($teacherSubjectTeacher->user) ? "" : $teacherSubjectTeacher->user->fullname,
                'subject_id'               => is_null($teacherSubjectTeacher->subject) ? 0 : $teacherSubjectTeacher->subject->id,
                'subject_name'             => is_null($teacherSubjectTeacher->subject) ? "" : $teacherSubjectTeacher->subject->name,
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
            ->whereNotNull('class_subject_id')
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
            ->whereNotNull('class_subject_id')
            ->where('user_id', $userId);
        if ($classId != 0) {
            $query->where('class_id', $classId);
        }
        return $query->get();
    }

    public function checkUserExistTimetable($userId, $subjectId, $categoryId, $timetableId, $classId)
    {
        return TeacherSubjectTimetable::query()
            ->where('user_id', $userId)
            ->where('subject_id', $subjectId)
            ->where('category_attendance_id', $categoryId)
            ->where('timetable_id', $timetableId)
            ->whereNot('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with('class')
            ->first();
    }

    public function countUserTimetable($subjectId, $categoryId, int $classId): int
    {
        return TeacherSubjectTimetable::query()
            ->where('subject_id', $subjectId)
            ->where('category_attendance_id', $categoryId)
            ->where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->count();
    }

    public function checkUserExistTimetableOfClass($timetableId, $classId, $categoryId): bool
    {
        return TeacherSubjectTimetable::query()
            ->where('category_attendance_id', $categoryId)
            ->where('timetable_id', $categoryId)
            ->where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->exists();
    }

    public function updateTeacherSubjectTeacher(
        $classSubjectTeacherId,
        $timetableId,
        $classId,
        $userId,
        $subjectId,
        $categoryId
    ): void
    {
        TeacherSubjectTimetable::query()
            ->where('timetable_id', $timetableId)
            ->where('category_attendance_id', $categoryId)
            ->where('class_id', $classId)
            ->update([
                'class_subject_teacher_id' => $classSubjectTeacherId,
                'subject_id'               => $subjectId,
                'user_id'                  => $userId,
                'timetable_id'             => $timetableId,
            ]);
    }

    public function createTeacherSubjectTeacher(
        int $classSubjectTeacherId,
        int $timetableId,
        int $classId,
            $userId,
            $subjectId,
            $categoryId
    ): void {
        TeacherSubjectTimetable::query()
            ->create(
                [
                    'class_subject_teacher_id' => $classSubjectTeacherId,
                    'category_attendance_id'   => $categoryId,
                    'subject_id'               => $subjectId,
                    'user_id'                  => $userId,
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

    public function subjectConfig(int $subjectId)
    {
        return SubjectTimetableConfig::query()
            ->where('subject_id', $subjectId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->first();
    }
}
