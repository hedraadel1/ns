<?php

namespace App\Models;

class AIModel extends BaseModel
{
    protected $fillable = [
        'name',
        'provider',
        'external_id',
        'description',
        'capabilities',
        'metadata',
        'status',
    ];

    protected $casts = [
        'capabilities' => 'json',
        'metadata' => 'json',
    ];
}
