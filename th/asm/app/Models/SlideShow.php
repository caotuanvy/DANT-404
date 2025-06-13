<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlideShow extends Model
{
    protected $table = 'slide_show';
    protected $primaryKey = 'Slide_id';
    public $timestamps = false;

    protected $fillable = ['Ten_slide', 'Hinh_anh'];
}
