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
        Schema::create('school_year', function (Blueprint $table) {
            $table->id();
            $table->string('name', 225);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('created_user_id');
            $table->integer('modified_user_id')->nullable();
            $table->integer('status')->default(1)->comment('1: Chưa diễn ra; 2: Đang diễn ra; 3: Đã kết thúc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_year');
    }
};
