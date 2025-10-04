<?php

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

// For PII encryption

class Referral extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id', 'trigger_message_id', 'user_id', 'assigned_role',
        'assigned_agent_id', 'description', 'status', 'agent_response',
        'response_visibility', 'org_id', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime', // ISO-8601
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations (no ticket link!)
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function triggerMessage()
    {
        return $this->belongsTo(Message::class, 'trigger_message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    // Encryption for PII fields (specs: field-level)
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->description) $model->description = Crypt::encrypt($model->description);
            if ($model->agent_response) $model->agent_response = Crypt::encrypt($model->agent_response);
        });
        static::retrieving(function ($model) {
            if ($model->description) $model->description = Crypt::decrypt($model->description);
            if ($model->agent_response) $model->agent_response = Crypt::decrypt($model->agent_response);
        });
    }

    // Scope for tenancy (enforce in queries)
    public function scopeForOrg($query, $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    // Accessor for decrypted response (only if public or owner)
    public function getDecryptedResponseAttribute()
    {
        if ($this->response_visibility === 'internal' && !auth()->id() === $this->user_id) {
            return '[داخلی - قابل مشاهده نیست]';
        }
        return $this->agent_response;
    }
}
