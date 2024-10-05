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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->bigInteger('main_teacher')->nullable();
            $table->bigInteger('school_year_id')->nullable();
            $table->bigInteger('academic_year_id')->nullable();
            $table->integer('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->bigInteger('grade_id')->nullable();
            $table->integer('is_deleted')->default(1)->comment('1: Active, 0: Deleted');
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
        Schema::dropIfExists('classes');
    }
};
