BEGIN;

TRUNCATE TABLE
  note_reference_added,
  notes,
  channel_members,
  channel_references,
  reference_nodes,
  channels,
  "references",
  users
RESTART IDENTITY CASCADE;

INSERT INTO users (name, email, password, created_at, updated_at)
VALUES
  ('Pablo Silva', 'pablo@example.com', '$2y$10$koiwvC.TJLWJXFeN5.Srqe8ZYiqWy63s/PENXIsHIH.GieqFYAABK', now(), now()),
  ('Igreja do Caminho', 'igreja@example.com', '$2y$10$koiwvC.TJLWJXFeN5.Srqe8ZYiqWy63s/PENXIsHIH.GieqFYAABK', now(), now()),
  ('Alice Mendes', 'alice@example.com', '$2y$10$koiwvC.TJLWJXFeN5.Srqe8ZYiqWy63s/PENXIsHIH.GieqFYAABK', now(), now()),
  ('Bob Santos', 'bob@example.com', '$2y$10$koiwvC.TJLWJXFeN5.Srqe8ZYiqWy63s/PENXIsHIH.GieqFYAABK', now(), now());

INSERT INTO "references" (type, title, abbreviation, author, description, created_at, updated_at)
VALUES
  ('BIBLE', 'Romanos', 'ROM', 'Paulo', 'Epistola aos Romanos (sample)', now(), now()),
  ('BIBLE', 'Filipenses', 'FIL', 'Paulo', 'Epistola aos Filipenses (sample)', now(), now()),
  ('BIBLE', 'Salmos', 'SLM', 'Varios', 'Livro de Salmos (sample)', now(), now()),
  ('BIBLE', 'Mateus', 'MAT', 'Mateus', 'Evangelho segundo Mateus (sample)', now(), now());

INSERT INTO channels (name, created_by, visibility, description, created_at, updated_at)
VALUES
  (
    'Serie em romanos',
    (SELECT id FROM users WHERE email = 'igreja@example.com'),
    'PUBLIC',
    'Serie devocional sobre Romanos',
    now(),
    now()
  ),
  (
    'Serie em filipenses',
    (SELECT id FROM users WHERE email = 'pablo@example.com'),
    'PRIVATE',
    'Serie devocional sobre Filipenses',
    now(),
    now()
  );

INSERT INTO channel_references (channel_id, reference_id, created_at, updated_at)
VALUES
  (
    (SELECT id FROM channels WHERE name = 'Serie em romanos'),
    (SELECT id FROM "references" WHERE title = 'Romanos'),
    now(),
    now()
  ),
  (
    (SELECT id FROM channels WHERE name = 'Serie em filipenses'),
    (SELECT id FROM "references" WHERE title = 'Filipenses'),
    now(),
    now()
  );

WITH rn AS (
  INSERT INTO reference_nodes (type, content, label, reference_id, parent_node_id, position, created_at, updated_at)
  VALUES
    ('BOOK', 'Romanos', 'Romanos', (SELECT id FROM "references" WHERE title = 'Romanos'), NULL, 1, now(), now()),
    ('BOOK', 'Filipenses', 'Filipenses', (SELECT id FROM "references" WHERE title = 'Filipenses'), NULL, 1, now(), now()),
    ('BOOK', 'Salmos', 'Salmos', (SELECT id FROM "references" WHERE title = 'Salmos'), NULL, 1, now(), now()),
    ('BOOK', 'Mateus', 'Mateus', (SELECT id FROM "references" WHERE title = 'Mateus'), NULL, 1, now(), now())
  RETURNING id, label, reference_id
)
INSERT INTO reference_nodes (type, content, label, reference_id, parent_node_id, position, created_at, updated_at)
VALUES
  (
    'CHAPTER',
    'Romanos 1',
    'Romanos 1',
    (SELECT id FROM "references" WHERE title = 'Romanos'),
    (SELECT id FROM rn WHERE label = 'Romanos'),
    1,
    now(),
    now()
  ),
  (
    'CHAPTER',
    'Filipenses 4',
    'Filipenses 4',
    (SELECT id FROM "references" WHERE title = 'Filipenses'),
    (SELECT id FROM rn WHERE label = 'Filipenses'),
    1,
    now(),
    now()
  ),
  (
    'CHAPTER',
    'Salmos 23',
    'Salmos 23',
    (SELECT id FROM "references" WHERE title = 'Salmos'),
    (SELECT id FROM rn WHERE label = 'Salmos'),
    1,
    now(),
    now()
  ),
  (
    'CHAPTER',
    'Mateus 5',
    'Mateus 5',
    (SELECT id FROM "references" WHERE title = 'Mateus'),
    (SELECT id FROM rn WHERE label = 'Mateus'),
    1,
    now(),
    now()
  );

INSERT INTO notes (user_id, content, reference_node_id, visibility, created_at, updated_at)
VALUES
  (
    (SELECT id FROM users WHERE email = 'igreja@example.com'),
    'Reflexao sobre Romanos 1.',
    (SELECT id FROM reference_nodes WHERE label = 'Romanos 1' LIMIT 1),
    'PUBLIC',
    now(),
    now()
  ),
  (
    (SELECT id FROM users WHERE email = 'pablo@example.com'),
    'Aplicacao pratica de Filipenses 4.',
    (SELECT id FROM reference_nodes WHERE label = 'Filipenses 4' LIMIT 1),
    'PUBLIC',
    now(),
    now()
  );

INSERT INTO note_reference_added (note_id, reference_node_id, user_id, created_at, updated_at)
VALUES
  (
    (SELECT id FROM notes WHERE content = 'Reflexao sobre Romanos 1.' LIMIT 1),
    (SELECT id FROM reference_nodes WHERE label = 'Romanos 1' LIMIT 1),
    (SELECT id FROM users WHERE email = 'igreja@example.com'),
    now(),
    now()
  ),
  (
    (SELECT id FROM notes WHERE content = 'Aplicacao pratica de Filipenses 4.' LIMIT 1),
    (SELECT id FROM reference_nodes WHERE label = 'Filipenses 4' LIMIT 1),
    (SELECT id FROM users WHERE email = 'pablo@example.com'),
    now(),
    now()
  );

INSERT INTO channel_members (channel_id, user_id, role, created_at, updated_at)
VALUES
  (
    (SELECT id FROM channels WHERE name = 'Serie em romanos'),
    (SELECT id FROM users WHERE email = 'igreja@example.com'),
    'OWNER',
    now(),
    now()
  ),
  (
    (SELECT id FROM channels WHERE name = 'Serie em romanos'),
    (SELECT id FROM users WHERE email = 'pablo@example.com'),
    'MEMBER',
    now(),
    now()
  ),
  (
    (SELECT id FROM channels WHERE name = 'Serie em filipenses'),
    (SELECT id FROM users WHERE email = 'pablo@example.com'),
    'OWNER',
    now(),
    now()
  ),
  (
    (SELECT id FROM channels WHERE name = 'Serie em filipenses'),
    (SELECT id FROM users WHERE email = 'alice@example.com'),
    'MEMBER',
    now(),
    now()
  );

COMMIT;
