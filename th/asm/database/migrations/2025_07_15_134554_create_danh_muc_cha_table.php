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
        Schema::create('danh_muc_cha', function (Blueprint $table) {
            $table->id('id');
            $table->string('ten_danh_muc');
            $table->string('mo_ta')->nullable();
            $table->timestamps();
        });

        // Thêm cột liên kết vào bảng danh_muc_san_pham
        Schema::table('danh_muc_san_pham', function (Blueprint $table) {
            $table->unsignedBigInteger('danh_muc_cha_id')->nullable()->after('category_id');
            $table->foreign('danh_muc_cha_id')->references('id')->on('danh_muc_cha')->onDelete('set null');
        });

        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('tokenable_type');
                $table->unsignedBigInteger('tokenable_id');
                $table->string('token', 80)->unique();
                $table->string('abilities')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('danh_muc_san_pham', function (Blueprint $table) {
            $table->dropForeign(['danh_muc_cha_id']);
            $table->dropColumn('danh_muc_cha_id');
        });
        Schema::dropIfExists('danh_muc_cha');
        Schema::dropIfExists('personal_access_tokens');
    }
};
