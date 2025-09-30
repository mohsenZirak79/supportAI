<?php

namespace App\Domains\Shared\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ticket extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'parent_id', 'root_id', 'title', 'message',
        'sender_type', 'sender_id', 'department', 'status', 'priority'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('ticket-attachments')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf'])
            ->useDisk('public');
    }

    // رابطه به تیکت اصلی
    public function root()
    {
        return $this->belongsTo(Ticket::class, 'root_id');
    }

    // رابطه به پاسخ‌ها
    public function replies()
    {
        return $this->hasMany(Ticket::class, 'parent_id');
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid(); // تولید UUID خودکار هنگام ایجاد
            }
        });
    }
}
