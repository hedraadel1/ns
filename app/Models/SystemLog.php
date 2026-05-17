<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends BaseModel
{
    protected $table = 'logs';

    protected $fillable = [
        'level',
        'channel',
        'message',
        'context',
        'type',
        'user_id',
        'related_id',
        'related_type',
    ];

    protected $casts = [
        'context' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
