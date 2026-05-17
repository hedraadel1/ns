<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Agent extends BaseModel
{
    // Agent Types
    public const TYPE_REFLECTION = 'reflection';
    public const TYPE_TEAM = 'team';
    public const TYPE_AUTONOMOUS = 'autonomous';
    public const TYPE_SPECIALIZED = 'specialized';
    public const TYPE_SUPERVISOR = 'supervisor';

    // Agent Statuses
    public const STATUS_IDLE = 'idle';
    public const STATUS_RUNNING = 'running';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ERROR = 'error';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'name',
        'key',
        'description',
        'type',
        'provider',
        'status',
        'settings',
        'metadata',
        'is_active',
        'last_executed_at',
        'execution_count',
        'success_count',
        'error_count',
    ];

    protected $casts = [
        'settings' => 'json',
        'metadata' => 'json',
        'is_active' => 'boolean',
        'last_executed_at' => 'datetime',
        'execution_count' => 'integer',
        'success_count' => 'integer',
        'error_count' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_IDLE,
        'is_active' => true,
        'execution_count' => 0,
        'success_count' => 0,
        'error_count' => 0,
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

    public function activeTools()
    {
        return $this->tools()->where('is_active', true)->get();
    }

    public function activeSkills()
    {
        return $this->skills()->where('is_active', true)->get();
    }

    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function isIdle(): bool
    {
        return $this->status === self::STATUS_IDLE;
    }

    public function hasError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    public function getSuccessRate(): float
    {
        if ($this->execution_count === 0) {
            return 0.0;
        }
        return round(($this->success_count / $this->execution_count) * 100, 2);
    }

    public function incrementExecution(): void
    {
        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);
    }

    public function recordSuccess(): void
    {
        $this->increment('success_count');
        $this->update(['status' => self::STATUS_IDLE]);
    }

    public function recordError(): void
    {
        $this->increment('error_count');
        $this->update(['status' => self::STATUS_ERROR]);
    }

    public function setRunning(): void
    {
        $this->update(['status' => self::STATUS_RUNNING]);
    }

    public function setIdle(): void
    {
        $this->update(['status' => self::STATUS_IDLE]);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithToolsAndSkills($query)
    {
        return $query->with(['tools', 'skills']);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_REFLECTION => 'Reflection Agent',
            self::TYPE_TEAM => 'Team Agent',
            self::TYPE_AUTONOMOUS => 'Autonomous Agent',
            self::TYPE_SPECIALIZED => 'Specialized Agent',
            self::TYPE_SUPERVISOR => 'Supervisor Agent',
            default => 'Unknown Agent',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_IDLE => 'Idle',
            self::STATUS_RUNNING => 'Running',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_ERROR => 'Error',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Unknown',
        };
    }
}
