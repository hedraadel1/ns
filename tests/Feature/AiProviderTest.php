<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AIProvider;
use App\Models\AIModel;
use App\Models\AIApiKey;

class AiProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_new_provider()
    {
        $response = $this->postJson('/api/v1/ai/providers', [
            'name' => 'Test Provider',
            'base_url' => 'https://api.test.com',
            'models_fetch_endpoint' => '/models',
            'generate_endpoint' => '/generate',
            'auth_header_format' => 'Bearer {key}',
            'payload_format' => 'openai',
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'name',
            'base_url',
            'models_fetch_endpoint',
            'generate_endpoint',
            'auth_header_format',
            'payload_format',
            'is_active',
            'created_at',
            'updated_at'
        ]);

        $this->assertDatabaseHas('ai_providers', [
            'name' => 'Test Provider',
            'base_url' => 'https://api.test.com',
        ]);
    }

    /** @test */
    public function it_lists_all_providers()
    {
        AIProvider::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/ai/providers');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_gets_a_specific_provider()
    {
        $provider = AIProvider::factory()->create();

        $response = $this->getJson("/api/v1/ai/providers/{$provider->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $provider->id->toString(),
            'name' => $provider->name,
        ]);
    }

    /** @test */
    public function it_updates_a_provider()
    {
        $provider = AIProvider::factory()->create([
            'name' => 'Original Name',
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/v1/ai/providers/{$provider->id}", [
            'name' => 'Updated Name',
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'Updated Name',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('ai_providers', [
            'id' => $provider->id,
            'name' => 'Updated Name',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function it_deletes_a_provider()
    {
        $provider = AIProvider::factory()->create();

        $response = $this->deleteJson("/api/v1/ai/providers/{$provider->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('ai_providers', ['id' => $provider->id]);
    }

    /** @test */
    public function it_syncs_models_for_a_provider()
    {
        $provider = AIProvider::factory()->create([
            'models_fetch_endpoint' => '/models',
            'generate_endpoint' => '/generate',
        ]);

        // Mock the HTTP response for model sync
        \Http::fake([
            // Mock the models endpoint
            '*' => \Http::response([
                'data' => [
                    [
                        'id' => 'gpt-4',
                        'name' => 'GPT-4',
                        'context_length' => 8192,
                    ],
                    [
                        'id' => 'gpt-3.5-turbo',
                        'name' => 'GPT-3.5 Turbo',
                        'context_length' => 4096,
                    ]
                ]
            ], 200, ['Content-Type' => 'application/json'])
        ]);

        $response = $this->postJson("/api/v1/ai/providers/{$provider->id}/sync-models");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Models synchronized successfully',
            'synced_count' => 2,
        ]);

        // Check that models were created in the database
        $this->assertDatabaseCount('ai_models', 2);
        $this->assertDatabaseHas('ai_models', [
            'provider_id' => $provider->id,
            'name' => 'GPT-4',
        ]);
        $this->assertDatabaseHas('ai_models', [
            'provider_id' => $provider->id,
            'name' => 'GPT-3.5 Turbo',
        ]);
    }
}