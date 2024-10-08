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
            $table->integer('status')->default(1)->comment('0: Chưa diễn ra; 1: Đang diễn ra; 2: Đã kết thúc');
            $table->date('start_year');
            $table->date('end_year');
            $table->integer('is_deleted')->default(0)->comment('0: Deleted; 1: Active');
            $table->integer('created_user_id');
            $table->integer('modified_user_id')->nullable();
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
