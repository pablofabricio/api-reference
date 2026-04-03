<?php

namespace Tests\Feature\Notes;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createNotesSchema();
    }

    public function test_index_returns_only_notes_for_requested_reference_node(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = User::where('email', 'notes@example.com')->value('id');

        $referenceNodeA = $this->createReferenceNode('Romanos 1');
        $referenceNodeB = $this->createReferenceNode('Filipenses 4');

        DB::table('notes')->insert([
            [
                'user_id' => $userId,
                'content' => 'Nota A',
                'reference_node_id' => $referenceNodeA,
                'visibility' => 'PUBLIC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'content' => 'Nota B',
                'reference_node_id' => $referenceNodeB,
                'visibility' => 'PRIVATE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/notes?reference_node_id=' . $referenceNodeA);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.content', 'Nota A');
    }

    public function test_notes_endpoints_require_authentication(): void
    {
        $response = $this->getJson('/api/notes');

        $response->assertStatus(401)
            ->assertJsonStructure(['error']);
    }

    public function test_notes_reject_invalid_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/notes');

        $response->assertStatus(401)
            ->assertJsonStructure(['error']);
    }

    public function test_store_creates_note_successfully(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = User::where('email', 'notes@example.com')->value('id');
        $referenceNodeId = $this->createReferenceNode('Salmos 23');

        $payload = [
            'user_id' => $userId,
            'content' => 'Nova nota de teste',
            'reference_node_id' => $referenceNodeId,
            'visibility' => 'PUBLIC',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/notes', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.content', 'Nova nota de teste')
            ->assertJsonPath('data.visibility', 'PUBLIC');

        $this->assertDatabaseHas('notes', [
            'content' => 'Nova nota de teste',
            'reference_node_id' => $referenceNodeId,
        ]);
    }

    public function test_store_returns_validation_error_when_payload_is_invalid(): void
    {
        $token = $this->authenticateAndGetToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/notes', [
                'content' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Validation error')
            ->assertJsonStructure(['errors']);
    }

    public function test_show_returns_the_requested_note(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = User::where('email', 'notes@example.com')->value('id');
        $referenceNodeId = $this->createReferenceNode('Mateus 5');

        $noteId = DB::table('notes')->insertGetId([
            'user_id' => $userId,
            'content' => 'Nota para show',
            'reference_node_id' => $referenceNodeId,
            'visibility' => 'PUBLIC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/notes/' . $noteId);

        $response->assertOk()
            ->assertJsonPath('data.id', $noteId)
            ->assertJsonPath('data.content', 'Nota para show');
    }

    public function test_update_changes_note_content(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = User::where('email', 'notes@example.com')->value('id');
        $referenceNodeId = $this->createReferenceNode('Romanos 8');

        $noteId = DB::table('notes')->insertGetId([
            'user_id' => $userId,
            'content' => 'Conteudo antigo',
            'reference_node_id' => $referenceNodeId,
            'visibility' => 'PRIVATE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/notes/' . $noteId, [
                'user_id' => $userId,
                'content' => 'Conteudo atualizado',
                'reference_node_id' => $referenceNodeId,
                'visibility' => 'CHANNEL',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.content', 'Conteudo atualizado')
            ->assertJsonPath('data.visibility', 'CHANNEL');

        $this->assertDatabaseHas('notes', [
            'id' => $noteId,
            'content' => 'Conteudo atualizado',
            'visibility' => 'CHANNEL',
        ]);
    }

    public function test_destroy_deletes_note(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = User::where('email', 'notes@example.com')->value('id');
        $referenceNodeId = $this->createReferenceNode('Joao 3');

        $noteId = DB::table('notes')->insertGetId([
            'user_id' => $userId,
            'content' => 'Nota para remover',
            'reference_node_id' => $referenceNodeId,
            'visibility' => 'PUBLIC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/notes/' . $noteId);

        $response->assertNoContent();

        $this->assertDatabaseMissing('notes', [
            'id' => $noteId,
        ]);
    }

    public function test_user_cannot_access_note_owned_by_another_user(): void
    {
        $token = $this->authenticateAndGetToken();
        $otherUser = User::factory()->create([
            'email' => 'other@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $referenceNodeId = $this->createReferenceNode('Atos 2');

        $noteId = DB::table('notes')->insertGetId([
            'user_id' => $otherUser->id,
            'content' => 'Nota de outro usuario',
            'reference_node_id' => $referenceNodeId,
            'visibility' => 'PUBLIC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $showResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/notes/' . $noteId);

        $showResponse->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');

        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/notes/' . $noteId, [
                'user_id' => $otherUser->id,
                'content' => 'Tentativa de atualizar nota alheia',
                'reference_node_id' => $referenceNodeId,
                'visibility' => 'PRIVATE',
            ]);

        $updateResponse->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');

        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/notes/' . $noteId);

        $deleteResponse->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');
    }

    public function test_user_cannot_create_note_for_another_user_id(): void
    {
        $token = $this->authenticateAndGetToken();
        $otherUser = User::factory()->create([
            'email' => 'another@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $referenceNodeId = $this->createReferenceNode('Joao 1');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/notes', [
                'user_id' => $otherUser->id,
                'content' => 'Tentando criar em nome de outro usuario',
                'reference_node_id' => $referenceNodeId,
                'visibility' => 'PUBLIC',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Access denied');
    }

    private function authenticateAndGetToken(): string
    {
        $user = User::factory()->create([
            'email' => 'notes@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678',
        ])->assertOk();

        return (string) $response->json('access_token');
    }

    private function createReferenceNode(string $label): int
    {
        return (int) DB::table('reference_nodes')->insertGetId([
            'type' => 'CHAPTER',
            'content' => $label,
            'label' => $label,
            'reference_id' => 1,
            'parent_node_id' => null,
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createNotesSchema(): void
    {
        if (!Schema::hasTable('reference_nodes')) {
            Schema::create('reference_nodes', function (Blueprint $table) {
                $table->id();
                $table->string('type', 50);
                $table->text('content');
                $table->string('label')->nullable();
                $table->unsignedBigInteger('reference_id')->default(1);
                $table->unsignedBigInteger('parent_node_id')->nullable();
                $table->integer('position')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->text('content');
                $table->unsignedBigInteger('reference_node_id');
                $table->string('visibility', 20)->default('PRIVATE');
                $table->timestamps();
            });
        }

        // Required by NoteService::authorizeModelAccess → hasChannelManagementAccessByReferenceId
        if (!Schema::hasTable('channel_references')) {
            Schema::create('channel_references', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('channel_id');
                $table->unsignedBigInteger('reference_id');
                $table->timestamps();
                $table->unique(['channel_id', 'reference_id']);
            });
        }

        if (!Schema::hasTable('channel_members')) {
            Schema::create('channel_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('channel_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role', 20)->default('MEMBER');
                $table->timestamps();
                $table->unique(['channel_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('channels')) {
            Schema::create('channels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('visibility', 20)->default('PRIVATE');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }
}
