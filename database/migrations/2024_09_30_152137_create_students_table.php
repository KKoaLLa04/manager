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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('address')->nullable();
            $table->string('student_code')->nullable();
            $table->date('dob');
            $table->integer('gender')->default(1)->comment('1: Nam; 2: Nữ;');
            $table->integer('status')->default(1)->comment('1: Active; 0: Un Active');
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
        Schema::dropIfExists('students');
    }
};
