<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $table = 'doitac';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'Ten_doi_tac',
        'Thuong_hieu_doi_tac',
        'trang_thai'
    ];

    public function products()
    {
        return $this->hasMany(SanPham::class, 'partner_id');
    }
}
