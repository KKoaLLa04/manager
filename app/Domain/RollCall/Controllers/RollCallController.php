<?php
namespace App\Domain\RollCall\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\RollCall\Models\RollCall;
use App\Domain\RollCall\Repository\RollCallRepository;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RollCallController extends BaseController
{

    protected $rollCallRepository;

    public function __construct(RollCallRepository $rollCallRepository)
    {   
        $this->rollCallRepository = $rollCallRepository;
    }
    public function index()
    {
        // Gọi phương thức từ repository để lấy danh sách roll_call
        $rollCalls = $this->rollCallRepository->getAllRollCalls();

        // Tính toán tổng số lớp đã điểm danh và chưa điểm danh
        $totalClassAttendanced = $rollCalls->count(); // Tổng số lớp đã điểm danh
        $totalClassNoAttendance = RollCall::where('status', 0)->count(); // Tổng số lớp chưa điểm danh

        // Trả về dữ liệu dưới dạng JSON
        return response()->json([
            'msg' => 'api.rollcall.index.success',
            'data' => [
                'totalClassAttendanced' => $totalClassAttendanced,
                'totalClassNoAttendance' => $totalClassNoAttendance,
                'rollCalls' => $rollCalls // Danh sách roll_calls
            ]
        ]);
    }
}
            