<?php

namespace Database\Seeders;

use App\Domain\Subject\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $arrNameSubject = [
            'Toán',
            'Ngữ văn',
            'Ngoại ngữ',
            'Giáo dục công dân',
            'Lịch sử',
            'Địa lý',
            'Vật lý',
            'Hóa học',
            'Sinh học',
            'Công nghệ',
            'Tin học',
            'Giáo dục thể chất',
            'Mỹ thuật',
            'Âm nhạc',
        ];

        foreach ($arrNameSubject as $key => $sub) {
            $item = new Subject();

            $item->name = $sub;

            $item->save();
        }


    }
}
