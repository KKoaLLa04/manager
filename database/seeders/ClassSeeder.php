<?php

namespace Database\Seeders;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gradeId = Grade::query()->first()->id;
        $schoolYearId = SchoolYear::query()->first()->id;
        $academicYearId = AcademicYear::query()->first()->id;
        $user = User::query()->create(
            [
                "fullname" => "Teacher",
                "phone" => "0388623534123",
                "email" => Str::random(10) . "@gmail.com",
                "username" => Str::random(10),
                "password" => "12345678",
                "access_type" => AccessTypeEnum::TEACHER->value,
            ]
        );
        $userId = $user->id;
        $classId = Classes::query()->create(
            [
                'grade_id' => $gradeId,
                'school_year_id' => $schoolYearId,
                'academic_year_id' => $academicYearId,
                'name' => "Lớp 6",
                'code' => 'LOP-6',
                'status' => StatusEnum::ACTIVE->value,
                'is_deleted' => DeleteEnum::NOT_DELETE->value,

            ]
        )->id;

        ClassSubjectTeacher::query()->create(
            [
                'class_id' => $classId,
                'user_id' => $userId,
                'start_date' => now(),
                'status' => StatusEnum::ACTIVE->value,
                'access_type' => StatusTeacherEnum::MAIN_TEACHER->value,
                'is_deleted' => DeleteEnum::NOT_DELETE->value,
            ]
        );
    }
}
