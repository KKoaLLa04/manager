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
use App\Domain\Subject\Models\Subject;
use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ClassRepository
{

    /**
     * @param int $schoolYearId
     * @return bool
     */
    public function checkSchoolYearId(int $schoolYearId): bool
    {
        return SchoolYear::where('id', $schoolYearId)->where('status', StatusEnum::ACTIVE->value)->exists();
    }

    public function getClasses(Request $request): array
    {
        $schoolYearId = $request->school_year_id;
        $page = isset($request->page) ? $request->page : PaginateEnum::PAGE->value;
        $size = isset($request->size) ? $request->size : PaginateEnum::MAX_SIZE->value;
        $search = isset($request->search) ? $request->search : '';

        $query = Classes::query()->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('school_year_id', $schoolYearId)
            ->where('name', 'like', '%' . $search . '%');
        $totalItems = $query->count();
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

        return [$totalPage, $page, $size,$totalItems, $classes ];
    }

    public function transform(mixed $page, mixed $totalPage, mixed $pageSize, int $totalItems, Collection $classes): array
    {
        return [
            "page" => $page,
            "totalPage" => $totalPage,
            "pageSize" => $pageSize,
            "totalItems" => $totalItems,
            "classes" => $this->transformClass($classes)
        ];
    }

    public function transformClass(Collection $classes): array
    {
        return $classes->map(function ($class) {
            return [
                "id" => $class->id,
                "name" => is_null($class->name) ? "" : $class->name,
                "schoolYear" => is_null($class->schoolYear->name) ? "" : $class->schoolYear->name,
                "grade" => is_null($class->grade->name) ? "" : $class->grade->name,
                "academic_name" => is_null($class->academicYear->name) ? "" : $class->academicYear->name,
                "academic_code" => is_null($class->academicYear->code) ? "" : $class->academicYear->code,
                "teacher_name" => is_null($class->user->first()) ? "" : (is_null($class->user->first()->fullname) ? "" : $class->user->first()->fullname),
                "teacher_email" => is_null($class->user->first()) ? "" :(is_null($class->user->first()->email) ? "" : $class->user->first()->email),
                "status" => is_null($class->status) ? "1" : $class->status,
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

    public function getTeachersPaginate(Request $request): array
    {
        $page = isset($request->page) ? $request->page : PaginateEnum::PAGE->value;
        $size = isset($request->size) ? $request->size : PaginateEnum::MAX_SIZE->value;
        $search = isset($request->search) ? $request->search : '';

        $classSubjectTeacherMain = ClassSubjectTeacher::query()
            ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->pluck('user_id')->unique()->toArray();
        $query = User::query()->where('access_type', AccessTypeEnum::TEACHER->value)
            ->whereNotIn('id', $classSubjectTeacherMain)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('fullname', 'like', '%' . $search . '%');
        $totalItems = $query->count();
        $totalPage = ceil($query->count() / $size);
        $teachers = $query->offset(($page - 1) * $size)
            ->limit($size)
            ->get();
        return [$totalPage, $page, $size,$totalItems, $teachers];
    }

    public function transformDataCreate(
        Collection $grades,
        Collection $academicYear,
        Collection $schoolYear,
        Collection $teachers
    ): array
    {
        return [
            'grades' => $this->toArrayGrades($grades),
            'academics' => $this->toArrayAcademic($academicYear),
            'schoolYears' => $this->toArraySchoolYear($schoolYear),
            'teachers' => $this->dataTeaches($teachers),
        ];
    }

    private function dataTeaches(Collection $teachers): array
    {
        $dataReturn = [];
        foreach ($teachers as $item) {
            $dataReturn[] = [
                'id' => $item->id,
                'name' => is_null($item->fullname) ? "" : $item->fullname,
            ];
        }
        return $dataReturn;
    }

    private function toArray(Collection $items): array
    {
        $dataReturn = [];
        foreach ($items as $item) {
            $dataReturn[] = [
                'id' => $item->id,
                'name' => is_null($item->fullname) ? "" : $item->fullname,
            ];
        }
        return $dataReturn;
    }

    public function transformDataAssign(int $totalPage,int $page,int $pageSize,int $totalItems,Collection $teachers): array
    {
        return [
            "teachers" => $teachers->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => is_null($item->code) ? "" : $item->code,
                    'name' => is_null($item->fullname) ? '' : $item->fullname,
                    'email' => $item->email,
                    'gender' => is_null($item->gender) ? "1" : $item->gender,
                    'dob' => is_null($item->dob) ? now()->timestamp : Carbon::parse($item->dob)->timestamp,
                    'phone' => is_null($item->phone) ? "" : $item->phone,
                ];
            })->toArray(),
            "totalPage" => $totalPage,
            "page" => $page,
            "pageSize" => $pageSize,
            "totalItems" => $totalItems,

        ];
    }

    public function detailClass(int $class_id): ?Classes
    {
        return Classes::where('id', $class_id)->where('is_deleted',
            DeleteEnum::NOT_DELETE->value)->with(
            [
                'schoolYear',
                'grade',
                'academicYear',
                'user'
            ]
        )->first();
    }

    public function getStudentOfClass(int $class_id): Collection
    {
        $date = now()->format('Y-m-d');
        $studentIds = StudentClassHistory::query()->where('class_id', $class_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusClassStudentEnum::STUDYING->value)
            ->where(function ($query) use ($date) {
                $query->where('start_date', '<=', $date)
                    ->whereNull('end_date');
                $query->orWhere(function ($query) use ($date) {
                    $query->where('end_date', '>=', $date)->where('start_date', '<=', $date);
                });
            })
            ->pluck('student_id')->toArray();
        return Student::query()->whereIn('id', $studentIds)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();
    }

    public function getSubjectOfClass(int $class_id): Collection
    {
        return ClassSubject::query()->where('class_id', $class_id)
            ->where('status', StatusEnum::ACTIVE->value)
            ->with('subject')
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();
    }

    public function getClassSubjectTeacher(array $subjectClassIds): Collection
    {
        return ClassSubjectTeacher::query()
            ->whereIn('class_subject_id', $subjectClassIds)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->with('user')
            ->get()
            ->groupBy('class_subject_id');
    }

    public function transformDetailClass(
        Classes    $class,
        Collection $students,
        Collection $classSubjects,
        Collection $subjectTeachers
    ): array
    {
        return [
            "id" => $class->id,
            "name" => is_null($class->name) ? "" : $class->name,
            "schoolYear" => is_null($class->schoolYear->name) ? "" : $class->schoolYear->name,
            "grade" => is_null($class->grade->name) ? "" : $class->grade->name,
            "academic" => is_null($class->academicYear->name) ? "" : $class->academicYear->name,
            "teacherName" => is_null($class->user->first()->fullname) ? "" : $class->user->first()->fullname,
            "students" => $students->map(function ($item) {
                return [
                    "code" => !is_null($item->student_code) ? $item->student_code : "",
                    "name" => is_null($item->fullname) ? "" : $item->fullname,
                    "dob" => is_null($item->dob) ? now()->timestamp : Carbon::parse($item->dob)->timestamp,
                ];
            })->toArray(),
            "classSubject" => $classSubjects->map(function ($item) use ($subjectTeachers) {
                return [
                    "id" => $item->id,
                    "subjectId" => !isset($item->subject->id) ? 0 : $item->subject->id,
                    "subjectName" => !isset($item->subject->name) ? "" : $item->subject->name,
                    "teacher" => $this->transformSubjectTeacher($subjectTeachers, $item->id),
                ];
            })
        ];
    }

    private function transformSubjectTeacher(Collection $subjectTeachers, int $classSubjectId): array
    {
        if ($subjectTeachers->has($classSubjectId)) {
            $subjectTeacher = collect($subjectTeachers->get($classSubjectId))->first();
            return [
                "id" => $subjectTeacher->user->first()->id,
                "name" => is_null($subjectTeacher->user->first()->fullname) ? "" : $subjectTeacher->user->first()->fullname,
            ];
        }
        return [];
    }

    public function transformTeacher(Collection $teachers): array
    {
        return $teachers->map(function ($item) {
            return [
                "id" => $item->id,
                "name" => is_null($item->fullname) ? "" : $item->fullname,
            ];
        })->toArray();
    }

    public function changeStatusClassSubjectTeacher(int $class_id, int $class_subject_id): void
    {
        ClassSubjectTeacher::query()
            ->where('class_id', $class_id)
            ->where('class_subject_id', $class_subject_id)
            ->where('access_type', StatusTeacherEnum::TEACHER->value)
            ->update([
                'status' => StatusEnum::UN_ACTIVE->value,
                'end_date' => now(),
                'modified_user_id' => Auth::id(),
            ]);
    }

    public function checkClassSubjectTeacher(int $classId, int $teacherId, int $classSubjectId): bool
    {
        return ClassSubjectTeacher::query()
            ->where('class_id', $classId)
            ->where('class_subject_id', $classSubjectId)
            ->where('access_type', StatusTeacherEnum::TEACHER->value)
            ->where('user_id', $teacherId)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->exists();
    }

    public function updateClassSubjectTeacher(int $classId, int $teacherId, int $classSubjectId)
    {
        ClassSubjectTeacher::query()
            ->create(
                [
                    "class_id" => $classId,
                    "class_subject_id" => $classSubjectId,
                    "user_id" => $teacherId,
                    "access_type" => StatusTeacherEnum::TEACHER->value,
                    "is_deleted" => DeleteEnum::NOT_DELETE->value,
                    "status" => StatusEnum::ACTIVE->value,
                    "start_date" => now(),
                    "created_user_id" => Auth::id(),
                ]
            );
    }

    public function getSubjectNotOfClass(array $subjectIdsOfClass): Collection
    {
        return Subject::query()->whereNotIn('id', $subjectIdsOfClass)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function transformCreateSubjectForClass(Collection $teachers, Collection $subjects): array
    {
        return [
            "teachers" => $teachers->map(function ($item) {
                return [
                    "id" => $item->id,
                    "fullname" => is_null($item->fullname) ? "" : $item->fullname,
                ];
            })->toArray(),
            "subjects" => $subjects->map(function ($item) {
                return [
                    "id" => $item->id,
                    "fullname" => is_null($item->name) ? "" : $item->name,
                ];
            })->toArray(),
        ];
    }

    public function createSubjectForClass(int $classId, int $subjectId): ClassSubject
    {
        return ClassSubject::create(
            [
                "class_id" => $classId,
                "subject_id" => $subjectId,
                "status" => StatusEnum::ACTIVE->value,
                "is_deleted" => DeleteEnum::NOT_DELETE->value,
                "created_user_id" => Auth::id(),
            ]
        );
    }

    public function createSubjectTeacherForClass(int $subjectClassId, int $teacherId, int $classId): void
    {
        ClassSubjectTeacher::create(
            [
                "class_id" => $classId,
                "class_subject_id" => $subjectClassId,
                "user_id" => $teacherId,
                "start_date" => now(),
                "access_type" => StatusTeacherEnum::TEACHER->value,
                "status" => StatusEnum::ACTIVE->value,
                "is_deleted" => DeleteEnum::NOT_DELETE->value,
                "created_user_id" => Auth::id(),
            ]
        );
    }

    public function deleteSubjectForClass(int $classId, int $classSubjectId): bool
    {
        $deletedClassSubject = $this->deleteClassSubject($classSubjectId);
        if ($deletedClassSubject) {
            return ClassSubjectTeacher::where('class_id', $classId)
                ->where('class_subject_id', $classSubjectId)
                ->update([
                    'end_date' => now(),
                    'status' => StatusEnum::UN_ACTIVE->value,
                    'is_deleted' => DeleteEnum::DELETED->value,
                    'modified_user_id' => Auth::id(),
                ]);
        }
        return false;
    }

    private function deleteClassSubject(int $classSubjectId): bool
    {
        return ClassSubject::where('id', $classSubjectId)->update(
            [
                'is_deleted' => DeleteEnum::DELETED->value,
                'status' => StatusEnum::UN_ACTIVE->value,
                'modified_user_id' => Auth::id(),
            ]
        );
    }

    private function toArrayGrades(Collection $grades)
    {
        return $grades->map(function ($item){
            return [
                'id' => $item->id,
                'name' => is_null($item->name) ? "" : $item->name,
            ];
        })->toArray();


    }

    private function toArrayAcademic(Collection $grades)
    {
        return $grades->map(function ($item){
            return [
                'id' => $item->id,
                'name' => is_null($item->name) ? "" : $item->name,
            ];
        })->toArray();


    }

    private function toArraySchoolYear(Collection $grades)
    {
        return $grades->map(function ($item){
            return [
                'id' => $item->id,
                'name' => is_null($item->name) ? "" : $item->name,
            ];
        })->toArray();


    }
}
