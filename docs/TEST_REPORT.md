# Relatório de validação do MVP FixIT

Data: 24/11/2026

## Ambientes

- PHP com PDO SQLite, MySQL e PostgreSQL.
- MySQL 8.4 no Docker em `127.0.0.1:3307`.
- PostgreSQL 16 temporário para compatibilidade de schema.
- PostgreSQL Neon hospedado por conexão TLS e pooler transacional.

## Cobertura executada

Resultado consolidado: 61 arquivos PHP passaram no lint; 17 suítes locais foram
executadas sem falhas; as três suítes dependentes de `DATABASE_URL` passaram no
Neon. MySQL e PostgreSQL confirmaram rollback sem resíduos.

| Área | Evidência automatizada |
| --- | --- |
| Autenticação | credenciais válidas/inválidas, conta inativa, sessão, logout e destino por perfil |
| Autorização | acesso sem sessão, perfil indevido, isolamento do solicitante e vínculo do técnico |
| Chamados | abertura, atribuição concorrente, atualização, comentário, solução e encerramento |
| Histórico | abertura, atribuição, atualização e encerramento em ordem cronológica |
| Administração | usuários, senha em hash, unicidade, auto-desativação, categorias e desativação |
| Consultas | busca, filtros combinados, estados vazios e agregações do resumo |
| Interface | templates do colaborador, técnico e administrador, escape e estruturas mobile |
| Bancos | rollback e ausência de resíduos no MySQL Docker e PostgreSQL Neon |

## Segurança verificada

- Consultas preparadas e IDs retornados nos três drivers.
- Escritas com sessão, autorização e token CSRF nas páginas integradas.
- Senhas armazenadas com `password_hash()` e verificadas com
  `password_verify()`.
- Saída de banco/usuário escapada nos templates.
- Nenhuma URL ou senha Neon versionada.

## Execução

Suíte local completa:

```powershell
php tests/run_all.php
```

Com `DATABASE_URL` definida, validação hospedada adicional:

```powershell
php tests/postgresql_hosted_test.php
php tests/postgresql_application_flow_test.php
php tests/docker_search_summary_test.php
```

## Evidência externa pendente

A validação do protótipo com duas pessoas e a aprovação do professor não foram
executadas por automação. Permanecem no cartão específico e não são declaradas
como concluídas neste relatório.
