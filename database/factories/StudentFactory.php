<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'fullname' => $this->faker->name,
            'address' => $this->faker->address,
            'student_code' => strtoupper($this->faker->bothify('STU-#####')),  // Mã học sinh với tiền tố 'STU'
            'dob' => $this->faker->date('Y-m-d', '2005-12-31'),  // Ngày sinh ngẫu nhiên trước năm 2005
            'gender' => $this->faker->randomElement([1,2]),  // Giới tính ngẫu nhiên
            'status' => $this->faker->randomElement([0, 1]),  // 0: Unactive, 1: Active
            'is_deleted' => 0,  // Luôn để là 0 (Active)
            'created_user_id' => $this->faker->randomDigitNotNull,  // Id người tạo
            'modified_user_id' => $this->faker->randomDigitNotNull,  // Id người chỉnh sửa
        ];
    }
}
