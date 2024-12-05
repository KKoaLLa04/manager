<?php

namespace App\Domain\Point\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\PaginateEnum;
use App\Domain\Point\Repository\ExamRepository;
use App\Domain\Point\Requests\DeleteExamRequest;
use App\Domain\Point\Requests\GetExamRequest;
use App\Domain\Point\Requests\StoreExamRequest;
use App\Domain\Point\Requests\UpdateExamRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ExamController extends BaseController
{
    public function __construct(
        protected ExamRepository $examPeriodRepository
    ) {
        parent::__construct();
    }

    public function index(GetExamRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $search       = isset($request->search) ? $request->search : '';
        $page         = isset($request->page) ? (int)$request->page : PaginateEnum::PAGE->value;
        $size         = isset($request->size) ? (int)$request->size : PaginateEnum::MAX_SIZE->value;
        $schoolYearId = isset($request->school_year_id) ? (int)$request->school_year_id : 0;
        $examPeriods  = $this->examPeriodRepository->getExam($search, $page, $size, $schoolYearId);
        if ($examPeriods->count() > 0) {
            return $this->responseSuccess($this->examPeriodRepository->transformGetExam($examPeriods));
        } else {
            return response()->json(['status' => 'success', 'data' => []]);
        }
    }

    public function store(StoreExamRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $dataInsert = [
            "name"           => $request->name ?? "",
            "school_year_id" => (int)$request->school_year_id,
            "point"          => (int)$request->point,
            "created_by"     => Auth::id(),
        ];
        $this->examPeriodRepository->storeExam($dataInsert);
        return $this->responseSuccess([]);
    }

    public function update(UpdateExamRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $examPeriodId = $request->exam_period_id;
        $dataUpdate = [
            "name"           => $request->name ?? "",
            "school_year_id" => (int)$request->school_year_id,
            "point"          => (int)$request->point,
            "updated_by"     => Auth::id(),
        ];
        $this->examPeriodRepository->updateExam($dataUpdate,$examPeriodId);
        return $this->responseSuccess([]);
    }

    public function delete(DeleteExamRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $examPeriodId = $request->exam_period_id;

        $this->examPeriodRepository->deleteExam($examPeriodId);
        return $this->responseSuccess([]);
    }

}
