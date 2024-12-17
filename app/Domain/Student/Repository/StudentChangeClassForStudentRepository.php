<?php

namespace App\Domain\Student\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusClassStudentEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\Type\TrueType;


class StudentChangeClassForStudentRepository {


    public function handle(string $keyword = '', int $school_year_id_choose = 0 , int $class_id = 0, int $school_year_id = 0, array $students_out = [], array $students_in = [])
    {
        $error = true;

        if (!empty($students_in)) {

            if ($class_id) {

                foreach ($students_in as $key => $item) {

                    $studentH = StudentClassHistory::where('student_id', $item)->where('status', StatusClassStudentEnum::NOT_YET_CLASS->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->where('end_date', null)->first();

                    if ($studentH) {

                        if ($school_year_id_choose == $school_year_id) {

                            $studentH->end_date = Carbon::now();
                            $check = $studentH->save();

                            if (!$check){
                                $error = true;
                            }

                            $studentHNew = new StudentClassHistory();

                            $studentHNew->student_id = $item;
                            $studentHNew->class_id = $class_id;
                            $studentHNew->start_date = Carbon::now();
                            $studentHNew->status = StatusClassStudentEnum::STUDYING->value;
                            $studentHNew->created_user_id = Auth::id();

                            $check = $studentHNew->save();

                            if (!$check){
                                $error = true;
                            }

                        } else {

                            $studentHNew = new StudentClassHistory();

                            $studentHNew->student_id = $item;
                            $studentHNew->class_id = $class_id;
                            $studentHNew->start_date = Carbon::now();
                            $studentHNew->status = StatusClassStudentEnum::STUDYING->value;
                            $studentHNew->created_user_id = Auth::id();

                            $check = $studentHNew->save();

                            if (!$check){
                                $error = true;
                            }


                        }

                    } else {

                        if ($school_year_id_choose == $school_year_id) {

                            $studentHNew = new StudentClassHistory();

                            $studentHNew->student_id = $item;
                            $studentHNew->class_id = $class_id;
                            $studentHNew->start_date = Carbon::now();
                            $studentHNew->status = StatusClassStudentEnum::STUDYING->value;
                            $studentHNew->created_user_id = Auth::id();

                            $check = $studentHNew->save();

                            if (!$check){
                                $error = true;
                            }

                        } else {

                            $studentHNew = new StudentClassHistory();

                            $studentHNew->student_id = $item;
                            $studentHNew->class_id = $class_id;
                            $studentHNew->start_date = Carbon::now();
                            $studentHNew->status = StatusClassStudentEnum::STUDYING->value;
                            $studentHNew->created_user_id = Auth::id();

                            $check = $studentHNew->save();

                            if (!$check){
                                $error = true;
                            }


                        }

                    }

                }

            } else {

                foreach ($students_in as $key => $item) {

                    $studentH = StudentClassHistory::where('student_id', $item)->where('status', StatusClassStudentEnum::NOT_YET_CLASS->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->where('end_date', null)->first();

                    if ($studentH) {

                        $studentH->end_date = Carbon::now();
                        $check = $studentH->save();

                        if (!$check){
                            $error = true;
                        }

                        $studentHNew = new StudentClassHistory();

                        $studentHNew->student_id = $item;
                        $studentHNew->class_id = $class_id;
                        $studentHNew->start_date = Carbon::now();
                        $studentHNew->status = StatusClassStudentEnum::STUDYING->value;
                        $studentHNew->created_user_id = Auth::id();

                        $check = $studentHNew->save();

                        if (!$check){
                            $error = true;
                        }

                    } else {

                        $studentHNew = new StudentClassHistory();

                        $studentHNew->student_id = $item;
                        $studentHNew->class_id = $class_id;
                        $studentHNew->start_date = Carbon::now();
                        $studentHNew->status = StatusClassStudentEnum::STUDYING->value;
                        $studentHNew->created_user_id = Auth::id();

                        $check = $studentHNew->save();

                        if (!$check){
                            $error = true;
                        }

                    }

                }

            }

        }



        if (!empty($students_out)) {

            foreach ($students_out as $key => $item) {


                $studentH = StudentClassHistory::where('student_id', $item)->where('class_id', $class_id)->where('status', StatusClassStudentEnum::STUDYING->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->where('end_date', null)->first();

                if ($studentH) {

                    $studentH->end_date = Carbon::now();
                    $check = $studentH->save();

                    if (!$check){
                        $error = true;
                    }

                    $studentHNew = new StudentClassHistory();

                    $studentHNew->student_id = $item;
                    $studentHNew->start_date = Carbon::now();
                    $studentHNew->status = StatusClassStudentEnum::NOT_YET_CLASS->value;
                    $studentHNew->created_user_id = Auth::id();

                    $check = $studentHNew->save();

                    if (!$check){
                        $error = true;
                    }

                } else {

                    $studentHNew = new StudentClassHistory();

                    $studentHNew->student_id = $item;
                    $studentHNew->start_date = Carbon::now();
                    $studentHNew->status = StatusClassStudentEnum::NOT_YET_CLASS->value;
                    $studentHNew->created_user_id = Auth::id();

                    $check = $studentHNew->save();

                    if (!$check){
                        $error = true;
                    }

                }

            }

        }

        return $error;

    }



}
