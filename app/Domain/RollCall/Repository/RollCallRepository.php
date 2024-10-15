<?php 

namespace App\Domain\RollCall\Repository;

use App\Domain\RollCall\Models\RollCall;

class RollCallRepository{
    public function getAllRollCalls()
    {
        return RollCall::with(['student', 'class.subjectTeachers.teacher'])->get()->map(function ($rollCall) {
            return [
                'class_name' => $rollCall->class->name ?? 'N/A', // Tên lớp học
                'grade' => $rollCall->class->grade->name ?? 0, // Khối
                'date' => $rollCall->date,
                'status' => $rollCall->status,
                'note' => $rollCall->note,
                'teachers' => $rollCall->class->subjectTeachers->map(function ($subjectTeacher) {
                    return [
                        'teacher_id' => $subjectTeacher->teacher->id ?? 'N/A',
                        'fullname' => $subjectTeacher->teacher->fullname ?? 'N/A',
                        'email' => $subjectTeacher->teacher->email ?? 'N/A',
                    ];
                }),
            ];
        });
    }
}