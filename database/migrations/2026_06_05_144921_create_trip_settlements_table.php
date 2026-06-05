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
        // --------------------------------------------------------
        // Migration: Thêm bảng Quyết toán chuyến đi (trip_settlements)
        // Ngày: 2026-06-05
        // Ghi chú: Quyết toán theo từng lệnh điều vận, lưu số liệu tĩnh
        // --------------------------------------------------------

        Schema::create('trip_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_code', 50)->unique()->comment('Mã quyết toán');
            $table->foreignId('dispatch_order_id')->unique()->constrained('dispatch_orders')->comment('Mỗi lệnh điều vận chỉ được quyết toán một lần');
            
            // Số liệu tài chính (lưu tĩnh tại thời điểm tạo/nộp quyết toán)
            $table->decimal('total_expense', 15, 2)->default(0)->comment('Tổng chi phí phát sinh trong lệnh');
            $table->decimal('total_cash_advance', 15, 2)->default(0)->comment('Tổng tạm ứng trong lệnh');
            $table->decimal('balance', 15, 2)->default(0)->comment('Số dư = tạm ứng - chi phí (dương: hoàn lại; âm: chi bổ sung)');
            
            // Trạng thái & luồng duyệt
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft')->comment('draft: nháp | submitted: đã nộp | approved: đã duyệt | rejected: từ chối');
            $table->text('rejection_reason')->nullable()->comment('Lý do từ chối (nếu bị rejected)');
            
            // Người đề nghị
            $table->foreignId('requested_by')->constrained('users')->comment('Người đề nghị quyết toán');
            $table->timestamp('requested_at')->nullable()->comment('Ngày giờ đề nghị');
            
            // Người duyệt
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->comment('Người duyệt quyết toán');
            $table->timestamp('approved_at')->nullable()->comment('Ngày giờ duyệt');
            
            // Ghi chú & audit
            $table->text('note')->nullable()->comment('Ghi chú');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_settlements');
    }
};
