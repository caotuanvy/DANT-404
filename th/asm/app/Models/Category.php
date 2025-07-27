<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes; // Bỏ comment nếu bạn có sử dụng SoftDeletes

class Category extends Model
{
    use HasFactory;
    // use SoftDeletes; // Bỏ comment nếu bạn có sử dụng SoftDeletes

    protected $table = 'danh_muc_san_pham'; // Đảm bảo tên bảng đúng
    protected $primaryKey = 'category_id'; // Đảm bảo khóa chính đúng

    public $timestamps = false; // Giữ nguyên nếu bạn không muốn Laravel tự động quản lý created_at/updated_at

    protected $fillable = [
        'ten_danh_muc',
        'mo_ta',
        'slug',
        'ngay_tao',
        'ngay_sua',
        'trang_thai',
        'danh_muc_cha_id', // RẤT QUAN TRỌNG: Thêm trường này vào fillable
    ];

    // Mối quan hệ với các sản phẩm thuộc danh mục này
    public function products()
    {
        return $this->hasMany(SanPham::class, 'ten_danh_muc_id', 'category_id');
    }

    // Mối quan hệ với DANH MỤC CHA (từ bảng danh_muc_cha)
    public function danhMucCha()
    {
        // Category (danh_muc_san_pham) thuộc về một DanhMucCha
        // 'danh_muc_cha_id' là khóa ngoại trong bảng 'danh_muc_san_pham'
        // 'id' là khóa chính trong bảng 'danh_muc_cha'
        return $this->belongsTo(DanhMucCha::class, 'danh_muc_cha_id', 'id');
    }

    // Xóa bỏ hoặc comment các mối quan hệ này nếu bảng `danh_muc_san_pham` không có cột `parent_id`
    // public function parent()
    // {
    //     return $this->belongsTo(Category::class, 'parent_id');
    // }

    // public function children()
    // {
    //     return $this->hasMany(Category::class, 'parent_id');
    // }
}