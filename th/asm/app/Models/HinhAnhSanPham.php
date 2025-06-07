<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HinhAnhSanPham extends Model
{
    use HasFactory;

    protected $table = 'hinh_anh_san_pham';
    protected $primaryKey = 'hinh_anh_id';

    protected $fillable = [
        'san_pham_id',
        'duongdan',
        'mo_ta',
        'ngay_tao'
    ];

    public $timestamps = false;

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id', 'san_pham_id');
    }
}

