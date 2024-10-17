<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User();
        $user->fullname = "xuân hoàng";
        $user->phone = "0966694210";
        $user->email = "xuanhoang@gmail.com";
        $user->username = "hoangdxph";
        $user->password = Hash::make('12345678');
        $user->code = "PH32103";
        $user->address = "HaNoi";
        $user->access_type = 1;
        $user->dob = "2024-07-31";
        $user->status = 1;
        $user->gender = 1;
        $user->is_deleted = 0;
        $user->created_user_id = 0;
        $user->modified_user_id = 0;
        $user->save();
    }
}
