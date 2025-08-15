<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::table('danh_muc_san_pham', function (Blueprint $table) {
    //         $table->unsignedBigInteger('parent_id')->nullable()->after('category_id');
    //     });
    //     if (!Schema::hasTable('personal_access_tokens')) {
    //         Schema::create('personal_access_tokens', function (Blueprint $table) {
    //             // ...existing code...
    //         });
    //     }
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::table('danh_muc_san_pham', function (Blueprint $table) {
    //         $table->dropColumn('parent_id');
    //     });
    // }
};
