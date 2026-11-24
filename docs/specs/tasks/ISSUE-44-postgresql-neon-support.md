# ISSUE-44 — Adicionar PostgreSQL hospedado como banco compartilhado

Status: Concluída

Issue: `#44`

Trello: `[P1] Migrar banco compartilhado para PostgreSQL Neon`

Entrega: PR `#45`, integrado à `main` em 24/11/2026.

Evidências finais: schema e seed aplicados no Neon; cinco tabelas e conta
administrativa confirmadas; login administrativo, cadastros e ciclo completo de
chamado aprovados com rollback e sem resíduos; regressões SQLite/MySQL aprovadas.

## Resultado

O FixIT aceitará uma conexão PostgreSQL hospedada por `DATABASE_URL`, com TLS,
schema e seed reproduzíveis. Credenciais reais permanecerão fora do Git. O MySQL
Docker continuará disponível como ambiente local durante a transição.

## Conexão

- `DATABASE_URL` tem precedência sobre `backend/config/database.php`.
- A URL deve usar `postgres://` ou `postgresql://` e fornecer host, banco e
  usuário.
- `sslmode=require` é o padrão do ambiente hospedado.
- A aplicação falha com mensagem de configuração, sem imprimir usuário, senha ou
  URL.

## Schema e dados iniciais

- PostgreSQL mantém as cinco entidades, vínculos, checks, índices e timestamps do
  modelo canônico.
- A instalação é idempotente e não remove tabelas ou dados existentes.
- O seed usa `ON CONFLICT`, senhas bcrypt fictícias e inclui colaborador,
  técnico, administrador e categorias do ambiente acadêmico.
- A conta administrativa inicial é `admin@fixit.test`; sua senha de demonstração
  deve ser trocada após o primeiro acesso ao ambiente compartilhado.

## Compatibilidade

- Inserts retornam IDs corretamente em PostgreSQL, MySQL e SQLite.
- O pooler transacional do Neon não depende de prepared statements de sessão;
  parâmetros continuam separados pelo PDO com emulação no driver PostgreSQL.
- Consultas preparadas, transações e regras de autorização permanecem iguais.
- MySQL Docker continua como fallback local até a migração ser validada.

## Critérios de aceite

- [x] PHP carrega `pdo_pgsql` no ambiente de desenvolvimento.
- [x] `DATABASE_URL` PostgreSQL é convertida em DSN sem expor segredos.
- [x] Schema PostgreSQL cria as cinco tabelas, checks, índices e FKs.
- [x] Seed PostgreSQL inclui administrador com hash válido.
- [x] Repositórios retornam IDs após inserts em PostgreSQL.
- [x] Guia documenta configuração, provisionamento e rotação de credencial.
- [x] Testes locais continuam aprovados em SQLite e MySQL.
- [x] Teste hospedado é executado quando `DATABASE_URL` estiver definida.

Evidências: provisionamento, seed e administrador passaram em PostgreSQL 16
temporário e no Neon. Login administrativo, cadastros e fluxo completo de
chamados também passaram no Neon, com rollback e sem resíduos. O pooler
transacional foi validado com parâmetros emulados pelo PDO PostgreSQL.

## Segurança

- Nunca versionar `.env`, `database.php`, URL, senha ou dump hospedado.
- Nunca registrar a URL completa em mensagens ou logs.
- Regenerar qualquer credencial compartilhada por canal inadequado.
