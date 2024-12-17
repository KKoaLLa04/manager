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
        Schema::table('student_class_history', function (Blueprint $table) {
            $table->string('note')->nullable()->after('status'); // Thêm cột 'note', có thể null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_class_history', function (Blueprint $table) {
            $table->dropColumn('note'); // Xóa cột 'note' khi rollback
        });
    }
};
