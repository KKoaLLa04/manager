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
        Schema::create('exam_period_scope_class', function (Blueprint $table) {
            $table->unsignedBigInteger('exam_period_id');
            $table->unsignedBigInteger('class_id');
            $table->integer('is_deleted')->default(0)->comment("1: deleted, 0: not deleted");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_period_scope_class');
    }
};
