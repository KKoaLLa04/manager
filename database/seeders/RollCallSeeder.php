<?php

namespace Database\Seeders;

use App\Domain\RollCall\Models\RollCall;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RollCallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rollcall = new RollCall();
        $rollcall->student_id = 1;
        $rollcall->note = "hehhe";
        $rollcall->class_id = 1;
        $rollcall->date = now();
        $rollcall->time = "12:00:00";
        $rollcall->status = 1;
        $rollcall->is_deleted = 0;
        $rollcall->created_user_id = 1;
        $rollcall->modified_user_id = 1;
        $rollcall->created_user_id = 1;
        $rollcall->save();
    }
}
