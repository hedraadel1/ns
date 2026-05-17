<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends BaseModel
{
    protected $fillable = [
        'name',
        'key',
        'description',
        'provider',
        'status',
        'settings',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'json',
        'metadata' => 'json',
        'is_active' => 'boolean',
    ];

    public function tools(): HasMany
    {
        return $this->hasMany(AgentTool::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(AgentSkill::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(AgentTask::class);
    }
}
