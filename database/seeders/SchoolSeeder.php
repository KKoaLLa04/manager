<?php

namespace Database\Seeders;

use App\Domain\School\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $school = new School();
        $school->code = "PH38040";
        $school->name = "THCS TechSchool";
        $school->avatar = "hfdfhnsjd.jpg";
        $school->address = "Hà Nội";
        $school->email = "huyp3172004@gmail.com";
        $school->logo = "hfdfhnsjd.jpg";
        $school->telephone = "0912323981";
        $school->modified_user_id = 1;
     
        $school->save();
    }
}
