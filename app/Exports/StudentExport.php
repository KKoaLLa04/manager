<?php

namespace App\Exports;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentExport implements FromCollection, WithMapping,WithHeadings
{
    public $toDate;
    public $fromDate;

    public function __construct($fromData, $toDate)
    {
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $students = Student::query()->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereBetween('created_at', [$this->fromDate, $this->toDate])
            ->with([
                'parents' => function ($query) {
                    $query->select('users.id', 'fullname', 'phone', 'code', 'status')
                        ->where('users.access_type', AccessTypeEnum::GUARDIAN->value)
                        ->where('users.is_deleted', DeleteEnum::NOT_DELETE->value);
                }
            ])
            ->get();
        $classes = ClassModel::all()->keyBy('id'); // Lấy thông tin tất cả lớp

        return $students->transform(function ($student) use ($classes) {
            $parent = $student->parents->first();

            $currentClassHistory = StudentClassHistory::where('student_id', $student->id)
                ->whereNull('end_date') // Lấy lớp chưa kết thúc (end_date là null)
                ->orderBy('start_date', 'desc') // Lấy lớp gần nhất theo ngày bắt đầu
                ->orderBy('id', 'desc')
                ->first();

            if ($currentClassHistory) {
                $className = $classes->get($currentClassHistory->class_id)->name ?? "";
            } else {
                $className = "";
            }

            return [
                'student_code' => $student->student_code,
                'fullname'     => $student->fullname,
                'gender'       => $student->gender,
                'class_name'   => $className,
                'parent'       => $parent->fullname,
            ];
        });

    }

    public function map($row): array
    {
        return [
            $row->student_code,
            $row->fullname,
            $row->gender,
            $row->class_name,
            $row->parent,
        ];
    }

    public function headings(): array
    {
        return [
            'Mã',
            'Tên',
            'GIới tính',
            'Lớp',
            'Phụ huynh',
        ];
    }

}
