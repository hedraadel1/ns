<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MCPIntegrationService
{
    protected array $servers = [];
    protected array $connections = [];

    public function registerServer(string $name, array $config): void
    {
        $this->servers[$name] = array_merge([
            'name' => $name,
            'url' => '',
            'transport' => 'stdio',
            'enabled' => true,
            'registered_at' => now()->toISOString(),
        ], $config);

        Log::info("MCP server registered: {$name}");
    }

    public function getServer(string $name): ?array
    {
        return $this->servers[$name] ?? null;
    }

    public function getAllServers(): array
    {
        return $this->servers;
    }

    public function connect(string $name): array
    {
        if (!isset($this->servers[$name])) {
            throw new \InvalidArgumentException("MCP server not found: {$name}");
        }

        $server = $this->servers[$name];

        if (!$server['enabled']) {
            throw new \RuntimeException("MCP server is disabled: {$name}");
        }

        try {
            $connection = $this->establishConnection($server);
            $this->connections[$name] = $connection;

            Log::info("MCP server connected: {$name}");

            return [
                'success' => true,
                'server' => $name,
                'connection' => $connection,
                'connected_at' => now()->toISOString(),
            ];
        } catch (\Throwable $e) {
            Log::error("MCP server connection failed: {$name}", [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'server' => $name,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function establishConnection(array $server): array
    {
        return [
            'server' => $server['name'],
            'url' => $server['url'],
            'transport' => $server['transport'],
            'status' => 'connected',
            'connected_at' => now()->toISOString(),
        ];
    }

    public function disconnect(string $name): bool
    {
        unset($this->connections[$name]);
        Log::info("MCP server disconnected: {$name}");
        return true;
    }

    public function isConnected(string $name): bool
    {
        return isset($this->connections[$name]);
    }

    public function getConnection(string $name): ?array
    {
        return $this->connections[$name] ?? null;
    }

    public function listTools(string $serverName): array
    {
        if (!isset($this->servers[$serverName])) {
            throw new \InvalidArgumentException("MCP server not found: {$serverName}");
        }

        $server = $this->servers[$serverName];

        return [
            'server' => $serverName,
            'tools' => $server['tools'] ?? [],
            'resources' => $server['resources'] ?? [],
        ];
    }

    public function callTool(string $serverName, string $toolName, array $params = []): array
    {
        if (!isset($this->servers[$serverName])) {
            throw new \InvalidArgumentException("MCP server not found: {$serverName}");
        }

        $server = $this->servers[$serverName];

        if (!$server['enabled']) {
            throw new \RuntimeException("MCP server is disabled: {$serverName}");
        }

        Log::info("MCP tool called: {$toolName} on server {$serverName}", $params);

        return [
            'success' => true,
            'server' => $serverName,
            'tool' => $toolName,
            'params' => $params,
            'result' => "MCP tool {$toolName} executed on {$serverName}",
            'called_at' => now()->toISOString(),
        ];
    }

    public function attachToAgent(Agent $agent, string $serverName): array
    {
        if (!isset($this->servers[$serverName])) {
            throw new \InvalidArgumentException("MCP server not found: {$serverName}");
        }

        $server = $this->servers[$serverName];
        $agentMetadata = $agent->metadata ?? [];
        $mcpServers = $agentMetadata['mcp_servers'] ?? [];

        if (!in_array($serverName, $mcpServers)) {
            $mcpServers[] = $serverName;
            $agentMetadata['mcp_servers'] = $mcpServers;
            $agent->update(['metadata' => $agentMetadata]);
        }

        Log::info("MCP server attached to agent: {$serverName} -> {$agent->name}");

        return [
            'success' => true,
            'agent_id' => $agent->id,
            'agent_name' => $agent->name,
            'server' => $serverName,
        ];
    }

    public function detachFromAgent(Agent $agent, string $serverName): array
    {
        $agentMetadata = $agent->metadata ?? [];
        $mcpServers = $agentMetadata['mcp_servers'] ?? [];

        $mcpServers = array_filter($mcpServers, fn($s) => $s !== $serverName);
        $agentMetadata['mcp_servers'] = array_values($mcpServers);
        $agent->update(['metadata' => $agentMetadata]);

        Log::info("MCP server detached from agent: {$serverName} -> {$agent->name}");

        return [
            'success' => true,
            'agent_id' => $agent->id,
            'agent_name' => $agent->name,
            'server' => $serverName,
        ];
    }

    public function getAgentServers(Agent $agent): array
    {
        $metadata = $agent->metadata ?? [];
        $serverNames = $metadata['mcp_servers'] ?? [];

        return array_filter($this->servers, fn($server, $name) =>
            in_array($name, $serverNames), ARRAY_FILTER_USE_BOTH);
    }

    public function unregister(string $name): bool
    {
        unset($this->servers[$name], $this->connections[$name]);
        return true;
    }

    public function clear(): void
    {
        $this->servers = [];
        $this->connections = [];
    }
}
