<?php
namespace App\Domain\Class\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Domain\Class\Repository\ClassRepository;
use App\Domain\Class\Requests\GetClassRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ClassController extends BaseController
{
    public function __construct(
        Request $request,
        protected ClassRepository $classRepository
    )
    {
        parent::__construct($request);
    }
    public function index(GetClassRequest $request)
    {
        if(Auth::user()->access_type != AccessTypeEnum::MANAGER->value){
            return $this->responseError(trans('api.error.not_found'),ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $checkSchoolYearId = $this->classRepository->checkSchoolYearId($request->school_year_id);
        if(!$checkSchoolYearId){
            return $this->responseError(trans('api.error.not_found'));
        }

        $classes = $this->classRepository->getClasses($request);
    }
}
