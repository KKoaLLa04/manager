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
        'regex' => 'Số điện thoại phải có độ dài từ 10-11 chữ số và chỉ chứa số.',
        'integer' => 'phải là kiểu số nguyên',
        'date' => 'phải là kiểu ngày tháng năm',
        'after_or_equal' => 'phải là ngày sau hoặc bằng :date',
        'same' => 'Bắt buộc phải giống :other',
        'email' => 'phải là kiểu email',
        'school_year' => [
            'start_date_before_end_date' => 'Thời gian bắt đầu phải lớn hơn hoặc bằng thời gian hiện tại',
            'start_date_not_equal_end_date_before' => 'Năm bắt đầu không được bằng năm kết thúc của năm học trước đó'
        ]
    ],
    'alert' => [
        'school_year' => [
            'delete_success' => 'Xóa năm học thành công',
            'delete_failed' => 'Xóa năm học thất bại',
            'index_success' => 'Lấy danh sách năm học thành công',
            'index_failed' => 'Lấy danh sách năm học thất bại',
            'detail_success' => 'Lấy chi tiết năm học thành công',
            'detail_failed' => 'Lấy chi tiết năm học thất bại',
            'add_success' => 'Thêm mới năm học thành công',
            'add_failed' => 'Thêm mới năm học thất bại',
            'edit_success' => 'Sửa năm học thành công',
            'edit_failed' => 'Sửa năm học thất bại',
        ],
        'together' => [
            'delete_success' => 'Xóa thành công',
            'delete_failed' => 'Xóa thất bại',
            'index_success' => 'Lấy danh sách thành công',
            'index_failed' => 'Lấy danh sách thất bại',
            'detail_success' => 'Lấy chi tiết thành công',
            'detail_failed' => 'Lấy chi tiết thất bại',
            'add_success' => 'Thêm mới thành công',
            'add_failed' => 'Thêm mới thất bại',
            'edit_success' => 'Sửa thành công',
            'edit_failed' => 'Sửa thất bại',
        ]
    ]
];
