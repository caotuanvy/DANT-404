<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'don_hang';
    const CREATED_AT = 'ngay_dat';
    const UPDATED_AT = 'ngay_sua';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nguoi_dung_id',
        'phuong_thuc_thanh_toan_id',
        'id_giam_gia',      // Sửa lỗi: Thay đổi từ 'id_giam_gia' thành 'ma_giam_gia'
        'dia_chi_id',
        'ten_nguoi_nhan',    // Thêm trường này vào fillable
        'sdt_nguoi_nhan',    // Thêm trường này vào fillable
        'dia_chi_giao_hang', // Thêm trường này vào fillable
        'phi_van_chuyen',    // Thêm trường này vào fillable
        'tong_tien',         // Thêm trường này vào fillable
        'is_paid',
        'trang_thai_don_hang',
        'ghi_chu',
        'ngay_dat',
        'ngay_tao',
        'ngay_sua',
        'so_tien_giam',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }

    public function diachi()
    {
        return $this->belongsTo(DiaChi::class, 'dia_chi_id', 'id_dia_chi');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'phuong_thuc_thanh_toan_id', 'phuong_thuc_thanh_toan_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'don_hang_id', 'id');
    }
}
