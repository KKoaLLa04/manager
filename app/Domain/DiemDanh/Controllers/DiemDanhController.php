<?php
namespace App\Domain\DiemDanh\Controllers;

use App\Common\Enums\StatusBuoi;
use App\Common\Enums\StatusThu;
use App\Common\Enums\StatusTiet;
use App\Domain\Class\Repository\ClassRepository;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\Common\Repository\GetUserRepository;
use App\Models\DiemDanh;
use Illuminate\Support\Facades\Auth;






class DiemDanhController extends BaseController
{


    private $user;


    public function __construct(
        Request $request,
        protected ClassRepository $classRepository,
    )
    {
        $this->user = new GetUserRepository();
        parent::__construct($request);
    }



    public function danhsach(Request $request)
    {

        $user_id = Auth::user()->id;

        if (!$this->user->getManager($user_id)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

//        $classSubjects = $this->classRepository->getSubjectOfClass($request->classId);
//        $classSubjectIds = $classSubjects->pluck('id')->toArray();
//        $classSubjectTeachers = $this->classRepository->getClassSubjectTeacher($classSubjectIds);
//        $subjects = collect();
//
//        foreach ($classSubjects as $classSubject) {
//            $classSubjectId = $classSubject->id;
//            $classSubjectTeacher = $classSubjectTeachers->get($classSubjectId);
//            if (!empty($classSubjectTeacher)) {
//                $subjects->push($classSubject->subject);
//            }
//        }
//        $subjects = $subjects->unique('id');

        $request->validate([
            'classId' => 'required'
        ]);
        $class_id = $request->classId;

        $sangThu2 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::SANG->value)->where('thu', StatusThu::THU2->value)->where('class_id', $class_id)->get();
        $sangThu3 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::SANG->value)->where('thu', StatusThu::THU3->value)->where('class_id', $class_id)->get();;
        $sangThu4 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::SANG->value)->where('thu', StatusThu::THU4->value)->where('class_id', $class_id)->get();;
        $sangThu5 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::SANG->value)->where('thu', StatusThu::THU5->value)->where('class_id', $class_id)->get();;
        $sangThu6 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::SANG->value)->where('thu', StatusThu::THU6->value)->where('class_id', $class_id)->get();;
        $sangThu7 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::SANG->value)->where('thu', StatusThu::THU7->value)->where('class_id', $class_id)->get();;
        $sangChuNhat = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::SANG->value)->where('thu', StatusThu::CHUNHAT->value)->where('class_id', $class_id)->get();;

        $chieuThu2 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::CHIEU->value)->where('thu', StatusThu::THU2->value)->where('class_id', $class_id)->get();;
        $chieuThu3 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::CHIEU->value)->where('thu', StatusThu::THU3->value)->where('class_id', $class_id)->get();;
        $chieuThu4 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::CHIEU->value)->where('thu', StatusThu::THU4->value)->where('class_id', $class_id)->get();;
        $chieuThu5 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::CHIEU->value)->where('thu', StatusThu::THU5->value)->where('class_id', $class_id)->get();;
        $chieuThu6 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::CHIEU->value)->where('thu', StatusThu::THU6->value)->where('class_id', $class_id)->get();;
        $chieuThu7 = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::CHIEU->value)->where('thu', StatusThu::THU7->value)->where('class_id', $class_id)->get();;
        $chieuChuNhat = DiemDanh::with(['classSubjectTeacher','classSubjectTeacher.subject'])->where('buoi', StatusBuoi::CHIEU->value)->where('thu', StatusThu::CHUNHAT->value)->where('class_id', $class_id)->get();;

        return response()->json(
            [
                'msg' => 'Lấy dánh sách thành công',
                'data' => [
                    "sang" => [
                        "thu2" => $sangThu2->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu3" => $sangThu3->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu4" => $sangThu4->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu5" => $sangThu5->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu6" => $sangThu6->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu7" => $sangThu7->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "chunhat" => $sangChuNhat->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                    ],
                    "chieu" => [
                        "thu2" => $chieuThu2->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu3" => $chieuThu3->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu4" => $chieuThu4->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu5" => $chieuThu5->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu6" => $chieuThu6->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "thu7" => $chieuThu7->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                        "chunhat" => $chieuChuNhat->map(function ($item) {
                            return [
                                "id" => $item->classSubjectTeacher->id ?? null,
                                "tiet" => $item->tiet,
                                "mon" => $item->classSubjectTeacher->subject->name ?? null,
                            ];
                        }),
                    ]
                ]
            ],
            ResponseAlias::HTTP_OK
        );
    }






    public function add_edit(Request $request)
    {

        $user_id = Auth::user()->id;

        if (!$this->user->getManager($user_id)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }


        $request->validate([
            'classId' => 'required'
        ]);

        // dd($request->all());

        $class_id = $request->classId;

        $sang = $request->sang;
        $chieu = $request->chieu;

        $checkExten = DiemDanh::where('class_id', $class_id)->get();



        if ($checkExten->count()) {

            $sang_hai = $sang['hai'];
            $sang_ba = $sang['ba'];
            $sang_tu = $sang['tu'];
            $sang_nam = $sang['nam'];
            $sang_sau = $sang['sau'];
            $sang_bay = $sang['bay'];
            $sang_chunhat = $sang['chunhat'];

            $chieu_hai = $chieu['hai'];
            $chieu_ba = $chieu['ba'];
            $chieu_tu = $chieu['tu'];
            $chieu_nam = $chieu['nam'];
            $chieu_sau = $chieu['sau'];
            $chieu_bay = $chieu['bay'];
            $chieu_chunhat = $chieu['chunhat'];


            // Sáng thứ 2
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU2->value, $sang_hai['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU2->value, $sang_hai['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU2->value, $sang_hai['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU2->value, $sang_hai['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU2->value, $sang_hai['tiet5'], $class_id, StatusBuoi::SANG->value);


            // Sáng thứ 3
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU3->value, $sang_ba['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU3->value, $sang_ba['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU3->value, $sang_ba['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU3->value, $sang_ba['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU3->value, $sang_ba['tiet5'], $class_id, StatusBuoi::SANG->value);

            // Sáng thứ 4
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU4->value, $sang_tu['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU4->value, $sang_tu['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU4->value, $sang_tu['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU4->value, $sang_tu['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU4->value, $sang_tu['tiet5'], $class_id, StatusBuoi::SANG->value);

            // Sáng thứ 5
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU5->value, $sang_nam['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU5->value, $sang_nam['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU5->value, $sang_nam['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU5->value, $sang_nam['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU5->value, $sang_nam['tiet5'], $class_id, StatusBuoi::SANG->value);


            // Sáng thứ 6
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU6->value, $sang_sau['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU6->value, $sang_sau['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU6->value, $sang_sau['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU6->value, $sang_sau['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU6->value, $sang_sau['tiet5'], $class_id, StatusBuoi::SANG->value);



            // Sáng thứ 7
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU7->value, $sang_bay['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU7->value, $sang_bay['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU7->value, $sang_bay['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU7->value, $sang_bay['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU7->value, $sang_bay['tiet5'], $class_id, StatusBuoi::SANG->value);


            // Sáng thứ chủ nhật
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet5'], $class_id, StatusBuoi::SANG->value);









            // Chiều thứ 2
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU2->value, $chieu_hai['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU2->value, $chieu_hai['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU2->value, $chieu_hai['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU2->value, $chieu_hai['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU2->value, $chieu_hai['tiet5'], $class_id, StatusBuoi::CHIEU->value);


            // Chiều thứ 3
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU3->value, $chieu_ba['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU3->value, $chieu_ba['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU3->value, $chieu_ba['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU3->value, $chieu_ba['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU3->value, $chieu_ba['tiet5'], $class_id, StatusBuoi::CHIEU->value);

            // Chiều thứ 4
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU4->value, $chieu_tu['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU4->value, $chieu_tu['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU4->value, $chieu_tu['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU4->value, $chieu_tu['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU4->value, $chieu_tu['tiet5'], $class_id, StatusBuoi::CHIEU->value);

            // Chiều thứ 5
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU5->value, $chieu_nam['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU5->value, $chieu_nam['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU5->value, $chieu_nam['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU5->value, $chieu_nam['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU5->value, $chieu_nam['tiet5'], $class_id, StatusBuoi::CHIEU->value);


            // Chiều thứ 6
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU6->value, $chieu_sau['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU6->value, $chieu_sau['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU6->value, $chieu_sau['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU6->value, $chieu_sau['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU6->value, $chieu_sau['tiet5'], $class_id, StatusBuoi::CHIEU->value);



            // Chiều thứ 7
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::THU7->value, $chieu_bay['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::THU7->value, $chieu_bay['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::THU7->value, $chieu_bay['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::THU7->value, $chieu_bay['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::THU7->value, $chieu_bay['tiet5'], $class_id, StatusBuoi::CHIEU->value);


            // Chiều thứ chủ nhật
            $this->suaDiemDanh(StatusTiet::TIET1->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET2->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET3->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET4->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->suaDiemDanh(StatusTiet::TIET5->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet5'], $class_id, StatusBuoi::CHIEU->value);


            return response()->json([
                'msg' => 'Sửa thời khóa biểu thành công'
            ], ResponseAlias::HTTP_OK);
        } else {


            $sang_hai = $sang['hai'];
            $sang_ba = $sang['ba'];
            $sang_tu = $sang['tu'];
            $sang_nam = $sang['nam'];
            $sang_sau = $sang['sau'];
            $sang_bay = $sang['bay'];
            $sang_chunhat = $sang['chunhat'];

            $chieu_hai = $chieu['hai'];
            $chieu_ba = $chieu['ba'];
            $chieu_tu = $chieu['tu'];
            $chieu_nam = $chieu['nam'];
            $chieu_sau = $chieu['sau'];
            $chieu_bay = $chieu['bay'];
            $chieu_chunhat = $chieu['chunhat'];


            // Sáng thứ 2
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU2->value, $sang_hai['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU2->value, $sang_hai['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU2->value, $sang_hai['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU2->value, $sang_hai['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU2->value, $sang_hai['tiet5'], $class_id, StatusBuoi::SANG->value);


            // Sáng thứ 3
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU3->value, $sang_ba['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU3->value, $sang_ba['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU3->value, $sang_ba['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU3->value, $sang_ba['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU3->value, $sang_ba['tiet5'], $class_id, StatusBuoi::SANG->value);

            // Sáng thứ 4
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU4->value, $sang_tu['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU4->value, $sang_tu['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU4->value, $sang_tu['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU4->value, $sang_tu['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU4->value, $sang_tu['tiet5'], $class_id, StatusBuoi::SANG->value);

            // Sáng thứ 5
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU5->value, $sang_nam['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU5->value, $sang_nam['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU5->value, $sang_nam['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU5->value, $sang_nam['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU5->value, $sang_nam['tiet5'], $class_id, StatusBuoi::SANG->value);


            // Sáng thứ 6
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU6->value, $sang_sau['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU6->value, $sang_sau['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU6->value, $sang_sau['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU6->value, $sang_sau['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU6->value, $sang_sau['tiet5'], $class_id, StatusBuoi::SANG->value);



            // Sáng thứ 7
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU7->value, $sang_bay['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU7->value, $sang_bay['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU7->value, $sang_bay['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU7->value, $sang_bay['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU7->value, $sang_bay['tiet5'], $class_id, StatusBuoi::SANG->value);


            // Sáng thứ chủ nhật
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet1'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet2'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet3'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet4'], $class_id, StatusBuoi::SANG->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::CHUNHAT->value, $sang_chunhat['tiet5'], $class_id, StatusBuoi::SANG->value);









            // Chiều thứ 2
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU2->value, $chieu_hai['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU2->value, $chieu_hai['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU2->value, $chieu_hai['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU2->value, $chieu_hai['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU2->value, $chieu_hai['tiet5'], $class_id, StatusBuoi::CHIEU->value);


            // Chiều thứ 3
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU3->value, $chieu_ba['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU3->value, $chieu_ba['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU3->value, $chieu_ba['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU3->value, $chieu_ba['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU3->value, $chieu_ba['tiet5'], $class_id, StatusBuoi::CHIEU->value);

            // Chiều thứ 4
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU4->value, $chieu_tu['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU4->value, $chieu_tu['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU4->value, $chieu_tu['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU4->value, $chieu_tu['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU4->value, $chieu_tu['tiet5'], $class_id, StatusBuoi::CHIEU->value);

            // Chiều thứ 5
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU5->value, $chieu_nam['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU5->value, $chieu_nam['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU5->value, $chieu_nam['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU5->value, $chieu_nam['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU5->value, $chieu_nam['tiet5'], $class_id, StatusBuoi::CHIEU->value);


            // Chiều thứ 6
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU6->value, $chieu_sau['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU6->value, $chieu_sau['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU6->value, $chieu_sau['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU6->value, $chieu_sau['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU6->value, $chieu_sau['tiet5'], $class_id, StatusBuoi::CHIEU->value);



            // Chiều thứ 7
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::THU7->value, $chieu_bay['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::THU7->value, $chieu_bay['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::THU7->value, $chieu_bay['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::THU7->value, $chieu_bay['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::THU7->value, $chieu_bay['tiet5'], $class_id, StatusBuoi::CHIEU->value);


            // Chiều thứ chủ nhật
            $this->taoDiemDanh(StatusTiet::TIET1->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet1'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET2->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet2'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET3->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet3'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET4->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet4'], $class_id, StatusBuoi::CHIEU->value);
            $this->taoDiemDanh(StatusTiet::TIET5->value, StatusThu::CHUNHAT->value, $chieu_chunhat['tiet5'], $class_id, StatusBuoi::CHIEU->value);


            return response()->json([
                'msg' => 'Tạo thời khóa biểu thành công'
            ], ResponseAlias::HTTP_OK);
        }
    }



    public function taoDiemDanh($tiet, $thu, $mon, $class_id, $buoi)
    {
        if (!$mon) return;
        $item = new DiemDanh();
        $item->tiet = $tiet;
        $item->thu = $thu;
        $item->mon = $mon;
        $item->class_id = $class_id;
        $item->buoi = $buoi;

        // lấy lại lịch sử

        // tạo thêm
        $item->save();
    }


    public function suaDiemDanh($tiet, $thu, $mon, $class_id, $buoi)
    {
        $item = DiemDanh::where('tiet', $tiet)->where('thu', $thu)->where('class_id', $class_id)->where('buoi', $buoi)->first();

        if ($item) {
            $item->mon = $mon;
            $item->save();
        } else {
            $this->taoDiemDanh($tiet, $thu, $mon, $class_id, $buoi);
        }
    }
}
