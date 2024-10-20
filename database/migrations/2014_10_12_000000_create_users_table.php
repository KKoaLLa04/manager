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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('fullname');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('code')->nullable();
            $table->integer('access_type')->comment('1: QT; 2: GV; 3: PH');
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->integer('status')->default(1)->comment('1: Active; 0: Un Active');
            $table->integer('is_deleted')->default(0)->comment('0: NOT DELETE, 1: DELETED');
            $table->integer('created_user_id')->nullable();
            $table->integer('modified_user_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
