<?php

namespace App\Domain\Timetable\Controllers;

use App\Common\Enums\DeleteEnum;
use App\Domain\Timetable\Repository\CategoryTimetableRepository;
use App\Http\Controllers\BaseController;
use App\Models\CategoryAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CategoryTimetableController extends BaseController
{

    public function __construct(
        Request                       $request,
        protected  CategoryTimetableRepository $categoryTimetableRepository,
    ) {
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data = CategoryAttendance::query()->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();
        $data = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'from_date' => Carbon::parse($item->from_date)->format('d-m-Y'),
                'to_date' => Carbon::parse($item->to_date)->format('d-m-Y'),
            ];
        });
        return $this->responseSuccess($data);
    }

    public function edit(Request $request)
    {
        $name = isset($request->name) ? $request->name : "";
        $fromDate = isset($request->from_date) ? Carbon::parse($request->from_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $toDate = isset($request->to_date) ? Carbon::parse($request->to_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $id = $request->id;
        $checkCategoryAttendance = $this->categoryTimetableRepository->checkCategoryTimetableExists($fromDate, $toDate,$id);
        if ($checkCategoryAttendance) {
            return $this->responseError('Ngày bắt đầu và ngày kết thúc đăng trung với đợt khác');
        }
        $data = [
            'name' => $name,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
        $this->categoryTimetableRepository->update($data, $id);
        return $this->responseSuccess();
    }


    public function store(Request $request)
    {
        $name = isset($request->name) ? $request->name : "";
        $fromDate = isset($request->from_date) ? Carbon::parse($request->from_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $toDate = isset($request->to_date) ? Carbon::parse($request->to_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $checkCategoryAttendance = $this->categoryTimetableRepository->checkCategoryTimetableExists($fromDate, $toDate);
        if ($checkCategoryAttendance) {
            return $this->responseError('Ngày bắt đầu và ngày kết thúc đăng trung với đợt khác');
        }
        $data = [
            'name' => $name,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
        $this->categoryTimetableRepository->create($data);
        return $this->responseSuccess();
    }

    public function delete(Request $request)
    {
        $id = $request->id;

        $this->categoryTimetableRepository->deleted($id);
        return $this->responseSuccess();
    }

}
