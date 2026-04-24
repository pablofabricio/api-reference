<?php

namespace Tests\Feature\References;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReferenceNodeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createReferencesSchema();
        $this->createReferenceNodesSchema();
    }

    public function test_index_includes_nested_reference_payload_with_id_and_title(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'ref-nodes@example.com')->value('id');

        $referenceId = $this->createReference($userId, 'BOOK', 'Filipenses - Semana 2 (Capitulo 2)');
        $this->createReferenceNode($referenceId, 'BOOK', 'Filipenses - Semana 2 (Capitulo 2)', null);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reference-nodes?reference_id=' . $referenceId);

        $response->assertOk()
            ->assertJsonPath('data.0.reference.id', $referenceId)
            ->assertJsonPath('data.0.reference.title', 'Filipenses - Semana 2 (Capitulo 2)');
    }

    public function test_show_includes_nested_reference_payload_with_id_and_title(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'ref-nodes@example.com')->value('id');

        $referenceId = $this->createReference($userId, 'BOOK', 'Filipenses - Semana 2 (Capitulo 2)');
        $nodeId = $this->createReferenceNode($referenceId, 'CHAPTER', 'Capitulo 2', null);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reference-nodes/' . $nodeId);

        $response->assertOk()
            ->assertJsonPath('data.id', $nodeId)
            ->assertJsonPath('data.reference.id', $referenceId)
            ->assertJsonPath('data.reference.title', 'Filipenses - Semana 2 (Capitulo 2)');
    }

    private function authenticateAndGetToken(): string
    {
        $user = User::factory()->create([
            'email' => 'ref-nodes@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678',
        ])->assertOk();

        return (string) $response->json('access_token');
    }

    private function createReference(int $userId, string $type, string $title): int
    {
        return (int) DB::table('references')->insertGetId([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createReferenceNode(int $referenceId, string $type, ?string $label, ?int $parentNodeId): int
    {
        return (int) DB::table('reference_nodes')->insertGetId([
            'reference_id' => $referenceId,
            'type' => $type,
            'label' => $label,
            'parent_node_id' => $parentNodeId,
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createReferencesSchema(): void
    {
        if (!Schema::hasTable('references')) {
            Schema::create('references', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('type', 50);
                $table->string('title', 255);
                $table->string('abbreviation', 50)->nullable();
                $table->string('author', 255)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createReferenceNodesSchema(): void
    {
        if (!Schema::hasTable('reference_nodes')) {
            Schema::create('reference_nodes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('type', 50);
                $table->text('content')->nullable();
                $table->string('label', 255)->nullable();
                $table->unsignedBigInteger('reference_id');
                $table->unsignedBigInteger('parent_node_id')->nullable();
                $table->integer('position')->default(0);
                $table->timestamps();
            });
        }
    }
}
