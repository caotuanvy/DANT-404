<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'don_hang'; // Tên bảng tương ứng trong DB
    protected $primaryKey = 'id'; // Khóa chính
    public $timestamps = false; // Tắt tự động timestamps nếu không dùng `created_at`/`updated_at`

    protected $fillable = [
        'nguoi_dung_id',
        'phuong_thuc_thanh_toan_id',
        'id_giam_gia',
        'dia_chi_id',
        'trang_thai',
        'ghi_chu',
        'phi_van_chuyen',
        'ngay_tao',
        'ngay_sua',
    ];

    // Quan hệ với người dùng
    public function user()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }

//     // Quan hệ với địa chỉ
//     public function address()
//     {
//         return $this->belongsTo(Address::class, 'dia_chi_id', 'id_dia_chi');
//     }

//     // Quan hệ với phương thức thanh toán
//     public function paymentMethod()
//     {
//         return $this->belongsTo(PaymentMethod::class, 'phuong_thuc_thanh_toan_id', 'phuong_thuc_thanh_toan_id');
//     }

//     // Quan hệ với mã giảm giá
//     public function discount()
//     {
//         return $this->belongsTo(Discount::class, 'id_giam_gia', 'giam_gia_id');
//     }

//     // Quan hệ với giỏ hàng (nếu có bảng chi tiết đơn hàng)
//     public function orderItems()
//     {
//         return $this->hasMany(OrderItem::class, 'don_hang_id', 'id');
//     }
}
