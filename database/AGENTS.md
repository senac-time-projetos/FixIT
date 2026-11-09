# Instruções para banco de dados

Estas regras complementam o `AGENTS.md` da raiz para arquivos em `database/`.

- MySQL com `utf8mb4` é o banco suportado.
- `database.sql` representa instalação nova; mudanças incrementais entram em
  `database/migrations/`.
- Toda migration deve ter objetivo, ordem clara e estratégia de reversão ou
  recuperação documentada.
- Use chaves estrangeiras, índices e restrições compatíveis com as regras de
  negócio; não dependa apenas da interface.
- Preserve chamados, comentários e histórico. Exclusões destrutivas exigem
  decisão explícita na spec.
- Seeds devem usar somente dados fictícios e hashes de senha gerados com PHP.
- Mudanças de esquema devem vir no mesmo PR do código que depende delas ou em uma
  tarefa predecessora explicitamente vinculada.
- Teste instalação limpa e evolução de base existente quando houver migration.
