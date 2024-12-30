<?php
namespace App\Domain\ParentSubject\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\ParentSubject\Repository\ParentSubjectReponsitory;
use App\Http\Controllers\BaseController;
use App\Models\UserStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentSubjectController extends BaseController
{
    protected $ParentSubjectReponsitory;
    private $user;
    public function __construct(Request $request, ParentSubjectReponsitory $ParentSubjectReponsitory)
    {
        $this->user=  new GetUserRepository();
        parent::__construct( $request);
        $this->ParentSubjectReponsitory = $ParentSubjectReponsitory;
    }
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::GUARDIAN->value;

        // Kiểm tra quyền truy cập của phụ huynh
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Lấy student_id từ request
        $student_id = $request->input('student_id', null);

        // Kiểm tra quyền truy cập của phụ huynh
        if (Auth::user()->access_type != $type) {
            return response()->json([
                'success' => false,
                'message' => trans('api.error.user_not_permission'),
            ], 403);
        }

        if (!$student_id) {
            $userStudent = UserStudent::where('user_id', $user_id)
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->first();

            if (!$userStudent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Con không thuộc quyền quản lý của phụ huynh',
                ], 404);
            }

            $student_id = $userStudent->student_id;
        }

        $userStudent = UserStudent::where('student_id', $student_id)
                                  ->where('user_id', $user_id)
                                  ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                                  ->first();

        if (!$userStudent) {
            return response()->json([
                'success' => false,
                'message' => 'Con không thuộc quyền quản lý của phụ huynh',
            ], 404);
        }

        $subjects = $this->ParentSubjectReponsitory->getSubjectsByParent($student_id);

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }


}
