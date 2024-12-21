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
        Schema::create('timetable', function (Blueprint $table) {
            $table->id();
            $table->integer('day')->comment("0: CN; 1: T2, 2: T3, 3: T4, 4: T5, 5: T6, 6: T7");
            $table->integer('time')->comment("1: buổi sáng, 2 buổi chiều");
            $table->time('from_time');
            $table->time('to_time');
            $table->integer('period');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable');
    }
};
