<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'danh_muc_san_pham';
    protected $primaryKey = 'category_id';

    public $timestamps = false;

    protected $fillable = [
        'ten_danh_muc',
        'mo_ta',
        'slug',
        'ngay_tao',
        'ngay_sua',
    ];

    public function products()
    {
        return $this->hasMany(SanPham::class, 'ten_danh_muc_id', 'category_id');

    }

    // public function children()
    // {
    //     return $this->hasMany(Category::class, 'parent_id')....;
    // }
}
