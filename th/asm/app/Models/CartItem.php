<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;
    protected $table = 'gio_hang_chi_tiet'; // Đảm bảo đúng tên bảng
    protected $primaryKey = 'gio_hang_chi_tiet_id'; // Đảm bảo đúng khóa chính
    protected $fillable = [
        'gio_hang_id',
        'san_pham_bien_the_id', // <-- Đảm bảo cột này tồn tại và được điền
        'so_luong',
        'don_gia',
        'ngay_tao'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'gio_hang_id', 'gio_hang_id');
    }

    public function sanPhamBienThe()
    {
        return $this->belongsTo(SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id'); // <-- Cực kỳ quan trọng
    }
}