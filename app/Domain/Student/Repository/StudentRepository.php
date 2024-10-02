<?php
namespace App\Domain\Student\Repository;

use App\Common\Enums\DeleteEnum;
use App\Models\Student;

class StudentRepository {


    public function handle () {

        $students = Student::select('id', 'fullname','address','student_code','dob','status','gender','created_user_id','modified_user_id','created_at','updated_at',)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();

        if($students->count() > 0){
            return $students;
        }

        return [];

    }


}