<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Grade::create(
            [
                "name" => "Khối 6"
            ],
            [
                "name" => "Khối 7"
            ],
            [
                "name" => "Khối 8"
            ],
            [
                "name" => "Khối 9"
            ]
        );
    }
}
