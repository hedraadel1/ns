<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AIProvider extends BaseModel
{
    protected $table = 'ai_providers';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'base_url',
        'models_fetch_endpoint',
        'generate_endpoint',
        'auth_header_format',
        'payload_format',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function models(): HasMany
    {
        return $this->hasMany(AIModel::class, 'provider_id');
    }
}
