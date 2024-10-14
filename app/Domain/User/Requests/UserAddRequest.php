<?php
namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            'fullname' => [
                'required',
                'min:6',
                'max:255'
            ],
            'username' => [
                'required',
                'min:6',
                'max:255',
                'unique:users,username'
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'min:6',
                'max:255'
            ],
            'confirm' => [
                'required',
                'same:password'
            ],
            'phone' => [
                'required',
                'regex:/^0[0-9]{9}$/',
                'unique:users,phone'
            ],
            'address' => [
            ],
            'access_type' => [
                'required',
                'integer',
            ],
            'dob' => [
                'required',
                'date'
            ],
            'status' => [
                'required',
                'integer'
            ],
            'gender' => [
                'required',
                'integer'
            ],
            // 'active' => [
            //     'required',
            //     'integer'
            // ],
            'user_id' => [
                'required',
                'integer'
            ],
            'type' => [
                'required',
                'integer'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => trans('api.error.required'),
            'integer' => trans('api.error.integer'),
            'date' => trans('api.error.date'),
            'regex' => trans('api.error.regex'),
            'same' => trans('api.error.same'),
            'unique' => trans('api.error.unique'),
            'email' => trans('api.error.email'),
        ];
    }

}
