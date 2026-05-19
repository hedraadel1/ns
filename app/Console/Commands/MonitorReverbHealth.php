<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitorReverbHealth extends Command
{
    protected $signature = 'monitor:reverb-health';

    protected $description = 'Execute a periodic health check against the Reverb WebSocket service.';

    public function handle(): int
    {
        $host = config('broadcasting.connections.reverb.host', env('REVERB_HOST', '127.0.0.1'));
        $port = config('broadcasting.connections.reverb.port', env('REVERB_PORT', 6001));
        $scheme = config('broadcasting.connections.reverb.scheme', env('REVERB_SCHEME', 'https'));
        $url = sprintf('%s://%s:%s/health', $scheme, $host, $port);

        try {
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $this->info('Reverb health check passed.');
                return self::SUCCESS;
            }

            $message = sprintf('Reverb health check failed with status %s.', $response->status());
            Log::warning('Reverb health check returned an unhealthy response.', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $this->error($message);

            return self::FAILURE;
        } catch (\Throwable $exception) {
            Log::error('Reverb health check error.', [
                'url' => $url,
                'exception' => $exception->getMessage(),
            ]);

            $this->error('Reverb health check error: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
