<?php
namespace App\Domain\Subject\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Common\Repository\GetUserRepository;
use App\Domain\Subject\Repository\SubjectIndexRepository;
use Illuminate\Support\Facades\Auth;

class SubjectController extends BaseController
{

    private $user;

    public function __construct(Request $request)
    {

        $this->user = new GetUserRepository();
        parent::__construct($request);
    }
    public function index(Request $request)
    {

        $user_id = Auth::user()->id;

        if (!$this->user->getManager($user_id)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $IndexRepository = new SubjectIndexRepository();

        $check = $IndexRepository->handle();

        if ($check) {
            return $this->responseSuccess(['data' => $check->toArray()], trans('api.alert.together.index_success'));
        } else {
            return $this->responseError(trans('api.alert.together.index_failed'));
        }


    }
}
