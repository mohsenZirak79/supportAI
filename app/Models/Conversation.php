<?php

namespace App\Models;

use App\Domains\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'support_agent_id', 'status', 'user_profile',
    ];

    protected $casts = [
        'user_profile' => 'array',  // JSON to array
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supportAgent()
    {
        return $this->belongsTo(User::class, 'support_agent_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
