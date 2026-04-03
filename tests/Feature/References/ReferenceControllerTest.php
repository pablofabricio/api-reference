<?php

namespace Tests\Feature\References;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createReferencesSchema();
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/references', [
            'type' => 'BOOK',
            'title' => 'Romanos',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['error']);
    }

    public function test_store_creates_reference_successfully(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'refs@example.com')->value('id');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/references', [
                'type'        => 'BOOK',
                'title'       => 'Romanos',
                'author'      => 'Paulo',
                'description' => 'Carta paulina',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'BOOK')
            ->assertJsonPath('data.title', 'Romanos')
            ->assertJsonPath('data.author', 'Paulo')
            ->assertJsonPath('data.user_id', $userId);

        $this->assertDatabaseHas('references', [
            'title'   => 'Romanos',
            'type'    => 'BOOK',
            'user_id' => $userId,
        ]);
    }

    public function test_store_returns_validation_error_when_type_is_missing(): void
    {
        $token = $this->authenticateAndGetToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/references', [
                'title' => 'Salmos',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Validation error')
            ->assertJsonStructure(['errors' => ['type']]);
    }

    public function test_store_returns_validation_error_when_title_is_missing(): void
    {
        $token = $this->authenticateAndGetToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/references', [
                'type' => 'BOOK',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Validation error')
            ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_show_returns_reference_for_owner(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'refs@example.com')->value('id');
        $refId = $this->createReference($userId, 'BOOK', 'Genesis');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/references/' . $refId);

        $response->assertOk()
            ->assertJsonPath('data.id', $refId)
            ->assertJsonPath('data.title', 'Genesis');
    }

    public function test_show_denies_access_to_another_users_reference(): void
    {
        $owner = User::factory()->create([
            'email'    => 'refs-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $other = User::factory()->create([
            'email'    => 'refs-other@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $refId = $this->createReference((int) $owner->id, 'BOOK', 'Exodo');

        $login = $this->postJson('/api/auth/login', [
            'email'    => $other->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/references/' . $refId)
            ->assertStatus(403);
    }

    public function test_update_changes_reference_data(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'refs@example.com')->value('id');
        $refId = $this->createReference($userId, 'BOOK', 'Levitico');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/references/' . $refId, [
                'type'  => 'SERMON',
                'title' => 'Levitico Atualizado',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'SERMON')
            ->assertJsonPath('data.title', 'Levitico Atualizado');

        $this->assertDatabaseHas('references', [
            'id'    => $refId,
            'type'  => 'SERMON',
            'title' => 'Levitico Atualizado',
        ]);
    }

    public function test_update_denies_access_to_another_users_reference(): void
    {
        $owner = User::factory()->create([
            'email'    => 'refs-upd-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $other = User::factory()->create([
            'email'    => 'refs-upd-other@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $refId = $this->createReference((int) $owner->id, 'BOOK', 'Numeros');

        $login = $this->postJson('/api/auth/login', [
            'email'    => $other->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/references/' . $refId, [
                'type'  => 'BOOK',
                'title' => 'Hackeado',
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('references', ['title' => 'Hackeado']);
    }

    public function test_destroy_deletes_reference(): void
    {
        $token = $this->authenticateAndGetToken();
        $userId = (int) User::where('email', 'refs@example.com')->value('id');
        $refId = $this->createReference($userId, 'BOOK', 'Deuteronomio');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/references/' . $refId)
            ->assertNoContent();

        $this->assertDatabaseMissing('references', ['id' => $refId]);
    }

    public function test_destroy_denies_access_to_another_users_reference(): void
    {
        $owner = User::factory()->create([
            'email'    => 'refs-del-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $other = User::factory()->create([
            'email'    => 'refs-del-other@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $refId = $this->createReference((int) $owner->id, 'BOOK', 'Josue');

        $login = $this->postJson('/api/auth/login', [
            'email'    => $other->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/references/' . $refId)
            ->assertStatus(403);

        $this->assertDatabaseHas('references', ['id' => $refId]);
    }

    public function test_index_returns_only_authenticated_user_references(): void
    {
        $owner = User::factory()->create([
            'email'    => 'refs-idx-owner@example.com',
            'password' => Hash::make('12345678'),
        ]);
        $other = User::factory()->create([
            'email'    => 'refs-idx-other@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $this->createReference((int) $owner->id, 'BOOK', 'Juizes');
        $this->createReference((int) $other->id, 'BOOK', 'Rute');

        $login = $this->postJson('/api/auth/login', [
            'email'    => $owner->email,
            'password' => '12345678',
        ])->assertOk();

        $token = (string) $login->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/references');

        $response->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->all();
        $this->assertContains('Juizes', $titles);
        $this->assertNotContains('Rute', $titles);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function authenticateAndGetToken(): string
    {
        $user = User::factory()->create([
            'email'    => 'refs@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => '12345678',
        ])->assertOk();

        return (string) $response->json('access_token');
    }

    private function createReference(int $userId, string $type, string $title): int
    {
        return (int) DB::table('references')->insertGetId([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
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
}
