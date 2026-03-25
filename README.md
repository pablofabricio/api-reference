# api-reference

API para gerenciar referências e notas vinculadas a nós de referência.

Visão geral

Esta API mantém um modelo simples e genérico de referências (por exemplo: BÍblia, música, poema, livro, sermão) e seus nós estruturados (livro, capítulo, verso, página, estrofe, linha, parágrafo, etc.). Usuários podem criar notas vinculadas a um nó de referência, publicar notas como privadas, públicas ou associadas a canais.

Principais conceitos:
- Users: perfis de usuário e autenticação
- References: metadados da obra/fonte
- ReferenceNodes: estrutura hierárquica dentro de uma referência (nós)
- Notes: anotações apontando para um ReferenceNode
- Channels: espaços temáticos com membros e referências associadas

Modelo de dados (resumo):
Users
- id
- name
- email
- password_hash

notes
- id
- user_id
- content
- reference_node_id
- visibility (PRIVATE | PUBLIC | CHANNEL)

references
- id
- type (BIBLE | MUSIC | POEM | BOOK | SERMON)
- title
- abbreviation
- author
- description

reference_nodes
- id
- type (BOOK | CHAPTER | VERSE | PAGE | SESSION | STANZA | LINE | PARAGRAPH)
- content
- label
- reference_id
- parent_node_id (nullable)
- position
(reference_id, parent_node_id, position) UNIQUE

channels
- id
- name
- created_by (user)
- visibility (PRIVATE | PUBLIC)
- description

channel_references
- id
- channel_id
- reference_id

channel_members
- channel_id
- user_id
- role (OWNER | MODERATOR | MEMBER)
- joined_at
(channel_id, user_id) UNIQUE

Regra de canais e membros
- Um canal pode ser PRIVATE ou PUBLIC.
- Para entrar em um canal, o usuario deve seguir o canal.
- Apenas canais PUBLIC podem ser seguidos.
- Ao seguir um canal, a API cria membership com role MEMBER automaticamente.

Executando com Docker

1. Subir os containers

```bash
docker compose up -d --build
```

2. Criar arquivo de ambiente e chaves (uma vez)

```bash
cp .env.example .env
docker exec -it api-reference-php php artisan key:generate --force
docker exec -it api-reference-php php artisan jwt:secret --force
```

3. Criar schema e popular dados

```bash
./scripts/apply_ddl.sh
./scripts/apply_seed.sh
```

Se o banco local estiver com drift de migrations (tabelas existentes e historico inconsistente), use:

```bash
./scripts/reconcile_and_migrate.sh
```

4. Acessar a API

- Base URL: http://localhost:8000/api

Comandos uteis dentro do container

```bash
docker exec -it api-reference-php composer install
docker exec -it api-reference-php php artisan route:list
docker exec -it api-reference-php php artisan test
docker exec -it api-reference-php ./vendor/bin/phpunit
```

Testando no Postman (usuario padrao)

- Email: pablo@example.com
- Senha: 12345678

Login

- Metodo: POST
- URL: http://localhost:8000/api/auth/login
- Headers: Content-Type: application/json
- Body:

```json
{
	"email": "pablo@example.com",
	"password": "12345678"
}
```

Ao fazer login, use o access_token retornado no header Authorization:

```text
Bearer <access_token>
```

Exemplo de seguir canal publico

- Metodo: POST
- URL: http://localhost:8000/api/channel-members
- Headers: Authorization: Bearer <access_token>
- Body:

```json
{
	"channel_id": 1
}
```

Contato
Pablo Fabrício - fabriciopablo2000@gmail.com
