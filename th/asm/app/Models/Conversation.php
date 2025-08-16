<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nguoi_dung_id',
        'admin_id',
    ];

    /**
     * Mối quan hệ với người dùng (user).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }

    /**
     * Mối quan hệ với admin.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'nguoi_dung_id');
    }

    /**
     * Mối quan hệ với tin nhắn.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }

    /**
     * Scope để thêm trường ảo `last_message_date` vào kết quả.
     */
    public function scopeWithLastMessageDate(Builder $query)
    {
        return $query->addSelect([
            'last_message_date' => Message::select('created_at')
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->take(1)
        ]);
    }
}