<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Clean up existing minimal data to avoid duplicates in repeated runs
    DB::statement('DELETE FROM note_reference_added');
    DB::statement('DELETE FROM notes');
    DB::statement('DELETE FROM channel_members');
    DB::statement('DELETE FROM channel_references');
    DB::statement('DELETE FROM reference_nodes');
    // "references" is a reserved word in Postgres, quote it explicitly
    DB::statement('DELETE FROM "references"');
    DB::statement('DELETE FROM channels');
    DB::statement('DELETE FROM users');

        // Users
        $users = [
            ['name' => 'Pablo Silva', 'email' => 'pablo@example.com'],
            ['name' => 'Igreja do Caminho', 'email' => 'igreja@example.com'],
            ['name' => 'Alice Mendes', 'email' => 'alice@example.com'],
            ['name' => 'Bob Santos', 'email' => 'bob@example.com'],
        ];

        foreach ($users as $u) {
            DB::table('users')->insert([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make('123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // References
        $refs = [
            ['type' => 'BOOK', 'title' => 'Romanos', 'abbreviation' => 'ROM', 'author' => 'Paulo', 'description' => 'Epístola aos Romanos (sample)'],
            ['type' => 'BOOK', 'title' => 'Filipenses', 'abbreviation' => 'FIL', 'author' => 'Paulo', 'description' => 'Epístola aos Filipenses (sample)'],
            ['type' => 'BOOK', 'title' => 'Salmos', 'abbreviation' => 'SLM', 'author' => 'Vários', 'description' => 'Livro de Salmos (sample)'],
            ['type' => 'BOOK', 'title' => 'Mateus', 'abbreviation' => 'MAT', 'author' => 'Mateus', 'description' => 'Evangelho segundo Mateus (sample)'],
            ['type' => 'BOOK', 'title' => 'Bible - King James', 'abbreviation' => 'KJV', 'author' => 'Various', 'description' => 'King James Version (sample)'],
            ['type' => 'BOOK', 'title' => 'Dev Notes Collection', 'abbreviation' => 'DEV', 'author' => 'Team', 'description' => 'Development references'],
        ];

        foreach ($refs as $r) {
            DB::table('references')->insert([
                'type' => $r['type'],
                'title' => $r['title'],
                'abbreviation' => $r['abbreviation'],
                'author' => $r['author'],
                'description' => $r['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Channels for series
        $channels = [
            ['name' => 'Série em romanos', 'owner_email' => 'igreja@example.com'],
            ['name' => 'Série em filipenses', 'owner_email' => 'pablo@example.com'],
            ['name' => 'Série em salmos', 'owner_email' => 'alice@example.com'],
            ['name' => 'Série em mateus', 'owner_email' => 'bob@example.com'],
        ];

        foreach ($channels as $c) {
            $ownerId = DB::table('users')->where('email', $c['owner_email'])->value('id');
            DB::table('channels')->insert([
                'name' => $c['name'],
                'created_by' => $ownerId,
                'description' => 'Série devocional - ' . $c['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Link references to channels and create reference_nodes and notes (simplified)
        $this->seedReferenceNodesAndNotes('Romanos', 'Série em romanos', 'Paul, a servant of Christ Jesus, called as an apostle... (Romans sample)');
        $this->seedReferenceNodesAndNotes('Filipenses', 'Série em filipenses', 'Rejoice in the Lord always... (Philippians sample)');
        $this->seedReferenceNodesAndNotes('Salmos', 'Série em salmos', 'O Senhor é o meu pastor; nada me faltará... (Salmos 23 sample)');
        $this->seedReferenceNodesAndNotes('Mateus', 'Série em mateus', 'Bem-aventuranças... (Mateus 5 sample)');

        // Add members: make owners admins
        foreach ($channels as $c) {
            $channelId = DB::table('channels')->where('name', $c['name'])->value('id');
            $ownerId = DB::table('users')->where('email', $c['owner_email'])->value('id');
            DB::table('channel_members')->insert([
                'channel_id' => $channelId,
                'user_id' => $ownerId,
                'role' => 'ADMIN',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Additional members: ensure Pablo is member of other channels and add extra members
        $pabloId = DB::table('users')->where('email', 'pablo@example.com')->value('id');
        $aliceId = DB::table('users')->where('email', 'alice@example.com')->value('id');
        $bobId = DB::table('users')->where('email', 'bob@example.com')->value('id');

        foreach ($channels as $c) {
            $channelId = DB::table('channels')->where('name', $c['name'])->value('id');

            // add Pablo to every channel if not owner
            $ownerId = DB::table('users')->where('email', $c['owner_email'])->value('id');
            if ($pabloId && $ownerId && $pabloId !== $ownerId) {
                DB::table('channel_members')->updateOrInsert([
                    'channel_id' => $channelId,
                    'user_id' => $pabloId,
                ], [
                    'role' => 'MEMBER',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // add Alice and Bob to some channels for variety
            if ($channelId && $aliceId) {
                DB::table('channel_members')->updateOrInsert([
                    'channel_id' => $channelId,
                    'user_id' => $aliceId,
                ], [
                    'role' => 'MEMBER',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if ($channelId && $bobId) {
                DB::table('channel_members')->updateOrInsert([
                    'channel_id' => $channelId,
                    'user_id' => $bobId,
                ], [
                    'role' => 'MEMBER',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function seedReferenceNodesAndNotes(string $refTitle, string $channelName, string $nodeContent)
    {
    $refId = DB::table('references')->where('title', $refTitle)->value('id');
        $channelId = DB::table('channels')->where('name', $channelName)->value('id');

        if (!$refId || !$channelId) {
            return;
        }

        // link
        DB::table('channel_references')->insert([
            'channel_id' => $channelId,
            'reference_id' => $refId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // sample section node
        $sectionId = DB::table('reference_nodes')->insertGetId([
            'type' => 'SECTION',
            'content' => $nodeContent,
            'label' => $refTitle . ' sample',
            'reference_id' => $refId,
            'parent_node_id' => null,
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    // create a sample note by the channel owner
    $ownerUserId = DB::table('channels')->where('id', $channelId)->value('created_by');
    $userId = DB::table('users')->where('id', $ownerUserId)->value('id');

        if ($userId) {
            $noteId = DB::table('notes')->insertGetId([
                'user_id' => $userId,
                'content' => 'Nota de exemplo sobre ' . $refTitle,
                'reference_node_id' => $sectionId,
                'visibility' => 'PUBLIC',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('note_reference_added')->insert([
                'note_id' => $noteId,
                'reference_node_id' => $sectionId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
