<?php

return [
    'error' => [
        'not_found'             => 'Data does not exist',
        'record_duplicate'      => 'This data already exists',
        'school_has_not_module' => 'Your school does not have this feature yet.',
        'user_not_permission' => 'User does not have access',
        

        'required' => 'The school code field is required.',
        'string' => 'The school code must be a string.',
        'min' => 'The school code must be at least :min characters.',
        'max' => 'The school code must not exceed :max characters.',
        'unique' => 'This school code already exists, please choose another code.',

        // Messages for the 'name' field
        'required' => 'The school name field is required.',
        'string' => 'The school name must be a string.',
        'min' => 'The school name must be at least :min characters.',
        'max' => 'The school name must not exceed :max characters.',

        // Messages for the 'avatar' field
        'url' => 'The avatar URL is invalid.',
        'image' => 'The avatar must be an image file.',
        'mimes' => 'The avatar must be in one of the following formats: jpeg, png, jpg, or gif.',
        'max' => 'The avatar must not exceed 2MB in size.',

        // Messages for the 'address' field
        'required' => 'The address field is required.',
        'string' => 'The address must be a string.',
        'max' => 'The address must not exceed :max characters.',

        // Messages for the 'logo' field
        'url' => 'The logo URL is invalid.',
        'image' => 'The logo must be an image file.',
        'mimes' => 'The logo must be in one of the following formats: jpeg, png, jpg, or gif.',
        'max' => 'The logo must not exceed 2MB in size.',

        // Messages for the 'telephone' field
        'required' => 'The telephone number is required.',
        'regex' => 'The telephone number must be 10-11 digits long and contain only numbers.',

        // Messages for the 'email' field
        'required' => 'The email field is required.',
        'unique' => 'This email already exists, please choose another one.',

        // Messages for the 'modified_user_id' field
        'modified_user_id.required' => 'The modified user is required.',

    ],
    
];
