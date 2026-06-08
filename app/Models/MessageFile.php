<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageFile extends Model
{
    protected $fillable = [
        'message_id',
        'filename',
        'path',
        'mime_type',
        'type',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
