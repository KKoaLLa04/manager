<?php

return [
    'error' => [
        'not_found'             => 'Dữ liệu không tồn tại',
        'record_duplicate'      => 'Dữ liệu này đã tồn tại',
        'school_has_not_module' => 'Trường của bé chưa được sử dụng tính năng này',
        'user_not_permission' => 'Người dùng không có quyền truy cập',
        

        'required' => 'Trường mã trường học là bắt buộc.',
        'string' => 'Mã trường học phải là chuỗi ký tự.',
        'min' => 'Mã trường học phải có ít nhất :min ký tự.',
        'max' => 'Mã trường học không được vượt quá :max ký tự.',
        'unique' => 'Mã trường học này đã tồn tại, vui lòng chọn mã khác.',
        
         // Messages cho trường 'name'
         'required' => 'Trường tên trường học là bắt buộc.',
         'string' => 'Tên trường học phải là chuỗi ký tự.',
         'min' => 'Tên trường học phải có ít nhất :min ký tự.',
         'max' => 'Tên trường học không được vượt quá :max ký tự.',

         // Messages cho trường 'avatar'
         'url' => 'URL của avatar không hợp lệ.',
         'image' => 'Avatar phải là một tệp hình ảnh.',
         'mimes' => 'Avatar phải có định dạng: jpeg, png, jpg, hoặc gif.',
         'max' => 'Kích thước avatar không được vượt quá 2MB.',

         // Messages cho trường 'address'
         'required' => 'Địa chỉ là bắt buộc.',
         'string' => 'Địa chỉ phải là chuỗi ký tự.',
         'max' => 'Địa chỉ không được vượt quá :max ký tự.',

         // Messages cho trường 'logo'
         'url' => 'URL của logo không hợp lệ.',
         'image' => 'Logo phải là một tệp hình ảnh.',
         'mimes' => 'Logo phải có định dạng: jpeg, png, jpg, hoặc gif.',
         'max' => 'Kích thước logo không được vượt quá 2MB.',

         // Messages cho trường 'telephone'
         'required' => 'Số điện thoại là bắt buộc.',
         'regex' => 'Số điện thoại phải có độ dài 10-11 chữ số và chỉ chứa số.',

         // Messages cho trường 'email'
         'required' => 'Email là bắt buộc.',
         'unique' => 'Email này đã tồn tại, vui lòng chọn email khác.',

         // Messages cho trường 'modified_user_id'
         'modified_user_id.required' => 'Người dùng chỉnh sửa là bắt buộc.',
    ],
];
