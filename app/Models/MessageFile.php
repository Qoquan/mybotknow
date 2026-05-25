<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageFile extends Model
{
    protected $fillable = [
        'message_id',
        'filename',
        'path',
        'mime_type',
        'type',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
