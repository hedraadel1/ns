<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Services\AgentConfigurationService;
use App\Services\AgentLifecycleService;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    public function __construct(
        protected AgentLifecycleService $lifecycle,
        protected AgentConfigurationService $config,
        protected LogService $logService
    ) {}

    public function index(Request $request)
    {
        $query = Agent::query();

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $agents = $query->with(['tools', 'skills'])->paginate($request->per_page ?? 20);

        return response()->json($agents);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:agents,key',
            'description' => 'nullable|string',
            'type' => 'required|string|in:reflection,team,autonomous,specialized,supervisor',
            'provider' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agent = Agent::create($validator->validated());

        $this->logService->info('Agent created', [
            'channel' => 'agent',
            'type' => 'create',
            'related_id' => $agent->id,
            'related_type' => Agent::class,
            'user_id' => $request->user()?->id,
        ]);

        return response()->json(['data' => $agent, 'message' => 'Agent created successfully'], 201);
    }

    public function show(Agent $agent)
    {
        $agent->load(['tools', 'skills', 'tasks']);
        $agent->config = $this->config->load($agent);

        return response()->json(['data' => $agent]);
    }

    public function update(Request $request, Agent $agent)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|string|in:reflection,team,autonomous,specialized,supervisor',
            'provider' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'metadata' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agent->update($validator->validated());

        $this->logService->info('Agent updated', [
            'channel' => 'agent',
            'type' => 'update',
            'related_id' => $agent->id,
            'related_type' => Agent::class,
            'user_id' => $request->user()?->id,
        ]);

        return response()->json(['data' => $agent, 'message' => 'Agent updated successfully']);
    }

    public function destroy(Agent $agent)
    {
        $agent->update(['is_active' => false]);

        $this->logService->info('Agent deactivated', [
            'channel' => 'agent',
            'type' => 'deactivate',
            'related_id' => $agent->id,
            'related_type' => Agent::class,
            'user_id' => request()->user()?->id,
        ]);

        return response()->json(['message' => 'Agent deactivated successfully']);
    }

    public function execute(Request $request, Agent $agent)
    {
        if ($agent->isRunning()) {
            return response()->json(['message' => 'Agent is already running'], 409);
        }

        try {
            $this->lifecycle->initialize($agent);

            $this->logService->info('Agent execution started', [
                'channel' => 'agent',
                'type' => 'execute',
                'related_id' => $agent->id,
                'related_type' => Agent::class,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'Agent execution started',
                'data' => $agent->fresh()
            ]);
        } catch (\Throwable $e) {
            $this->logService->error('Agent execution failed', [
                'channel' => 'agent',
                'type' => 'execute',
                'related_id' => $agent->id,
                'related_type' => Agent::class,
                'user_id' => $request->user()?->id,
                'context' => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'message' => 'Failed to start agent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStatus(Agent $agent)
    {
        $agent->load(['tools', 'skills']);

        return response()->json([
            'data' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'type' => $agent->type,
                'status' => $agent->status,
                'status_label' => $agent->status_label,
                'is_running' => $agent->isRunning(),
                'is_idle' => $agent->isIdle(),
                'has_error' => $agent->hasError(),
                'success_rate' => $agent->getSuccessRate(),
                'execution_count' => $agent->execution_count,
                'success_count' => $agent->success_count,
                'error_count' => $agent->error_count,
                'last_executed_at' => $agent->last_executed_at,
                'available_transitions' => $this->lifecycle->getAvailableTransitions($agent),
                'config' => $this->config->load($agent),
            ]
        ]);
    }

    public function getHealth(Agent $agent)
    {
        $health = [
            'id' => $agent->id,
            'name' => $agent->name,
            'status' => $agent->status,
            'is_active' => $agent->is_active,
            'success_rate' => $agent->getSuccessRate(),
            'execution_count' => $agent->execution_count,
            'error_count' => $agent->error_count,
            'last_executed_at' => $agent->last_executed_at,
            'tools_count' => $agent->tools()->count(),
            'active_tools_count' => $agent->tools()->where('is_active', true)->count(),
            'skills_count' => $agent->skills()->count(),
            'active_skills_count' => $agent->skills()->where('is_active', true)->count(),
            'health_status' => $this->calculateHealthStatus($agent),
        ];

        return response()->json(['data' => $health]);
    }

    public function getMetrics(Agent $agent)
    {
        $metrics = [
            'execution_count' => $agent->execution_count,
            'success_count' => $agent->success_count,
            'error_count' => $agent->error_count,
            'success_rate' => $agent->getSuccessRate(),
            'last_executed_at' => $agent->last_executed_at,
            'avg_execution_time' => $this->calculateAvgExecutionTime($agent),
            'total_tasks' => $agent->tasks()->count(),
            'pending_tasks' => $agent->tasks()->where('status', 'pending')->count(),
            'completed_tasks' => $agent->tasks()->where('status', 'completed')->count(),
            'failed_tasks' => $agent->tasks()->where('status', 'failed')->count(),
        ];

        return response()->json(['data' => $metrics]);
    }

    protected function calculateHealthStatus(Agent $agent): string
    {
        if (!$agent->is_active) {
            return 'inactive';
        }

        if ($agent->hasError()) {
            return 'error';
        }

        if ($agent->getSuccessRate() < 50 && $agent->execution_count > 10) {
            return 'degraded';
        }

        if ($agent->isRunning()) {
            return 'healthy';
        }

        return 'healthy';
    }

    protected function calculateAvgExecutionTime(Agent $agent): ?float
    {
        $avgTime = $agent->tasks()
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, completed_at)) as avg_seconds')
            ->value('avg_seconds');

        return $avgTime ? round($avgTime, 2) : null;
    }
}