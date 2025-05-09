<?php

namespace App\Domain\Point\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\PaginateEnum;
use App\Domain\Point\Repository\ExamPeriodRepository;
use App\Domain\Point\Repository\ExamRepository;
use App\Domain\Point\Requests\DeleteExamPeriodRequest;
use App\Domain\Point\Requests\DeleteExamRequest;
use App\Domain\Point\Requests\GetExamPeriodRequest;
use App\Domain\Point\Requests\GetExamRequest;
use App\Domain\Point\Requests\StoreExamPeriodRequest;
use App\Domain\Point\Requests\StoreExamRequest;
use App\Domain\Point\Requests\UpdateExamPeriodRequest;
use App\Domain\Point\Requests\UpdateExamRequest;
use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ExamPeriodController extends BaseController
{
    public function __construct(
        private ExamPeriodRepository $examPeriodRepository,
    ) {
        parent::__construct();
    }

    public function index(GetExamPeriodRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $examPeriods = $this->examPeriodRepository->getExamPeriodById($request->exam_id);
        $data = $this->examPeriodRepository->transformExamPeriod($examPeriods);
        return $this->responseSuccess($data);

    }

    public function store(StoreExamPeriodRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $date = isset($request->date) ? Carbon::parse($request->date)->toDateString() : Carbon::now()->toDateString();
        $name = isset($request->name) ? $request->name : "";
        $type = isset($request->type) ? $request->type : 1;
        $data = [
            'exam_id' => $request->exam_id,
            'date' => $date,
            'name' => $name,
            'type' => $type,
            'created_by' => Auth::id(),
        ];

        $this->examPeriodRepository->storeExamPeriod($data);
        return $this->responseSuccess([]);

    }

    public function update(UpdateExamPeriodRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $date = isset($request->date) ? Carbon::parse($request->date)->toDateString() : Carbon::now()->toDateString();
        $name = isset($request->name) ? $request->name : "";
        $type = isset($request->type) ? $request->type : 1;
        $dataUpdate = [
            'exam_id' => $request->exam_id,
            'date' => $date,
            'name' => $name,
            'type' => $type,
            'updated_by' => Auth::id(),
        ];


        $this->examPeriodRepository->updateExamPeriod($dataUpdate, $request->exam_period_id);
        return $this->responseSuccess([]);

    }

    public function delete(DeleteExamPeriodRequest $request)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $this->examPeriodRepository->deleteExamPeriod($request->exam_period_id);
        return $this->responseSuccess([]);

    }

}
