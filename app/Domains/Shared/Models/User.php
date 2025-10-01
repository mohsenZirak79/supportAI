<?php

namespace App\Domains\Shared\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

// بعداً پکیج نصب

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    // HasRoles برای permissions

    public $incrementing = false;
    protected $keyType = 'string';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'family', 'national_id', 'postal_code', 'birth_date', 'birth_date', 'address'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function assignedConversations()
    {
        return $this->hasMany(Conversation::class, 'support_agent_id');
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
