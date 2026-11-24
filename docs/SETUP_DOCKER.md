# Ambiente local com Docker

Use este ambiente quando o MySQL/MariaDB do XAMPP estiver indisponível. O
container usa MySQL 8.4, porta `3307` e volume próprio; nenhum arquivo do XAMPP é
alterado.

## Pré-requisitos

- Docker Desktop iniciado.
- PHP local com `pdo_mysql` habilitado.
- Porta `3307` livre, ou outra porta definida em `FIXIT_DB_DOCKER_PORT`.

## Iniciar

Na raiz do projeto:

```powershell
docker compose up -d db
docker compose ps
Copy-Item backend/config/database.docker.example.php backend/config/database.php
php tests/docker_mysql_test.php
```

Na primeira execução, `database/database.sql` e `database/seeds/demo.sql` são
aplicados automaticamente. O arquivo `backend/config/database.php` é local e não
deve ser versionado.

Depois, mantenha o Apache do XAMPP ligado e acesse a aplicação normalmente. O
PHP do XAMPP conversa com o MySQL do Docker por `127.0.0.1:3307`.

## Comandos úteis

```powershell
# Estado e saúde
docker compose ps

# Logs do banco
docker compose logs db

# Parar sem perder os dados
docker compose stop db

# Iniciar novamente
docker compose start db

# Parar e remover somente o container/rede; o volume permanece
docker compose down
```

## Recriar uma instalação limpa

Esta operação apaga apenas o volume Docker `fixit_mysql_data`. Faça isso somente
quando os dados locais puderem ser descartados:

```powershell
docker compose down --volumes
docker compose up -d db
```

Não remova nem renomeie arquivos de `C:\xampp\mysql\data` como parte deste
procedimento.

## Porta alternativa

Se `3307` estiver ocupada, defina uma porta antes de iniciar e ajuste o mesmo
valor em `backend/config/database.php`:

```powershell
$env:FIXIT_DB_DOCKER_PORT = '3308'
docker compose up -d db
```
