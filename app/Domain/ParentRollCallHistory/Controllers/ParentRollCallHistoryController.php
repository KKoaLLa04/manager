<?php
namespace App\Domain\ParentRollCallHistory\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Domain\ParentRollCallHistory\Repository\ParentRollCallHistoryRepository;
use Illuminate\Support\Facades\Auth;

class ParentRollCallHistoryController extends BaseController
{
    protected $ParentRollCallHistoryRepository;
    private $user;
    public function __construct(Request $request, ParentRollCallHistoryRepository $parentRollCallHistoryRepository)
    {
        $this->user=  new GetUserRepository();
        parent::__construct( $request);
        $this->ParentRollCallHistoryRepository = $parentRollCallHistoryRepository;
    }
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::GUARDIAN->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

            $userId = $request->user()->id;
            $studentId = $request->query('studentId');
            $pageSize = $request->query('pageSize', 10);
            $keyWord = $request->query('keyWord');
            $date = $request->query('date');


            $result = $this->ParentRollCallHistoryRepository->getParentStudentRollCallHistories(
                $userId, $pageSize, $keyWord, $date, $studentId
            );

            return response()->json($result);
        }

}
