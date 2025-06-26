<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhuongThucThanhToan extends Model
{
    protected $table = 'phuong_thuc_thanh_toan';

    // Khai báo khóa chính không phải là 'id'
    protected $primaryKey = 'phuong_thuc_thanh_toan_id';

    public $incrementing = true; // nếu là INT AUTO_INCREMENT
    public $timestamps = false; // nếu bảng không có created_at / updated_at
}
