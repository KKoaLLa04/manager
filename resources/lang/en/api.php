<?php

return [
    'error' => [
        'not_found'             => 'Data does not exist',
        'record_duplicate'      => 'This data already exists',
        'school_has_not_module' => 'Your school does not have this feature yet.',
        'user_not_permission' => 'User does not have access',
        
        'required' => '  field is required.',
        'string' => '  must be a string.',
        'min' => '  must be at least :min characters.',
        'max' => '  must not exceed :max characters.',
        'unique' => '  already exists, please choose another code.',
        'url' => 'The URL is invalid.',
        'image' => 'The must be an image file.',
        'mimes' => 'The must be in one of the following formats: jpeg, png, jpg, or gif.',
        'regex' => 'The telephone number must be 10-11 digits long and contain only numbers.',

    ],

    'guardian' => [
    'index.success' => 'Successfully retrieved guardian list',
    'index.errors' => 'Failed to retrieve guardian list',
    'add.success' => 'Successfully added a new guardian',
    'add.errors' => 'Failed to add new guardian',
    'edit.success' => 'Successfully updated guardian',
    'edit.errors' => 'The guardian has been terminated',
    'delete.success' => 'Successfully deleted guardian',
    'delete.errors' => 'The guardian is still active!',
    'show.success' => 'Successfully retrieved a guardian',
    'show.errors' => 'Failed to retrieve a guardian',
    'password_mismatch' => 'The confirmation password does not match the password',
    'lock.success' => 'Account successfully locked',
    'lock.errors' => 'Failed to lock account',
    'unlock.success' => 'Account successfully unlocked',
    'unlock.errors' => 'Failed to unlock account',
    'change_password.success' => 'Successfully changed guardian password',
    'change_password.errors' => 'Failed to change guardian password',
    ],

    
];
