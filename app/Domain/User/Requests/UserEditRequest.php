<?php
namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserEditRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            'userName' => [
                'required',
                'min:6',
                'max:255'
            ],
            // 'username' => [
            //     'required',
            //     'min:6',
            //     'max:255',
            //     'unique:users,username,'.$this->id
            // ],
            'userEmail' => [
                'required',
                'email',
                'unique:users,email,'.$this->id
            ],
            // 'password' => [
            //     // 'required',
            //     // 'min:6',
            //     'max:255'
            // ],
            // 'confirm' => [
            //     // 'required',
            //     'same:password'
            // ],
            'userPhone' => [
                'required',
                'regex:/^0[0-9]{9}$/',
                'unique:users,phone,'.$this->id
            ],
            'userAddress' => [
            ],
            'userAccessType' => [
                'required',
                'integer',
            ],
            'userDob' => [
                'required',
                'date'
            ],
            'userStatus' => [
                'required',
                'integer'
            ],
            'userGender' => [
                'required',
                'integer'
            ],
            // 'active' => [
            //     'required',
            //     'integer'
            // ],
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
