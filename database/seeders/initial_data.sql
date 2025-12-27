-- Initial seed data for api-reference
-- Inserts users, channels (including 'bible'), references, reference_nodes and channel members

BEGIN;

-- Users
INSERT INTO users (name, email, password, created_at, updated_at)
VALUES
  ('Pablo Silva', 'pablo@example.com', NULL, now(), now()),
  ('Igreja do Caminho', 'igreja@example.com', NULL, now(), now()),
  ('Alice Mendes', 'alice@example.com', NULL, now(), now()),
  ('Bob Santos', 'bob@example.com', NULL, now(), now());

-- References (works/sources)
INSERT INTO "references" (type, title, abbreviation, author, description, created_at, updated_at)
VALUES
  ('BOOK', 'Romanos', 'ROM', 'Paulo', 'Epístola aos Romanos (sample)', now(), now()),
  ('BOOK', 'Filipenses', 'FIL', 'Paulo', 'Epístola aos Filipenses (sample)', now(), now()),
  ('BOOK', 'Salmos', 'SLM', 'Vários', 'Livro de Salmos (sample)', now(), now()),
  ('BOOK', 'Mateus', 'MAT', 'Mateus', 'Evangelho segundo Mateus (sample)', now(), now()),

-- Channels (created_by references user emails inserted above)
INSERT INTO channels (name, created_by, description, created_at, updated_at)
VALUES
  ('Série em romanos', (SELECT id FROM users WHERE email = 'igreja@example.com'), 'Série devocional sobre Romanos', now(), now()),
  ('Série em filipenses', (SELECT id FROM users WHERE email = 'pablo@example.com'), 'Série devocional sobre Filipenses', now(), now());

-- Link new references to new channels
INSERT INTO channel_references (channel_id, reference_id, created_at, updated_at)
VALUES
  ((SELECT id FROM channels WHERE name = 'Série em romanos'), (SELECT id FROM "references" WHERE title = 'Romanos'), now(), now()),
  ((SELECT id FROM channels WHERE name = 'Série em filipenses'), (SELECT id FROM "references" WHERE title = 'Filipenses'), now(), now());

INSERT INTO reference_nodes (type, content, label, reference_id, parent_node_id, position, created_at, updated_at)
VALUES
  ('BOOK', 'Romanos', 'Romanos', (SELECT id FROM "references" WHERE title = 'Romanos'), NULL, 1, now(), now()),
  ('SECTION', 'Paul, a servant of Christ Jesus, called as an apostle, set apart for the gospel of God... (Romanos 1:1-10 sample text)', 'Romanos 1:1-10', (SELECT id FROM "references" WHERE title = 'Romanos'), (SELECT id FROM reference_nodes WHERE label = 'Romanos' AND reference_id = (SELECT id FROM "references" WHERE title = 'Romanos') LIMIT 1), 1, now(), now()),
  ('BOOK', 'Filipenses', 'Filipenses', (SELECT id FROM "references" WHERE title = 'Filipenses'), NULL, 1, now(), now()),
  ('SECTION', 'Rejoice in the Lord always. I will say it again: Rejoice! Let your gentleness be evident to all. (Philippians 4:1-10 sample text)', 'Filipenses 4:1-10', (SELECT id FROM "references" WHERE title = 'Filipenses'), (SELECT id FROM reference_nodes WHERE label = 'Filipenses' AND reference_id = (SELECT id FROM "references" WHERE title = 'Filipenses') LIMIT 1), 1, now(), now()),
  ('BOOK', 'Salmos', 'Salmos', (SELECT id FROM "references" WHERE title = 'Salmos'), NULL, 1, now(), now()),
  ('SECTION', 'Salmo 23: O Senhor é o meu pastor; nada me faltará...', 'Salmos 23:1', (SELECT id FROM "references" WHERE title = 'Salmos'), (SELECT id FROM reference_nodes WHERE label = 'Salmos' AND reference_id = (SELECT id FROM "references" WHERE title = 'Salmos') LIMIT 1), 1, now(), now()),
  ('BOOK', 'Mateus', 'Mateus', (SELECT id FROM "references" WHERE title = 'Mateus'), NULL, 1, now(), now()),
  ('SECTION', 'Mateus 5: Blessed are the poor in spirit... (sample text)', 'Mateus 5:1-12', (SELECT id FROM "references" WHERE title = 'Mateus'), (SELECT id FROM reference_nodes WHERE label = 'Mateus' AND reference_id = (SELECT id FROM "references" WHERE title = 'Mateus') LIMIT 1), 1, now(), now());

-- Notes linked to Romanos and Filipenses reference nodes
INSERT INTO notes (user_id, content, reference_node_id, visibility, created_at, updated_at)
VALUES
  ((SELECT id FROM users WHERE email = 'igreja@example.com'), 'Reflexão sobre Romanos 1:1-10 — conteúdo exemplar', (SELECT id FROM reference_nodes WHERE label = 'Romanos 1:1-10' LIMIT 1), 'PUBLIC', now(), now()),
  ((SELECT id FROM users WHERE email = 'pablo@example.com'), 'Aplicação prática de Filipenses 4:1-10 — conteúdo exemplar', (SELECT id FROM reference_nodes WHERE label = 'Filipenses 4:1-10' LIMIT 1), 'PUBLIC', now(), now());

-- Additional sample notes for new references
INSERT INTO notes (user_id, content, reference_node_id, visibility, created_at, updated_at)
VALUES
  ((SELECT id FROM users WHERE email = 'alice@example.com'), 'Conforto no Salmo 23 — reflexão curta', (SELECT id FROM reference_nodes WHERE label = 'Salmos 23:1' LIMIT 1), 'PUBLIC', now(), now()),
  ((SELECT id FROM users WHERE email = 'bob@example.com'), 'Sermão sobre Mateus 5 — bem-aventuranças', (SELECT id FROM reference_nodes WHERE label = 'Mateus 5:1-12' LIMIT 1), 'PUBLIC', now(), now());

-- Track the reference nodes added to notes
INSERT INTO note_reference_added (note_id, reference_node_id, user_id, created_at, updated_at)
VALUES
  ((SELECT id FROM notes WHERE content LIKE 'Reflexão sobre Romanos%' LIMIT 1), (SELECT id FROM reference_nodes WHERE label = 'Romanos 1:1-10' LIMIT 1), (SELECT id FROM users WHERE email = 'igreja@example.com'), now(), now()),
  ((SELECT id FROM notes WHERE content LIKE 'Aplicação prática de Filipenses%' LIMIT 1), (SELECT id FROM reference_nodes WHERE label = 'Filipenses 4:1-10' LIMIT 1), (SELECT id FROM users WHERE email = 'pablo@example.com'), now(), now());


-- Members for new channels
INSERT INTO channel_members (channel_id, user_id, role, created_at, updated_at)
VALUES
  ((SELECT id FROM channels WHERE name = 'Série em romanos'), (SELECT id FROM users WHERE email = 'igreja@example.com'), 'ADMIN', now(), now()),
  ((SELECT id FROM channels WHERE name = 'Série em romanos'), (SELECT id FROM users WHERE email = 'pablo@example.com'), 'MEMBER', now(), now()),
  ((SELECT id FROM channels WHERE name = 'Série em filipenses'), (SELECT id FROM users WHERE email = 'pablo@example.com'), 'ADMIN', now(), now()),
  ((SELECT id FROM channels WHERE name = 'Série em filipenses'), (SELECT id FROM users WHERE email = 'alice@example.com'), 'MEMBER', now(), now());


COMMIT;
