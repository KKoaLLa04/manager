<?php

namespace App\Domain\School\Controllers;

use App\Domain\School\Repository\SchoolRepository;

use App\Domain\School\Requests\SchoolRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SchoolController extends BaseController
{
    protected $schoolRepository;

    public function __construct(Request $request, SchoolRepository $schoolRepository)
    {
        parent::__construct($request);
        $this->schoolRepository = $schoolRepository;
    }

    public function index()
    {
        // Lấy danh sách các trường học từ repository
        $schools = $this->schoolRepository->getAllSchools();

        // Trả về danh sách với status thành công
        return response()->json([
            'status' => 'success',
            'data' => $schools
        ], 200);
    }

    // Phương thức sửa thông tin một trường học theo ID
    public function update($id, SchoolRequest $request)
    {
        // Lấy tất cả dữ liệu từ request
        $data = $request->all();

        // Kiểm tra và xử lý upload avatar (nếu có)
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        // Kiểm tra và xử lý upload logo (nếu có)
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;
        }

        // Cập nhật thông tin trường học thông qua repository
        $updatedSchool = $this->schoolRepository->updateSchool($id, $data);

        // Trả về kết quả sau khi cập nhật
        return response()->json([
            'status' => 'success',
            'message' => 'Thông tin trường học đã được cập nhật thành công.',
            'data' => $updatedSchool,
        ], 200);
    }
    public function show($id): JsonResponse
    {
        // Tìm trường học theo ID
        $school = $this->schoolRepository->findSchoolById($id); 

        if (!$school) {
            return response()->json([
                'status' => 'error',
                'message' => 'Trường học không tồn tại.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $school
        ], 200);
    }

}