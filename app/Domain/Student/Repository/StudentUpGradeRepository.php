<?php

namespace App\Domain\Student\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SebastianBergmann\Type\TrueType;

class StudentUpGradeRepository {


    public function handle(int $user_id, array $students, Request $request)
    {

        try {

            foreach ($students as $value) {

                $exixts = StudentClassHistory::where('student_id', $value)->where('end_date', null)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

                if ($exixts) {

                    $exixts->end_date = Carbon::now();
                    $exixts->status = StatusEnum::UN_ACTIVE->value;
                    $exixts->is_deleted = DeleteEnum::DELETED->value;
                    $exixts->modified_user_id = $user_id;

                    $exixts->save();

                }

                $item = new StudentClassHistory();

                $item->class_id = $request->class_id;
                $item->student_id = $value;
                $item->start_date = Carbon::now();
                $item->status = StatusEnum::ACTIVE->value;
                $item->is_deleted = DeleteEnum::NOT_DELETE->value;
                $item->created_user_id = $user_id;

                $item->save();

            }

            return true;

        } catch (\Throwable $th) {
            return false;
        }


    }



}
