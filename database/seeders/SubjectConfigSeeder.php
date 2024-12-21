<?php

namespace Database\Seeders;

use App\Domain\Subject\Models\Subject;
use App\Models\SubjectTimetableConfig;
use Illuminate\Database\Seeder;

class SubjectConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataInsert = [];
        $subjects = Subject::query()->get();
        foreach ($subjects as $subject) {
            $dataInsert[] = [
                'subject_id' => $subject->id,
                'quantity' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        SubjectTimetableConfig::query()->insert(
        $dataInsert
        );
    }
}
