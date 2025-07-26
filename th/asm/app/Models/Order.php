<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'don_hang';
    protected $primaryKey = 'id'; // Khóa chính là 'id'
    public $timestamps = false; // Tắt timestamps Laravel mặc định

    protected $fillable = [
        'nguoi_dung_id',
        'phuong_thuc_thanh_toan_id',
        'id_giam_gia',          // Giữ nguyên theo Model, nhưng cần thống nhất với Controller
        'dia_chi_id',           // Địa chỉ giao hàng
        'trang_thai_don_hang',  // <-- Đã thống nhất với Controller dùng 'trang_thai_don_hang'
        'ghi_chu',
        'phi_van_chuyen',
        'tong_tien',            // <-- Đã thêm vào fillable (quan trọng!)
        'is_paid',              // <-- Đã thêm vào fillable (quan trọng!)
        'ngay_dat',             // <-- Đã thêm vào fillable (quan trọng!)
        'ngay_tao',             // Giữ lại theo các file cũ nếu có trong DB
        'ngay_sua',             // Giữ lại theo các file cũ nếu có trong DB
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }

    // Quan hệ với địa chỉ (Giả định bạn vẫn dùng DiaChi Model, nếu gộp thành Address thì đổi ở đây)
    public function diachi()
    {
        return $this->belongsTo(DiaChi::class, 'dia_chi_id', 'id_dia_chi');
    }

    // Quan hệ với phương thức thanh toán
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'phuong_thuc_thanh_toan_id', 'phuong_thuc_thanh_toan_id');
    }

    // Quan hệ với các mục trong đơn hàng
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'don_hang_id', 'id');
    }

    // Quan hệ với mã giảm giá (nếu bạn bỏ comment và sử dụng)
    // public function discount()
    // {
    //     return $this->belongsTo(Discount::class, 'id_giam_gia', 'giam_gia_id');
    // }
}
