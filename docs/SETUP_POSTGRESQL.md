# PostgreSQL hospedado (Neon)

O FixIT aceita PostgreSQL por meio da variável de ambiente `DATABASE_URL`. A URL
é segredo de ambiente: não coloque seu valor em arquivo versionado, Issue, PR,
Trello, captura de tela ou log.

## Pré-requisitos

- PHP com extensões `pdo_pgsql` e `pgsql` habilitadas.
- Projeto PostgreSQL/Neon criado.
- URL no formato `postgresql://usuario:senha@host/banco?sslmode=require`.

No PHP portátil usado pelo projeto, confirme:

```powershell
php -m | Select-String -Pattern 'pdo_pgsql|pgsql'
```

## Provisionamento inicial

Defina `DATABASE_URL` somente na sessão atual ou no gerenciador de segredos da
hospedagem. Depois execute:

```powershell
php database/setup_postgresql.php
```

O comando aplica `database/postgresql.sql`, executa o seed acadêmico idempotente
e confirma que `admin@fixit.test` está ativo como administrador. Ele não imprime
a URL nem a senha.

Contas fictícias iniciais:

| Perfil | E-mail | Senha inicial |
| --- | --- | --- |
| Colaborador | `mariana@fixit.test` | `FixIT@Colab2026` |
| Técnico | `douglas@fixit.test` | `FixIT@Tecnico2026` |
| Administrador | `admin@fixit.test` | `FixIT@Admin2026` |

Troque a senha administrativa após o primeiro acesso compartilhado. O seed não
sobrescreve hashes de contas já existentes.

## Execução da aplicação

Quando `DATABASE_URL` está definida, ela tem precedência sobre o arquivo local
`backend/config/database.php`. Sem a variável, o projeto continua usando a
configuração MySQL/XAMPP/Docker já documentada.

URLs com host `-pooler` são suportadas. O PDO usa parâmetros emulados no driver
PostgreSQL para não depender de prepared statements vinculados a uma sessão
física do pool de conexões.

Para validar uma base já provisionada:

```powershell
php tests/postgresql_hosted_test.php
```

## Segurança e recuperação

- Revogue e gere novamente qualquer URL divulgada em chat ou captura.
- Use uma credencial separada por ambiente e o menor privilégio possível.
- Antes de alterações futuras de schema, crie um branch/backup no painel Neon.
- Não execute exclusões físicas de usuários, categorias, chamados ou histórico.
