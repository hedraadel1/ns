<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log as LaravelLog;

/**
 * LogService
 *
 * Centralized application logging service.
 * Wraps Laravel's logging with structured context,
 * categorization, and database persistence.
 */
class LogService
{
    /**
     * The default log channel to use.
     *
     * @var string
     */
    protected string $channel;

    /**
     * Create a new LogService instance.
     *
     * @param string|null $channel
     * @return void
     */
    public function __construct(?string $channel = null)
    {
        $this->channel = $channel ?? config('logging.default', 'stack');
    }

    /**
     * Log a debug message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(Log::LEVEL_DEBUG, $message, $context);
    }

    /**
     * Log an info message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(Log::LEVEL_INFO, $message, $context);
    }

    /**
     * Log a notice message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(Log::LEVEL_NOTICE, $message, $context);
    }

    /**
     * Log a warning message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(Log::LEVEL_WARNING, $message, $context);
    }

    /**
     * Log an error message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(Log::LEVEL_ERROR, $message, $context);
    }

    /**
     * Log a critical message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(Log::LEVEL_CRITICAL, $message, $context);
    }

    /**
     * Log an alert message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(Log::LEVEL_ALERT, $message, $context);
    }

    /**
     * Log an emergency message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(Log::LEVEL_EMERGENCY, $message, $context);
    }

    /**
     * Write a log entry at the specified level.
     *
     * @param string $level
     * @param string $message
     * @param array<string, mixed> $context
     * @return Log
     */
    public function log(string $level, string $message, array $context = []): Log
    {
        // Write to Laravel's log channels
        LaravelLog::stack([$this->channel])->log($level, $message, $context);

        // Persist to database
        $log = Log::create([
            'level' => $level,
            'category' => $context['category'] ?? Log::CATEGORY_SYSTEM,
            'message' => $message,
            'context' => Arr::except($context, ['category']),
            'source' => $context['source'] ?? 'app',
            'user_id' => $context['user_id'] ?? null,
            'ip_address' => $context['ip_address'] ?? request()->ip(),
            'user_agent' => $context['user_agent'] ?? request()->userAgent(),
        ]);

        return $log;
    }

    /**
     * Get recent logs.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, Log>
     */
    public function recent(int $limit = 100)
    {
        return Log::latest()->limit($limit)->get();
    }

    /**
     * Get logs by level.
     *
     * @param string|array $levels
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, Log>
     */
    public function byLevel($levels, int $limit = 100)
    {
        return Log::byLevel($levels)->latest()->limit($limit)->get();
    }

    /**
     * Get logs by category.
     *
     * @param string|array $categories
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, Log>
     */
    public function byCategory($categories, int $limit = 100)
    {
        return Log::byCategory($categories)->latest()->limit($limit)->get();
    }
}
