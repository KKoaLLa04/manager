<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Domain\AcademicYear\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academy = new AcademicYear();
        $academy->name = "KH18";
        $academy->code = "KH123";
        $academy->status = 0;
        $academy->start_year = "2024-09-29 00:00:00";
        $academy->end_year = "2028-09-29 00:00:00";
        $academy->created_user_id = 1;
        $academy->modified_user_id = 1;
        $academy->is_deleted = 0;
        $academy->save();
    }
}
