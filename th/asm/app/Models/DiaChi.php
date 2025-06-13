<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaChi extends Model
{
    protected $table = 'dia_chi'; // Đúng tên bảng trong database
    public $timestamps = false; // Nếu bảng không có created_at, updated_at
}
