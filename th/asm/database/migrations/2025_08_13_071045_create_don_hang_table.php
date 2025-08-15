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
            Schema::create('don_hang', function (Blueprint $table) {
                $table->id(); // Khóa chính cho bảng don_hang

                // Khóa ngoại đến bảng nguoi_dung
                $table->foreignId('nguoi_dung_id')->nullable()->constrained('nguoi_dung', 'nguoi_dung_id');

                // Khóa ngoại đến bảng giam_gia
                // Laravel sẽ tự tạo cột id_giam_gia kiểu UNSIGNED BIGINT để khớp với giam_gia_id
                $table->foreignId('id_giam_gia')->nullable()->constrained('giam_gia', 'giam_gia_id');

                // Các cột khác của bạn
                $table->integer('phuong_thuc_thanh_toan_id')->nullable();
                $table->integer('dia_chi_id')->nullable();
                $table->integer('trang_thai_don_hang')->nullable();
                $table->text('ghi_chu')->nullable();
                $table->decimal('phi_van_chuyen', 15, 2)->nullable();
                $table->integer('tong_tien')->nullable();
                $table->boolean('is_paid')->default(false);

                $table->timestamps(); // Tự động tạo created_at và updated_at
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('don_hang');
    }
};
