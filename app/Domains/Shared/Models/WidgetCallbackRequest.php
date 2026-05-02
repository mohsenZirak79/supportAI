<?php

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WidgetCallbackRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_CANCELLED = 'cancelled';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'conversation_id',
        'trigger_message_id',
        'status',
        'handled_by',
        'contacted_at',
        'admin_note',
        'source',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function triggerMessage()
    {
        return $this->belongsTo(Message::class, 'trigger_message_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'در انتظار تماس',
            self::STATUS_CONTACTED => 'تماس گرفته شده',
            self::STATUS_DECLINED => 'رد شده',
            self::STATUS_CANCELLED => 'لغو توسط کاربر',
        ];
    }
}
