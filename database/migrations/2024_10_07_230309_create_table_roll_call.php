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
        Schema::create('roll_call', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('note')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->integer('status')->default(1);
            $table->integer('is_deleted')->default(0)->comment('0: NOT DELETE, 1: DELETED');
            $table->unsignedBigInteger('created_user_id')->nullable();
            $table->unsignedBigInteger('modified_user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roll_call');
    }
};
