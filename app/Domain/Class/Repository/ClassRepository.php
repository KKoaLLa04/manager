<?php

namespace App\Domain\Class\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\PaginateEnum;
use App\Common\Enums\StatusClassStudentEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ClassRepository
{

    /**
     * @param  int  $schoolYearId
     * @return bool
     */
    public function checkSchoolYearId(int $schoolYearId): bool
    {
        return SchoolYear::where('id', $schoolYearId)->where('status', StatusEnum::ACTIVE->value)->exists();
    }

    public function getClasses(Request $request): array
    {
        $schoolYearId = $request->school_year_id;
        $page         = isset($request->page) ? $request->page : PaginateEnum::PAGE->value;
        $size         = isset($request->size) ? $request->size : PaginateEnum::MAX_SIZE->value;
        $search       = isset($request->search) ? $request->search : '';

        $query = Classes::query()->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('school_year_id', $schoolYearId)
            ->where('name', 'like', '%'.$search.'%');

        $totalPage = ceil($query->count() / $size);

        $classes = $query->offset(($page - 1) * $size)
            ->with(
                [
                    'schoolYear',
                    'grade',
                    'academicYear',
                    'user'
                ]
            )
            ->limit($size)
            ->get();

        return [$totalPage, $page, $size, $classes];
    }

    public function transform(mixed $page, mixed $totalPage, mixed $size, Collection $classes): array
    {
        return [
            "page"      => $page,
            "totalPage" => $totalPage,
            "size"      => $size,
            "classes"   => $this->transformClass($classes)
        ];
    }

    public function transformClass(Collection $classes): array
    {

        return $classes->map(function ($class){
            return  [
                "id"            => $class->id,
                "name"          => is_null($class->name) ? "" : $class->name,
                "schoolYear"    => is_null($class->schoolYear->name) ? "" : $class->schoolYear->name,
                "grade"         => is_null($class->grade->name) ? "" : $class->grade->name,
                "academic_name" => is_null($class->academicYear->name) ? "" : $class->academicYear->name,
                "academic_code" => is_null($class->academicYear->code) ? "" : $class->academicYear->code,
                "teacher_name"  => is_null($class->user->first()->name) ? "" : $class->user->first()->name,
                "teacher_email" => is_null($class->user->first()->fullname) ? "" : $class->user->first()->fullname,
                "status"        => is_null($class->status) ? "1" : $class->status,
            ];
        })->toArray();
    }

    public function getGrades(): Collection
    {
        return Grade::query()->get();
    }

    public function getAcademicYear(): Collection
    {
        return AcademicYear::query()->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();
    }

    public function getTeachers(): Collection
    {
        $classSubjectTeacherMain = ClassSubjectTeacher::query()
            ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->pluck('user_id')->unique()->toArray();
        return User::query()->where('access_type', AccessTypeEnum::TEACHER->value)
            ->whereNotIn('id', $classSubjectTeacherMain)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->get();

    }

    public function transformDataCreate(Collection $grades, Collection $academicYear, Collection $schoolYear, Collection $teachers): array
    {
        return [
            'grades' => $this->toArray($grades),
            'academics' => $this->toArray($academicYear),
            'schoolYears' => $this->toArray($schoolYear),
            'teachers' => $this->dataTeaches($teachers),
        ];
    }
    private function dataTeaches(Collection $teachers): array
    {
        $dataReturn = [];
        foreach ($teachers as $item) {
            $dataReturn[] = [
                'id'            => $item->id,
                'name'          => is_null($item->fullname) ? "": $item->fullname,
            ];
        }
        return $dataReturn;
    }

    private function toArray(Collection $items): array
    {
        $dataReturn = [];
        foreach ($items as $item) {
            $dataReturn[] = [
                'id'            => $item->id,
                'name'          => is_null($item->fullname) ? "": $item->fullname,
            ];
        }
        return $dataReturn;
    }

    public function transformDataAssign(Collection $teachers): array
    {
        return $teachers->map(function ($item) {
            return [
                'id'            => $item->id,
                'name'          => is_null($item->fullname) ? '' : $item->fullname,
                'email'         => $item->email,
                'gender'        => is_null($item->gender) ? "1" : $item->gender,
                'dob'           => is_null($item->dob) ? "" : $item->dob,
                'phone'          => is_null($item->phone) ? "" : $item->phone,
            ];
        })->toArray();
    }

    public function detailClass(int $class_id): ?Classes
    {
        return Classes::where('id', $class_id)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();
    }

    public function getStudentOfClass(int $class_id): Collection
    {
        $date = now();
        $studentIds =  StudentClassHistory::query()->where('class_id', $class_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusClassStudentEnum::STUDYING->value)
            ->where(function ($query)use ($date) {
                $query->where('start_date', '<=', $date)
                    ->whereNull('end_date');
                $query->orWhere(function ($query)use ($date) {
                    $query->where('end_date', '>=', $date)->where('start_date', '<=', $date);
                });
            })
            ->pluck('student_id')->toArray();
        return Student::query()->whereIn('id', $studentIds)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();
    }
}
