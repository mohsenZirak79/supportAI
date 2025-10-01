<?php
// app/Domains/Shared/Models/Message.php
namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Message extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['conversation_id', 'sender_id', 'sender_type', 'type', 'content', 'metadata',];
    protected $casts = [
        'attachments' => 'array',
        'created_at' => 'datetime'
    ];

    // محتوا رو encrypted ذخیره کن
    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value)
        );
    }
    public function sender()
    {
        return $this->belongsTo(\App\Domains\Shared\Models\User::class, 'sender_id');
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
