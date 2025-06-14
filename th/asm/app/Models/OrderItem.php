<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'chi_tiet_don_hang'; // Đúng tên bảng trong database
    public $timestamps = false; // Nếu bảng không có created_at, updated_at
    public function bien_the()
        {
            return $this->belongsTo(\App\Models\SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id');
        }
}
