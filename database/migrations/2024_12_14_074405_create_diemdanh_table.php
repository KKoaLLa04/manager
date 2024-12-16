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
        Schema::create('diemdanh', function (Blueprint $table) {
            $table->id();
            $table->integer('tiet');
            $table->integer('thu');
            $table->integer('mon')->nullable(); // Cho phép giá trị NULL
            $table->integer('class_id');
            $table->integer('buoi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diemdanh');
    }
};
