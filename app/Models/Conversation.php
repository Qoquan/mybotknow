<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'title',
        'model',
        'total_tokens',
    ];

    protected $casts = [
        'total_tokens' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
    public function members(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'conversation_members')
        ->withPivot('role', 'joined_at')
        ->withTimestamps();
}

    public function conversationMembers(): HasMany
    {
        return $this->hasMany(ConversationMember::class);
    }

    public function isShared(): bool
    {
        return $this->conversationMembers()->count() > 0;
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
