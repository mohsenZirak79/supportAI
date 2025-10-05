<?php

namespace App\Domains\Shared\Models;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Morilog\Jalali\Jalalian;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'family', 'national_id',
        'postal_code', 'phone', 'birth_date', 'address'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['created_at_jalali'];

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

    public function getCreatedAtJalaliAttribute()
    {
        return $this->created_at
            ? Jalalian::forge($this->created_at)->format('Y/m/d')
            : null;
    }

    // ================= JWTSubject =================
    public function getJWTIdentifier()
    {
        return $this->getKey(); // معمولاً id کاربر
    }

    public function getJWTCustomClaims()
    {
        return []; // اگر claim سفارشی میخوای اضافه کن
    }
}
