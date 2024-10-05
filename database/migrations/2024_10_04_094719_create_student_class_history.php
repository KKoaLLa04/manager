<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_class_history', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->nullable();
            $table->integer('class_id')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('status')->default(1)->comment('0: Nghỉ học; 1: Đang học ; 2: Chưa vào lớp ; 3: Bảo lưu');
            $table->integer('is_deleted')->default(1)->comment('0: Deleted; 1: Active');
            $table->integer('created_user_id')->nullable();
            $table->integer('modified_user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_student_class_history');
    }
};
