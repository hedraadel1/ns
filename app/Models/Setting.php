<?php

namespace App\Models;

class Setting extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'metadata',
        'is_public',
    ];

    protected $casts = [
        'metadata' => 'json',
        'is_public' => 'boolean',
    ];
}
