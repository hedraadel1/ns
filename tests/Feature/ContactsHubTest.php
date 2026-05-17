<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactsHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_crud_and_search_workflow()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $createResponse = $this->postJson('/api/v1/contacts', [
            'name' => 'Hedra Nexus',
            'email' => 'hedra@nexus.ai',
            'phone' => '+1234567890',
            'type' => Contact::TYPE_CLIENT,
            'company' => 'Nexus Labs',
        ]);

        $createResponse->assertStatus(201);
        $contactId = $createResponse->json('data.id');

        $this->assertDatabaseHas('contacts', ['id' => $contactId, 'name' => 'Hedra Nexus', 'type' => Contact::TYPE_CLIENT]);

        $showResponse = $this->getJson("/api/v1/contacts/{$contactId}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.name', 'Hedra Nexus');

        $searchResponse = $this->getJson('/api/v1/contacts?search=Hedra');
        $searchResponse->assertStatus(200);
        $searchResponse->assertJsonPath('data.data.0.name', 'Hedra Nexus');

        $updateResponse = $this->putJson("/api/v1/contacts/{$contactId}", [
            'title' => 'Executive AI',
            'type' => Contact::TYPE_PROSPECT,
        ]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('data.title', 'Executive AI');
        $this->assertDatabaseHas('contacts', ['id' => $contactId, 'type' => Contact::TYPE_PROSPECT]);

        $deleteResponse = $this->deleteJson("/api/v1/contacts/{$contactId}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('contacts', ['id' => $contactId]);
    }

    public function test_contact_import_export_endpoints()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $importPayload = [
            'contacts' => [
                [
                    'name' => 'Alice Example',
                    'email' => 'alice@example.com',
                    'phone' => '+15550000001',
                    'type' => Contact::TYPE_FRIEND,
                ],
                [
                    'name' => 'Bob Example',
                    'email' => 'bob@example.com',
                    'phone' => '+15550000002',
                    'type' => Contact::TYPE_FAMILY,
                ],
            ],
        ];

        $importResponse = $this->postJson('/api/v1/contacts/import', $importPayload);
        $importResponse->assertStatus(200);
        $importResponse->assertJsonPath('created', 2);

        $this->assertDatabaseHas('contacts', ['name' => 'Alice Example', 'type' => Contact::TYPE_FRIEND]);
        $this->assertDatabaseHas('contacts', ['name' => 'Bob Example', 'type' => Contact::TYPE_FAMILY]);

        $exportResponse = $this->get('/api/v1/contacts/export');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('content-type'));
        $this->assertStringContainsString('Alice Example', $exportResponse->getContent());
    }

    public function test_contact_analytics_and_related_endpoints()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $contact = Contact::factory()->create(['type' => Contact::TYPE_VENDOR]);

        $analyticsResponse = $this->getJson("/api/v1/contacts/{$contact->id}/analytics");
        $analyticsResponse->assertStatus(200);
        $analyticsResponse->assertJsonPath('data.contact_id', (string) $contact->id);
        $analyticsResponse->assertJsonPath('data.analytics.type', Contact::TYPE_VENDOR);
    }
}
