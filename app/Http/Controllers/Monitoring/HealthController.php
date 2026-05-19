<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    public function health(Request $request)
    {
        $redis = $this->checkRedis();
        $database = $this->checkDatabase();
        $reverb = $this->checkReverb();
        $queue = $this->checkQueue();

        $status = collect([$redis['ok'], $database['ok'], $reverb['ok'], $queue['ok']])->every(fn ($ok) => $ok)
            ? 'healthy'
            : 'degraded';

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toDateTimeString(),
            'checks' => [
                'redis' => $redis,
                'database' => $database,
                'reverb' => $reverb,
                'queue' => $queue,
            ],
        ]);
    }

    public function reverb()
    {
        return response()->json($this->checkReverb());
    }

    public function queue()
    {
        return response()->json($this->checkQueue());
    }

    protected function checkRedis(): array
    {
        try {
            $connection = Redis::connection();
            $ok = $connection->ping() === 'PONG';
            return ['ok' => $ok, 'driver' => 'redis'];
        } catch (\Throwable $exception) {
            Log::warning('Redis health check failed', ['exception' => $exception->getMessage()]);
            return ['ok' => false, 'error' => $exception->getMessage()];
        }
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['ok' => true, 'driver' => DB::getDefaultConnection()];
        } catch (\Throwable $exception) {
            Log::warning('Database health check failed', ['exception' => $exception->getMessage()]);
            return ['ok' => false, 'error' => $exception->getMessage()];
        }
    }

    protected function checkReverb(): array
    {
        $host = config('broadcasting.connections.reverb.host', env('REVERB_HOST', '127.0.0.1'));
        $port = config('broadcasting.connections.reverb.port', env('REVERB_PORT', 6001));
        $scheme = config('broadcasting.connections.reverb.scheme', env('REVERB_SCHEME', 'https'));
        $url = sprintf('%s://%s:%s/health', $scheme, $host, $port);

        try {
            $response = Http::timeout(3)->get($url);
            $ok = $response->successful();
            return [
                'ok' => $ok,
                'url' => $url,
                'status' => $response->status(),
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'url' => $url,
                'error' => $exception->getMessage(),
            ];
        }
    }

    protected function checkQueue(): array
    {
        try {
            $redis = Redis::connection();
            $queues = [
                'default' => $redis->llen('queues:default'),
                'critical' => $redis->llen('queues:critical'),
                'llm-inference' => $redis->llen('queues:llm-inference'),
                'batch' => $redis->llen('queues:batch'),
            ];
            $failedJobs = DB::table('failed_jobs')->count();
            return [
                'ok' => true,
                'queues' => $queues,
                'failed_jobs' => $failedJobs,
            ];
        } catch (\Throwable $exception) {
            Log::warning('Queue health check failed', ['exception' => $exception->getMessage()]);
            return ['ok' => false, 'error' => $exception->getMessage()];
        }
    }
}
