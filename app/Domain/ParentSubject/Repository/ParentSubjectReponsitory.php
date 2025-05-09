<?php

namespace App\Domain\ParentSubject\Repository;

use App\Models\ClassSubject;
use App\Models\StudentClassHistory;
use App\Models\UserStudent;
use App\Common\Enums\DeleteEnum;
use Illuminate\Support\Facades\Auth;

class ParentSubjectReponsitory
{
    public function getSubjectsByParent($student_id = null)
    {
        $user_id = Auth::user()->id;

        $userStudent = UserStudent::where('user_id', $user_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();

        if ($userStudent->isEmpty()) {
            return [];
        }

        if (!$student_id) {
            $student_id = $userStudent->first()->student_id;
        }

        $isValidStudent = $userStudent->contains('student_id', $student_id);

        if (!$isValidStudent) {
            return [];
        }

        // Lấy lớp hiện tại của con
        $studentClassHistory = StudentClassHistory::where('student_id', $student_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereNull('end_date')
            ->first();

        if (!$studentClassHistory) {
            return [];
        }

        $class_id = $studentClassHistory->class_id;

        // Lấy danh sách môn học của lớp
        $classSubjects = ClassSubject::with('subject')
            ->where('class_id', $class_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();

        // Trả về danh sách môn học
        return $classSubjects->map(function ($classSubject) {
            return [
                'subject_id' => $classSubject->subject->id,
                'subject_name' => $classSubject->subject->name,
            ];
        });
    }
}

