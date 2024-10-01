<?php

return [
    'error' => [
        'not_found'             => 'Dữ liệu không tồn tại',
        'record_duplicate'      => 'Dữ liệu này đã tồn tại',
        'school_has_not_module' => 'Trường của bé chưa được sử dụng tính năng này',
        'user_not_permission' => 'Người dùng không có quyền truy cập',
        
        'required' => ' trường này là bắt buộc.',
        'string' => ' phải là một chuỗi ký tự.',
        'min' => ' phải có ít nhất :min ký tự.',
        'max' => ' không được vượt quá :max ký tự.',
        'unique' => ' đã tồn tại, vui lòng chọn mã khác.',
        'url' => 'URL không hợp lệ.',
        'image' => ' phải là một tệp hình ảnh.',
        'mimes' => ' phải ở một trong các định dạng sau: jpeg, png, jpg, hoặc gif.',
        'regex' => 'Số điện thoại phải có độ dài từ 10-11 chữ số và chỉ chứa số.'

    ],
    'school_year' => [
        'start_date_before_end_date' => 'Thời gian bắt đầu phải lớn hơn hoặc bằng thời gian hiện tại',
    ],

    'academic_year' => [
        'index.success'=> 'Lấy danh sách niên khóa thành công',
        'index.errors'=> 'Lấy danh sách niên khóa niên khóa',
        'add.success' => 'Thêm mới niên khóa thành công',
        'add.errors'=> 'Thêm mới niên khóa thất bại',
        'edit.success' => 'Cập nhập niên khóa thành công',
        'edit.errors' => 'Niên khóa đã kết thúc',
        'delete.success' => 'Xóa niên khóa thành công',
        'delete.errors' => 'Niên khóa vẫn còn hoạt động!',
    ],
];
