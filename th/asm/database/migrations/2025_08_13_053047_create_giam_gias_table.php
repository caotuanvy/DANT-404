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
        Schema::create('giam_gia', function (Blueprint $table) {
            $table->id('giam_gia_id');
            $table->string('ma_giam_gia')->unique();
            $table->string('ten_chuong_trinh')->nullable();
            $table->enum('loai_giam_gia', ['percentage', 'fixed_amount', 'free_ship']);
            $table->decimal('gia_tri', 15, 2);
            $table->decimal('gia_tri_don_hang_toi_thieu', 15, 2)->nullable();
            $table->decimal('gia_tri_giam_toi_da', 15, 2)->nullable();
            $table->integer('so_luong');
            $table->integer('so_luong_da_dung')->default(0);
            $table->timestamp('ngay_bat_dau')->nullable(); // Thêm ->nullable()
            $table->timestamp('ngay_ket_thuc')->nullable(); // Thêm ->nullable()
            $table->boolean('trang_thai')->default(true);
            $table->text('ap_dung_cho')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giam_gia');
    }
};
