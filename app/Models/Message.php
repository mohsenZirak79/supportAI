<?php

namespace App\Models;

use App\Domains\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

// برای encryption

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', 'sender_id', 'type', 'content', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Accessor برای decrypt content
    protected function getContentAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    // Mutator برای encrypt
    protected function setContentAttribute($value)
    {
        $this->attributes['content'] = $value ? Crypt::encryptString($value) : null;
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
