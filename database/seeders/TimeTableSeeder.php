<?php

namespace Database\Seeders;

use App\Models\Timetable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use function Symfony\Component\String\s;

class TimeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $day = 1;
        $dataInsert = [];
        for ($j = 1; $j <= 2; $j++) {
            for ($i = 1; $i <= 6; $i++) {
                for ($k = 1; $k <= 5; $k++) {
                    if ($k == 1 && $j == 1) {
                        $from_time = "07:00";
                        $to_time = "07:45";
                    }elseif ($k == 1 && $j == 2) {
                        $from_time = "02:00";
                        $to_time = "02:45";
                    }elseif ($k == 2 && $j == 1) {
                        $from_time = "07:50";
                        $to_time = "08:35";
                    }elseif ($k == 2 && $j == 2) {
                        $from_time = "02:50";
                        $to_time = "03:35";
                    }elseif ($k == 3 && $j == 1) {
                        $from_time = "08:45";
                        $to_time = "09:30";
                    }elseif ($k == 3 && $j == 2) {
                        $from_time = "03:45";
                        $to_time = "04:30";
                    }elseif ($k == 4 && $j == 1) {
                        $from_time = "09:35";
                        $to_time = "10:20";
                    }elseif ($k == 4 && $j == 2) {
                        $from_time = "04:35";
                        $to_time = "05:20";
                    }elseif ($k == 5 && $j == 1) {
                        $from_time = "10:25";
                        $to_time = "11:00";
                    }elseif ($k == 6 && $j == 2) {
                        $from_time = "05:25";
                        $to_time = "06:10";
                    }
                    $dataInsert[] = [
                        'day' => $i,
                        'time' => $j,
                        'period' => $k,
                        'to_time' => $to_time,
                        'from_time' => $from_time,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        TimeTable::query()->insert(
        $dataInsert
        );
    }
}
