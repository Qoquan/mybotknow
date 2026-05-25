<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModelUsage extends Model
{
    protected $fillable = [
        'user_id',
        'model',
        'total_messages',
        'total_tokens',
        'usage_date',
    ];

    protected $casts = [
        'total_messages' => 'integer',
        'total_tokens'   => 'integer',
        'usage_date'     => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
