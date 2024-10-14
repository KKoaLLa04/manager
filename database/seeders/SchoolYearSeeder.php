<?php

namespace Database\Seeders;

use App\Domain\SchoolYear\Models\SchoolYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $item = new SchoolYear();

        $item->name = "2027 - 2028";
        $item->status = 1;
        $item->start_date = "2027-06-16";
        $item->end_date = "2028-06-16";
        $item->created_user_id = 1;

        $item->save();

    }
}
