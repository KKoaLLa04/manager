<?php
namespace App\Domain\Student\Requests;

use App\Common\Enums\StatusClassStudentEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StudentUpdateRequest extends FormRequest 
{
    public function __construct()
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'fullname' => 'required|min:3|max:255',
            'address' => 'required|min:5|max:255',
            'dob' => 'required|date|before:today',
            'status' => 'required|in:0,1,2',
            'gender' => 'required|in:1,2',
            'class_id' => [
                    'nullable',
                    'integer',
                    'exists:classes,id',
                    'required_if:status,' . StatusClassStudentEnum::STUDYING->value,
                    function ($attribute, $value, $fail) {
                        // Lấy giá trị status từ request
                        $status = $this->input('status');
                        
                        if (($status == StatusClassStudentEnum::STUDYING->value || $status == StatusClassStudentEnum::LEAVE->value) 
                        && $this->input('status') == StatusClassStudentEnum::NOT_YET_CLASS->value) {
                        $fail('Không thể chuyển sang trạng thái chưa vào lớp khi học sinh đã có lớp.');
                        }
                    
                    },
                ],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Họ tên là bắt buộc.',
            'address.required' => 'Địa chỉ là bắt buộc.',
            'dob.required' => 'Ngày sinh là bắt buộc.',
            'dob.before' => 'Ngày sinh phải nhỏ hơn ngày hiện tại.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'gender.required' => 'Giới tính là bắt buộc.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'class_id.exists' => 'Lớp học không tồn tại.',
            'class_id.required_if' => 'Lớp học là bắt buộc khi trạng thái là đang học.',
        ];
    }

    public function attributes(): array
    {
        return [
            'fullname' => 'Họ tên',
            'address' => 'Địa chỉ',
            'dob' => 'Ngày sinh',
            'status' => 'Trạng thái',
            'gender' => 'Giới tính',
            'class_id' => 'Lớp học',
        ];
    }
     // public function handle(int $id, int $user_id, StudentUpdateRequest $request) 
    // {
    //     $request->validated();
        
    //     // Tìm học sinh với ID
    //     $item = ModelsStudent::find($id);
        
    //     // Lưu thông tin học sinh
    //     $item->fullname = $request->fullname;
    //     $item->address = $request->address; 
    //     $item->dob = $request->dob;
    //     $item->gender = $request->gender;
    //     $item->modified_user_id = $user_id;
    
    //     // Lưu đối tượng học sinh
    //     if ($item->save()) {
    //         // Lấy class_id hiện tại từ lịch sử lớp học gần nhất
    //         $studentClassHistory = StudentClassHistory::where('student_id', $id)
    //                                                   ->orderBy('start_date', 'desc')
    //                                                   ->first();
    
    //         $classId = $request->class_id;
    //         $status = $request->status;
    
    //         // Nếu status là 0 (nghỉ học), cập nhật end_date cho lớp hiện tại
    //         if ($status == StatusClassStudentEnum::LEAVE->value && $studentClassHistory) {
    //             // Cập nhật end_date cho lớp hiện tại
    //             $studentClassHistory->end_date = now(); 
    //             $studentClassHistory->modified_user_id = $user_id;
    //             $studentClassHistory->status = StatusClassStudentEnum::LEAVE->value; // Cập nhật trạng thái lớp thành nghỉ học
    //             $studentClassHistory->save();
    //         }
    
    //         // Nếu có class_id mới và không phải là trạng thái nghỉ học
    //         if ($classId && $studentClassHistory && $status != StatusClassStudentEnum::LEAVE->value) {
    //             // Kiểm tra xem có phải là chuyển lớp hay không
    //             if ($studentClassHistory->class_id != $classId) {
    //                 // Cập nhật end_date cho lớp hiện tại trước khi chuyển lớp
    //                 $studentClassHistory->end_date = now(); 
    //                 $studentClassHistory->modified_user_id = $user_id;
    //                 $studentClassHistory->status = StatusClassStudentEnum::LEAVE->value; // Cập nhật trạng thái lớp thành nghỉ học
    //                 $studentClassHistory->save();
    //             }
    
    //             // Tạo một bản ghi mới cho lớp học mới
    //             $newStudentClassHistory = new StudentClassHistory();
    //             $newStudentClassHistory->student_id = $item->id;
    //             $newStudentClassHistory->class_id = $classId;
    //             $newStudentClassHistory->start_date = now(); // Gán ngày bắt đầu
    //             $newStudentClassHistory->end_date = null; // Lớp hiện tại chưa kết thúc
    //             $newStudentClassHistory->status = StatusClassStudentEnum::STUDYING->value; 
    //             $newStudentClassHistory->is_deleted = DeleteEnum::NOT_DELETE; 
    //             $newStudentClassHistory->created_user_id = $user_id; // Ghi lại người tạo
    
    //             // Lưu đối tượng class history mới
    //             $newStudentClassHistory->save();
    //         }
    
    //         // Nếu status là 2 (chưa vào lớp), không cần tạo bản ghi mới và gán class_id thành null
    //         if ($status == StatusClassStudentEnum::NOT_YET_CLASS->value) {
    //             // Chỉ cần lưu một bản ghi mới với class_id null
    //             $newStudentClassHistory = new StudentClassHistory();
    //             $newStudentClassHistory->student_id = $item->id;
    //             $newStudentClassHistory->class_id = null; // Không có lớp
    //             $newStudentClassHistory->start_date = now(); 
    //             $newStudentClassHistory->end_date = null; 
    //             $newStudentClassHistory->status = StatusClassStudentEnum::NOT_YET_CLASS->value; 
    //             $newStudentClassHistory->is_deleted = DeleteEnum::NOT_DELETE; 
    //             $newStudentClassHistory->created_user_id = $user_id; // Ghi lại người tạo
    
    //             // Lưu đối tượng class history mới
    //             $newStudentClassHistory->save();
    //         }
    //     }
    //     return true;
    // }
    
}
            