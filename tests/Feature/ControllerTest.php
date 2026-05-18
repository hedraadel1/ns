<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\User;
use App\Models\Workflow;
use App\Models\AgentTask;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    // ─── AgentController Tests ───────────────────────────────────────────

    public function test_agent_index_returns_paginated_agents(): void
    {
        Agent::factory()->count(3)->create();
        
        $response = $this->getJson('/api/v1/agents');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'type', 'status']]]);
    }

    public function test_agent_index_filters_by_type(): void
    {
        Agent::factory()->create(['type' => Agent::TYPE_REFLECTION]);
        Agent::factory()->create(['type' => Agent::TYPE_TEAM]);
        
        $response = $this->getJson('/api/v1/agents?type=reflection');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', Agent::TYPE_REFLECTION);
    }

    public function test_agent_index_filters_by_status(): void
    {
        Agent::factory()->create(['status' => Agent::STATUS_RUNNING]);
        Agent::factory()->create(['status' => Agent::STATUS_IDLE]);
        
        $response = $this->getJson('/api/v1/agents?status=running');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', Agent::STATUS_RUNNING);
    }

    public function test_agent_index_filters_by_is_active(): void
    {
        Agent::factory()->create(['is_active' => true]);
        Agent::factory()->create(['is_active' => false]);
        
        $response = $this->getJson('/api/v1/agents?is_active=true');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.is_active', true);
    }

    public function test_agent_index_searches_by_name(): void
    {
        Agent::factory()->create(['name' => 'Searchable Agent']);
        Agent::factory()->create(['name' => 'Other Agent']);
        
        $response = $this->getJson('/api/v1/agents?search=Searchable');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Searchable Agent');
    }

    public function test_agent_store_creates_new_agent(): void
    {
        $response = $this->postJson('/api/v1/agents', [
            'name' => 'New Agent',
            'key' => 'new-agent',
            'type' => Agent::TYPE_REFLECTION,
            'provider' => 'openai',
        ]);
        
        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Agent');
        
        $this->assertDatabaseHas('agents', ['name' => 'New Agent', 'key' => 'new-agent']);
    }

    public function test_agent_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/agents', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'key', 'type']);
    }

    public function test_agent_store_validates_unique_key(): void
    {
        Agent::factory()->create(['key' => 'existing-key']);
        
        $response = $this->postJson('/api/v1/agents', [
            'name' => 'New Agent',
            'key' => 'existing-key',
            'type' => Agent::TYPE_REFLECTION,
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }

    public function test_agent_show_returns_agent_with_relations(): void
    {
        $agent = Agent::factory()->create();
        
        $response = $this->getJson("/api/v1/agents/{$agent->id}");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $agent->id)
            ->assertJsonStructure(['data' => ['tools', 'skills', 'tasks', 'config']]);
    }

    public function test_agent_update_updates_agent(): void
    {
        $agent = Agent::factory()->create(['name' => 'Old Name']);
        
        $response = $this->putJson("/api/v1/agents/{$agent->id}", [
            'name' => 'New Name',
        ]);
        
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');
        
        $this->assertDatabaseHas('agents', ['id' => $agent->id, 'name' => 'New Name']);
    }

    public function test_agent_destroy_deactivates_agent(): void
    {
        $agent = Agent::factory()->create(['is_active' => true]);
        
        $response = $this->deleteJson("/api/v1/agents/{$agent->id}");
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('agents', ['id' => $agent->id, 'is_active' => false]);
    }

    public function test_agent_execute_starts_agent_execution(): void
    {
        $agent = Agent::factory()->create(['status' => Agent::STATUS_IDLE]);

        $response = $this->postJson("/api/v1/agents/{$agent->id}/execute");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Agent execution started');
    }

    public function test_agent_execute_returns_conflict_when_already_running(): void
    {
        $agent = Agent::factory()->create(['status' => Agent::STATUS_RUNNING]);

        $response = $this->postJson("/api/v1/agents/{$agent->id}/execute");

        if ($response->status() !== 409) {
            echo "\nResponse status: " . $response->status() . "\n";
            echo "Response content: " . $response->getContent() . "\n";
        }

        $response->assertStatus(409)
            ->assertJsonPath('message', 'Agent is already running');
    }

    public function test_agent_get_status_returns_status_info(): void
    {
        $agent = Agent::factory()->create([
            'status' => Agent::STATUS_RUNNING,
            'execution_count' => 5,
            'success_count' => 4,
        ]);
        
        $response = $this->getJson("/api/v1/agents/{$agent->id}/status");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.is_running', true)
            ->assertJsonPath('data.success_rate', 80.0)
            ->assertJsonPath('data.execution_count', 5);
    }

    public function test_agent_get_health_returns_health_info(): void
    {
        $agent = Agent::factory()->create();
        
        $response = $this->getJson("/api/v1/agents/{$agent->id}/health");
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'id', 'name', 'status', 'is_active', 'success_rate',
                'execution_count', 'error_count', 'health_status',
                'tools_count', 'active_tools_count', 'skills_count', 'active_skills_count',
            ]]);
    }

    public function test_agent_get_metrics_returns_metrics(): void
    {
        $agent = Agent::factory()->create();
        
        $response = $this->getJson("/api/v1/agents/{$agent->id}/metrics");
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'execution_count', 'success_count', 'error_count',
                'success_rate', 'last_executed_at', 'avg_execution_time',
                'total_tasks', 'pending_tasks', 'completed_tasks', 'failed_tasks',
            ]]);
    }

    // ─── WorkflowController Tests ────────────────────────────────────────

    public function test_workflow_index_returns_paginated_workflows(): void
    {
        Workflow::factory()->count(3)->create();
        
        $response = $this->getJson('/api/v1/workflows');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'status', 'progress']]]);
    }

    public function test_workflow_index_filters_by_status(): void
    {
        Workflow::factory()->create(['status' => Workflow::STATUS_DRAFT]);
        Workflow::factory()->create(['status' => Workflow::STATUS_ACTIVE]);
        
        $response = $this->getJson('/api/v1/workflows?status=draft');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', Workflow::STATUS_DRAFT);
    }

    public function test_workflow_store_creates_new_workflow(): void
    {
        $response = $this->postJson('/api/v1/workflows', [
            'name' => 'New Workflow',
            'key' => 'new-workflow',
            'steps' => [
                ['name' => 'Step 1', 'action' => 'log', 'message' => 'Workflow started'],
            ],
            'trigger_type' => Workflow::TRIGGER_MANUAL,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Workflow');

        $this->assertDatabaseHas('workflows', ['name' => 'New Workflow']);
    }

    public function test_workflow_store_validates_required_steps(): void
    {
        $response = $this->postJson('/api/v1/workflows', [
            'name' => 'No Steps Workflow',
            'key' => 'no-steps',
            'steps' => [],
            'trigger_type' => Workflow::TRIGGER_MANUAL,
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steps']);
    }

    public function test_workflow_show_returns_workflow_with_progress(): void
    {
        $workflow = Workflow::factory()->create([
            'steps' => [
                ['name' => 'Step 1', 'status' => 'completed'],
                ['name' => 'Step 2', 'status' => 'pending'],
            ],
        ]);
        
        $response = $this->getJson("/api/v1/workflows/{$workflow->id}");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $workflow->id)
            ->assertJsonPath('progress', 50)
            ->assertJsonPath('total_steps', 2)
            ->assertJsonPath('completed_steps', 1);
    }

    public function test_workflow_update_updates_workflow(): void
    {
        $workflow = Workflow::factory()->create(['name' => 'Old Name']);
        
        $response = $this->putJson("/api/v1/workflows/{$workflow->id}", [
            'name' => 'New Name',
        ]);
        
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_workflow_destroy_deactivates_workflow(): void
    {
        $workflow = Workflow::factory()->create(['is_active' => true]);
        
        $response = $this->deleteJson("/api/v1/workflows/{$workflow->id}");
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('workflows', ['id' => $workflow->id, 'is_active' => false]);
    }

    public function test_workflow_execute_starts_workflow_execution(): void
    {
        $workflow = Workflow::factory()->create([
            'status' => Workflow::STATUS_DRAFT,
            'steps' => [
                ['name' => 'Step 1', 'action' => 'agent', 'agent_type' => 'autonomous'],
            ],
        ]);
        
        $response = $this->postJson("/api/v1/workflows/{$workflow->id}/execute");
        
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Workflow execution completed');
    }

    public function test_workflow_get_progress_returns_progress_info(): void
    {
        $workflow = Workflow::factory()->create([
            'steps' => [
                ['name' => 'Step 1', 'status' => 'completed'],
                ['name' => 'Step 2', 'status' => 'completed'],
                ['name' => 'Step 3', 'status' => 'pending'],
            ],
        ]);
        
        $response = $this->getJson("/api/v1/workflows/{$workflow->id}/progress");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.progress', 67)
            ->assertJsonPath('data.total_steps', 3)
            ->assertJsonPath('data.completed_steps', 2);
    }

    public function test_workflow_get_templates_returns_templates(): void
    {
        $response = $this->getJson('/api/v1/workflows/templates');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'description', 'category', 'steps']]]);
    }

    // ─── TaskController Tests ────────────────────────────────────────────

    public function test_task_index_returns_paginated_tasks(): void
    {
        AgentTask::factory()->count(3)->create();
        
        $response = $this->getJson('/api/v1/tasks');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'title', 'status', 'priority']]]);
    }

    public function test_task_index_filters_by_agent_id(): void
    {
        $agent = Agent::factory()->create();
        AgentTask::factory()->create(['agent_id' => $agent->id]);
        AgentTask::factory()->create();
        
        $response = $this->getJson("/api/v1/tasks?agent_id={$agent->id}");
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_task_index_filters_by_workflow_id(): void
    {
        $workflow = Workflow::factory()->create();
        AgentTask::factory()->create(['workflow_id' => $workflow->id]);
        AgentTask::factory()->create();
        
        $response = $this->getJson("/api/v1/tasks?workflow_id={$workflow->id}");
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_task_store_creates_and_enqueues_task(): void
    {
        $response = $this->postJson('/api/v1/tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'priority' => 5,
        ]);
        
        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'New Task')
            ->assertJsonPath('data.status', 'pending');
        
        $this->assertDatabaseHas('agent_tasks', ['title' => 'New Task', 'status' => 'pending']);
    }

    public function test_task_store_validates_required_title(): void
    {
        $response = $this->postJson('/api/v1/tasks', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_task_show_returns_task_with_relations(): void
    {
        $task = AgentTask::factory()->create();
        
        $response = $this->getJson("/api/v1/tasks/{$task->id}");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonStructure(['data' => ['agent', 'workflow', 'steps']]);
    }

    public function test_task_update_updates_task(): void
    {
        $task = AgentTask::factory()->create(['status' => 'pending']);
        
        $response = $this->putJson("/api/v1/tasks/{$task->id}", [
            'status' => 'running',
            'progress' => 50,
        ]);
        
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'running')
            ->assertJsonPath('data.progress', 50);
    }

    public function test_task_destroy_deletes_task(): void
    {
        $task = AgentTask::factory()->create();
        
        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('agent_tasks', ['id' => $task->id]);
    }

    public function test_task_cancel_cancels_task(): void
    {
        $task = AgentTask::factory()->create(['status' => 'pending']);
        
        $response = $this->postJson("/api/v1/tasks/{$task->id}/cancel");
        
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Task cancelled');
    }

    public function test_task_pause_pauses_task(): void
    {
        $task = AgentTask::factory()->create(['status' => 'running']);
        
        $response = $this->postJson("/api/v1/tasks/{$task->id}/pause");
        
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Task paused');
    }

    public function test_task_resume_resumes_task(): void
    {
        $task = AgentTask::factory()->create(['status' => 'paused']);
        
        $response = $this->postJson("/api/v1/tasks/{$task->id}/resume");
        
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Task resumed');
    }

    public function test_task_get_stats_returns_statistics(): void
    {
        AgentTask::factory()->count(5)->create(['status' => 'completed']);
        AgentTask::factory()->count(3)->create(['status' => 'pending']);
        
        $response = $this->getJson('/api/v1/tasks/stats');
        
        $response->assertStatus(200)
            ->assertJsonPath('data.total', 8)
            ->assertJsonPath('data.completed', 5)
            ->assertJsonPath('data.pending', 3);
    }

    public function test_task_get_active_returns_active_tasks(): void
    {
        AgentTask::factory()->create(['status' => 'pending']);
        AgentTask::factory()->create(['status' => 'running']);
        AgentTask::factory()->create(['status' => 'completed']);
        
        $response = $this->getJson('/api/v1/tasks/active');
        
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_task_get_queue_stats_returns_queue_statistics(): void
    {
        $response = $this->getJson('/api/v1/tasks/queue-stats');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['pending', 'processing', 'completed', 'failed']]);
    }

    public function test_task_get_routing_stats_returns_routing_statistics(): void
    {
        $response = $this->getJson('/api/v1/tasks/routing-stats');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    // ─── SettingController Tests ─────────────────────────────────────────

    public function test_setting_index_returns_all_settings(): void
    {
        Setting::factory()->count(3)->create();
        
        $response = $this->getJson('/api/v1/settings');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'key', 'value', 'type', 'group']]]);
    }

    public function test_setting_store_creates_new_setting(): void
    {
        $response = $this->postJson('/api/v1/settings', [
            'key' => 'new_setting',
            'value' => 'test_value',
            'type' => Setting::TYPE_STRING,
            'group' => Setting::GROUP_GENERAL,
        ]);
        
        $response->assertStatus(201)
            ->assertJsonPath('data.key', 'new_setting');
        
        $this->assertDatabaseHas('settings', ['key' => 'new_setting']);
    }

    public function test_setting_show_returns_setting(): void
    {
        $setting = Setting::factory()->create();

        $response = $this->getJson("/api/v1/settings/{$setting->key}");

        $response->assertStatus(200)
            ->assertJsonPath('data.key', $setting->key);
    }

    public function test_setting_update_updates_setting(): void
    {
        $setting = Setting::factory()->create(['value' => 'old_value']);

        $response = $this->putJson("/api/v1/settings/{$setting->key}", [
            'value' => 'new_value',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.value', 'new_value');
    }

    public function test_setting_destroy_deletes_setting(): void
    {
        $setting = Setting::factory()->create();

        $response = $this->deleteJson("/api/v1/settings/{$setting->key}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('settings', ['key' => $setting->key]);
    }

    public function test_setting_get_by_group_returns_grouped_settings(): void
    {
        Setting::factory()->create(['group' => Setting::GROUP_GENERAL]);
        Setting::factory()->create(['group' => Setting::GROUP_SECURITY]);

        $response = $this->getJson('/api/v1/settings/grouped');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_setting_get_public_returns_public_settings(): void
    {
        Setting::factory()->create(['is_public' => true]);
        Setting::factory()->create(['is_public' => false]);
        
        $response = $this->getJson('/api/v1/settings/public');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
