<?php

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Events\Retrieved; // Import for event

// For PII encryption

class Referral extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [  // Match migration – no org_id/metadata for now
        'conversation_id', 'trigger_message_id', 'user_id', 'assigned_role',
        'assigned_agent_id', 'description', 'status', 'agent_response', 'response_visibility'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations
    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function triggerMessage() { return $this->belongsTo(Message::class, 'trigger_message_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function assignedAgent() { return $this->belongsTo(User::class, 'assigned_agent_id'); }

    // Encryption: Use events instead of boot() for clarity (Laravel 11+ best practice)
    protected static function booted() {
        parent::booted();
        // Saving: encrypt before save
        static::saving(function ($model) {
            if ($model->description) $model->description = Crypt::encrypt($model->description);
            if ($model->agent_response) $model->agent_response = Crypt::encrypt($model->agent_response);
        });
        // Retrieved: decrypt after load (not retrieving – that's for query)
        static::retrieved(function ($model) {
            if ($model->description) $model->description = Crypt::decrypt($model->description);
            if ($model->agent_response) $model->agent_response = Crypt::decrypt($model->agent_response);
        });
    }

    // Scope (if add org_id later)
    public function scopeForOrg($query, $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    // Accessor
    public function getDecryptedResponseAttribute() {
        if ($this->response_visibility === 'internal' && auth()->id() !== $this->user_id) {  // Fix: !== not ===
            return '[داخلی - قابل مشاهده نیست]';
        }
        return $this->agent_response;
    }
}
