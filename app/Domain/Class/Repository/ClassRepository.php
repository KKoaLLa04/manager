<?php

namespace App\Domain\Class\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\PaginateEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Models\Classes;
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
        $dataClasses = [];
        foreach ($classes as $class) {
            $dataClasses[] = [
                "id"            => $class->id,
                "name"          => $class->name,
                "schoolYear"    => $class->schoolYear->name,
                "grade"         => $class->grade->name,
                "academic_name" => $class->academicYear->name,
                "academic_code" => $class->academicYear->code,
                "teacher_name"  => $class->user->first()->fullname,
                "teacher_email" => $class->user->first()->fullname,
                "status"        => $class->status,
            ];
        }
        return $dataClasses;
    }
}
