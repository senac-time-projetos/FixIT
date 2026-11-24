# Banco de dados do FixIT

## Modelo implementado

`database.sql` cria uma instalação limpa em MySQL/MariaDB com `utf8mb4`, InnoDB,
chaves estrangeiras, restrições e índices.

| Tabela | Responsabilidade |
| --- | --- |
| `usuarios` | Identidade, hash de senha, perfil e situação |
| `categorias` | Classificação ativa/inativa dos chamados |
| `chamados` | Solicitação, prioridade, status, técnico e solução |
| `comentarios` | Mensagens com chamado, autor e data |
| `historico_chamados` | Eventos imutáveis do ciclo de atendimento |

O número auto incremental de `chamados` é o identificador público apresentado na
interface. Usuários e categorias são inativados; não são apagados.

## Instalação limpa

Para usar MySQL 8.4 em um container isolado na porta `3307`, siga o
[guia do ambiente Docker](../docs/SETUP_DOCKER.md). O compose executa a
instalação limpa e o seed automaticamente na criação do volume.

No phpMyAdmin, importe `database/database.sql`. Pela linha de comando, a partir da
raiz do repositório:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root < database\database.sql
```

Se o usuário tiver senha, use `-p` e informe-a no prompt. Não coloque a senha no
comando, no histórico ou no repositório.

## Base que já importou o template

Faça backup e execute no cliente `mysql`:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root < database\migrations\001_create_fixit_schema.sql
```

A migration cria o modelo FixIT e preserva a tabela genérica `users`. Ela não
copia essa tabela porque não existe um contrato confiável para perfil e situação.
Revise os dados antes de planejar sua remoção em outra migration.

## Dados fictícios opcionais

Depois do esquema, importe `database/seeds/demo.sql`. As contas usam endereços
`.test` e hashes bcrypt; nenhum hash deve ser substituído por senha em texto puro.

| Perfil | E-mail | Senha local de demonstração |
| --- | --- | --- |
| Colaborador | `mariana@fixit.test` | `FixIT@Colab2026` |
| Técnico | `douglas@fixit.test` | `FixIT@Tecnico2026` |
| Administrador | `admin@fixit.test` | `FixIT@Admin2026` |

Essas credenciais são exclusivamente fictícias. Altere ou remova as contas antes
de qualquer ambiente compartilhado.

O teste `php tests/docker_mysql_test.php` valida as cinco tabelas, o seed, um
login real com bcrypt, o charset e rollback contra o MySQL do container.

## Recuperação

Antes de migration, exporte o banco no phpMyAdmin ou com `mysqldump`. Em falha,
restaure o backup. Não use `DROP TABLE` em uma base com chamados, comentários ou
histórico sem uma migration revisada e um plano explícito de preservação.
