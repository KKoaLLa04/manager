<?php
namespace App\Domain\User\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\MainTearchEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;

class ChooseClassToMainTearchRepository {


    public function handle ($school_year_id) {

        $classes = Classes::where('school_year_id', $school_year_id)->get();

        $arrIdClassHasMainTearch = [];

        foreach ($classes as $cls) {

            $checkClassHasMainTearch = ClassSubjectTeacher::where('class_id', $cls->id)->where('end_date', null)->where('status', StatusEnum::ACTIVE->value)->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

            if($checkClassHasMainTearch){
                $arrIdClassHasMainTearch[] = $cls->id;
            }

        }

        $classeNoHasMainTearch = Classes::whereNotIn('id', $arrIdClassHasMainTearch)->get();

        if($classeNoHasMainTearch->toArray()){
            return $classeNoHasMainTearch->map(function ($item) {
                return [
                    "className" => $item->name,
                    "classId" => $item->id,
                ];
            });
        }

        return [];

    }
    public function assignTeacherToSubject(array $data)
    {
        // Kiểm tra xem giáo viên đã được gán cho môn học trong lớp này chưa
        $exists = ClassSubjectTeacher::where([
            'class_subject_id' => $data['class_subject_id'],
            'class_id'         => $data['class_id'],
            'user_id'          => $data['user_id'],
            'is_deleted'       => DeleteEnum::NOT_DELETE->value,
        ])->exists();

        if ($exists) {
            return false; // Đã tồn tại bản ghi, giáo viên đã được gán
        }

        // Kiểm tra xem giáo viên đã được gán vào môn học khác chưa
        $teacherAssignedToOtherSubject = ClassSubjectTeacher::where([
            'user_id'    => $data['user_id'],
            'is_deleted' => DeleteEnum::NOT_DELETE->value,
        ])->exists();

        if ($teacherAssignedToOtherSubject) {
            return false; // Nếu giáo viên đã dạy môn học khác, không thể gán vào môn này
        }

        // Tạo bản ghi mới trong bảng class_subject_teacher
        return ClassSubjectTeacher::create([
            'class_subject_id' => $data['class_subject_id'],
            'class_id'         => $data['class_id'],
            'user_id'          => $data['user_id'],
            'start_date'       => $data['start_date'],
            'end_date'         => $data['end_date'],
            'status'           => $data['status'] ?? StatusEnum::ACTIVE->value,
            'access_type'      => $data['access_type'],
            'is_deleted'       => DeleteEnum::NOT_DELETE->value,
            'created_user_id'  => $data['created_user_id'],
        ]);
    }

}
