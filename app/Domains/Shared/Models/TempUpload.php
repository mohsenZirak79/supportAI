<?php

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;

class TempUpload extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'temp_uploads'; // اگر جدول خاص داری
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['user_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function registerMediaCollections(): void
    {
        // پارکینگ پیش‌فرض
        $this->addMediaCollection('uploads')->useDisk('public');

        // در صورت نیاز برای آپلود مستقیم بدون attach:
        $this->addMediaCollection('message_voices')->useDisk('public');
        $this->addMediaCollection('message_files')->useDisk('public');
        $this->addMediaCollection('avatars')->useDisk('public'); // مثال: کاربر
    }
}
