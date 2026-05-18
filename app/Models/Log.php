<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Log Model
 *
 * Stores application log entries with categorization, severity,
 * and contextual metadata for search and alerting.
 *
 * @property int $id
 * @property string $level
 * @property string $category
 * @property string $message
 * @property array<string, mixed> $context
 * @property string $source
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Log extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'level',
        'category',
        'message',
        'context',
        'source',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Log level constants (PSR-3 compatible).
     */
    public const LEVEL_DEBUG = 'debug';
    public const LEVEL_INFO = 'info';
    public const LEVEL_NOTICE = 'notice';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_CRITICAL = 'critical';
    public const LEVEL_ALERT = 'alert';
    public const LEVEL_EMERGENCY = 'emergency';

    /**
     * Log category constants.
     */
    public const CATEGORY_AUTH = 'auth';
    public const CATEGORY_SECURITY = 'security';
    public const CATEGORY_API = 'api';
    public const CATEGORY_WORKFLOW = 'workflow';
    public const CATEGORY_AGENT = 'agent';
    public const CATEGORY_AI = 'ai';
    public const CATEGORY_SYSTEM = 'system';
    public const CATEGORY_DATABASE = 'database';
    public const CATEGORY_CACHE = 'cache';
    public const CATEGORY_QUEUE = 'queue';

    /**
     * Scope to filter by level.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $levels
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLevel($query, $levels)
    {
        if (is_array($levels)) {
            return $query->whereIn('level', $levels);
        }
        return $query->where('level', $levels);
    }

    /**
     * Scope to filter by category.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $categories
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $categories)
    {
        if (is_array($categories)) {
            return $query->whereIn('category', $categories);
        }
        return $query->where('category', $categories);
    }

    /**
     * Scope to filter by source.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $source
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope to filter by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, ?int $userId)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    /**
     * Scope to filter by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     * @param string|null $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, string $from, ?string $to = null)
    {
        if ($to) {
            return $query->whereBetween('created_at', [$from, $to]);
        }
        return $query->where('created_at', '>=', $from);
    }

    /**
     * Scope to get error-level logs and above.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('level', [
            self::LEVEL_ERROR,
            self::LEVEL_CRITICAL,
            self::LEVEL_ALERT,
            self::LEVEL_EMERGENCY,
        ]);
    }

    /**
     * Get the level label.
     *
     * @return string
     */
    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            self::LEVEL_DEBUG => 'Debug',
            self::LEVEL_INFO => 'Info',
            self::LEVEL_NOTICE => 'Notice',
            self::LEVEL_WARNING => 'Warning',
            self::LEVEL_ERROR => 'Error',
            self::LEVEL_CRITICAL => 'Critical',
            self::LEVEL_ALERT => 'Alert',
            self::LEVEL_EMERGENCY => 'Emergency',
            default => ucfirst($this->level),
        };
    }

    /**
     * Get the level color class.
     *
     * @return string
     */
    public function getLevelColorAttribute(): string
    {
        return match ($this->level) {
            self::LEVEL_DEBUG => 'gray',
            self::LEVEL_INFO => 'blue',
            self::LEVEL_NOTICE => 'cyan',
            self::LEVEL_WARNING => 'yellow',
            self::LEVEL_ERROR => 'red',
            self::LEVEL_CRITICAL => 'red',
            self::LEVEL_ALERT => 'red',
            self::LEVEL_EMERGENCY => 'red',
            default => 'gray',
        };
    }
}
