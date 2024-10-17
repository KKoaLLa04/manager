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
        Schema::table('students', function (Blueprint $table) {
            // Đổi kiểu dữ liệu của cột 'dob' thành timestamp
            $table->timestamp('dob')->nullable()->change();
    
            // Thêm cột 'phone' nếu chưa có
            if (!Schema::hasColumn('students', 'phone')) {
                $table->string('phone')->nullable();
            }
        });
    }
    
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Khôi phục lại kiểu dữ liệu cũ nếu cần (giả sử trước đây là date)
            $table->date('dob')->nullable()->change();
    
            // Xóa cột 'phone' nếu cần revert
            if (Schema::hasColumn('students', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
    
};
