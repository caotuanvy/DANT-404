<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'phuong_thuc_thanh_toan';
    protected $primaryKey = 'phuong_thuc_thanh_toan_id'; // đúng là "id" nếu DB bạn dùng id
    public $timestamps = false;

    protected $fillable = [
        'ten_pttt',
        "trang_thai",
    ];
    public function orders()
    {
        return $this->hasMany(Order::class, 'phuong_thuc_thanh_toan_id', 'phuong_thuc_thanh_toan_id');
    }
}
