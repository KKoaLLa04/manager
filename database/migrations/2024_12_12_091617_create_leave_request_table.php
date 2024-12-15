<?php

use App\Common\Enums\LeaveRequestEnum;
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
        Schema::create('leave_request', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('title');
            $table->text('note')->nullable();
            $table->date('leave_date');
            $table->date('return_date');
            $table->time('time');
            $table->tinyInteger('status')->default(LeaveRequestEnum::AWAITING->value); 
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('parent_user_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->text('refuse_note')->nullable();
            $table->integer('is_deleted')->default(0)->comment('0: NOT DELETE, 1: DELETED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request');
    }
};
