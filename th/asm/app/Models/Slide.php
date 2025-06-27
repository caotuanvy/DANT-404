<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $table = 'slide';
    protected $primaryKey = 'slide_id';
    public $timestamps = false;

    protected $fillable = ['ten_slide', 'hien_thi'];

    // Quan hệ 1 slide có nhiều hình ảnh
    public function hinhAnh()
    {
        return $this->hasMany(SlideHinhAnh::class, 'slide_id', 'slide_id');
    }
}
