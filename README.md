# api-reference

API para gerenciar referências e notas vinculadas a nós de referência.

Visão geral

Esta API mantém um modelo simples e genérico de referências (por exemplo: BÍblia, música, poema, livro, sermão) e seus nós estruturados (livro, capítulo, verso, página, estrofe, linha, parágrafo, etc.). Usuários podem criar notas vinculadas a um nó de referência, publicar notas como privadas, públicas ou associadas a canais.

Principais conceitos:
- Users: perfis de usuário e autenticação
- References: metadados da obra/fonte
- ReferenceNodes: estrutura hierárquica dentro de uma referência (nós)
- Notes: anotações apontando para um ReferenceNode
- Libraries: coleções pessoais de referências
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

libraries
- id
- user_id
- name

library_items
- library_id
- reference_id

channels
- id
- name
- created_by (user)
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

Como contribuir
1. Configure o `.env` com variáveis locais
2. Instale dependências: `composer install`
3. Gere e rode migrations: `php artisan migrate`
4. Inicie o servidor: `php artisan serve`

Contato
Pablo Fabrício - fabriciopablo2000@gmail.com
- channel_id
