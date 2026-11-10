# ISSUE-20 — Criar esquema MySQL e camada de persistência PDO

Status: Concluída

Issue: `#20`

Quadro: <https://trello.com/b/ihV3ZVG8/pi-senac-fixit>

## Resultado

Uma instalação limpa criará o banco FixIT com as cinco entidades do MVP,
restrições, índices e dados fictícios opcionais. O backend terá uma conexão PDO
centralizada e repositórios reutilizáveis, sem SQL nas telas.

## Escopo

- `database/database.sql` como instalação limpa e idempotente.
- Migration inicial, aditiva e não destrutiva para a base do template.
- Seed separado com três contas fictícias e categorias de demonstração.
- Configuração por variáveis de ambiente com arquivo local ignorado pelo Git.
- Fábrica de conexão PDO com exceções internas e sem mensagem sensível ao usuário.
- Repositórios de usuários, categorias, chamados, comentários e histórico.
- Métodos de transação para serviços críticos futuros.
- Testes dos contratos com SQLite em memória e validação estrutural do SQL.
- Documentação de instalação, recuperação e credenciais fictícias.

## Fora do escopo

- Sessão, login e autorização por rota.
- Controllers e integração das páginas com dados reais.
- Services de assumir/encerrar chamados.
- Execução automática da migration em produção.

## Modelo de dados

### `usuarios`

`id`, `nome`, `email` único, `senha_hash`, `perfil`, `situacao`, timestamps.

### `categorias`

`id`, `nome` único, `descricao`, `situacao`, timestamps.

### `chamados`

Número público, solicitante, categoria, técnico opcional, título, descrição,
prioridade, status, solução opcional e timestamps de abertura/atualização/
encerramento.

### `comentarios`

Chamado, autor, conteúdo e data de criação. Comentários não são apagados.

### `historico_chamados`

Chamado, autor opcional, evento, descrição, estado anterior/novo e data. O
histórico é append-only na camada pública.

## Restrições e índices

- MySQL InnoDB e `utf8mb4_unicode_ci`.
- Perfis: `colaborador`, `tecnico`, `administrador`.
- Situações: `ativo`, `inativo`.
- Prioridades: `baixa`, `media`, `alta`.
- Status: `aberto`, `em_atendimento`, `encerrado`.
- E-mail e nomes de categoria únicos.
- Índices para solicitante/status/data, técnico/status, categoria/status,
  prioridade/status e relacionamentos de comentários/histórico.
- Chaves estrangeiras usam `RESTRICT` para impedir que cadastros ou chamados com
  histórico sejam removidos; `CASCADE` é limitado à atualização de chaves.

## Contratos da persistência

1. Toda entrada variável usa `PDOStatement` preparado.
2. Repositórios retornam arrays associativos ou `null`, nunca exibem exceções.
3. Métodos de lista aceitam limites e filtros normalizados.
4. `create` retorna o identificador criado.
5. Atualizações e desativações retornam se alguma linha foi alterada.
6. Histórico oferece somente inclusão e consulta.
7. A conexão ativa `ERRMODE_EXCEPTION`, `FETCH_ASSOC` e desabilita emulação de
   prepares no MySQL.
8. Configuração local pode vir de `database.php`; o exemplo lê `FIXIT_DB_*`.

## Segurança e recuperação

- Nenhuma senha em texto puro ou credencial pessoal é versionada.
- Seed contém contas `.test` e hashes gerados por `password_hash()`.
- A migration não remove `users`; uma base com dados exige migração manual antes
  de eliminar a tabela legada.
- Recuperação: restaurar backup e reexecutar apenas migrations ainda não
  aplicadas. A migration inicial pode ser repetida porque usa `IF NOT EXISTS`.

## Critérios de aceite

- [x] Instalação limpa define as cinco tabelas e relações.
- [x] Migration preserva a tabela legada e cria o modelo FixIT.
- [x] Seed não contém senha em texto puro nem dados reais.
- [x] Conexão PDO é centralizada e configurável sem credencial versionada.
- [x] Cinco repositórios usam consultas preparadas.
- [x] Testes cobrem conexão, CRUD base, filtros e histórico append-only.
- [x] Documentação XAMPP descreve instalação e seed.
- [x] Lint, testes e `git diff --check` passam.

## Verificação

- `php tests/database_foundation_test.php`.
- `php -l` nos PHP alterados.
- inspeção de segredos e senhas em texto puro.
- importação manual no MySQL/XAMPP quando o serviço estiver disponível.

## Evidências observadas

- `tests/database_foundation_test.php`: conexão SQLite isolada, chaves
  estrangeiras, CRUD base, filtros, atribuição, comentário, encerramento,
  histórico, resumo, commit e rollback aprovados.
- Os hashes bcrypt do seed foram conferidos com as três senhas fictícias
  documentadas.
- O cliente do XAMPP foi localizado, porém o servidor MySQL local estava parado;
  por isso a importação real permanece como passo reproduzível da documentação e
  não foi declarada como executada.
- PR integrado: `#21`; Issue encerrada: `#20`.
