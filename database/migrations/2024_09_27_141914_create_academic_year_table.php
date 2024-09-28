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
        Schema::create('academic_year', function (Blueprint $table) {
            $table->id();
            $table->string('name',255);
            $table->string('code',255)->unique();
            $table->integer('status')->default(1)->comment('1: Chưa diễn ra; 2: Đang diễn ra; 3: Đã kết thúc');
            $table->dateTime('start_year');
            $table->dateTime('end_year');
            $table->integer('is_deleted')->default(1)->comment('0: Deleted; 1: Active');
            $table->integer('created_user_id');
            $table->integer('modified_user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_year');
    }
};
