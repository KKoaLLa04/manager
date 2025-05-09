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
        Schema::table('exam_period', function (Blueprint $table) {
            $table->string('type')->default(1); // Thêm cột 'note', có thể null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_period', function (Blueprint $table) {
            $table->dropColumn('type'); // Xóa cột 'note' khi rollback
        });
    }
};
