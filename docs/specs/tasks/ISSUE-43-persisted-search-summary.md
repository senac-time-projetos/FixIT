# ISSUE-43 — Integrar busca, filtros e resumo persistidos

Status: Concluída

Issue: `#43`

Trello: `[P2] Implementar busca, filtros e resumo`

Entrega: PR `#48`, integrado à `main` em 24/11/2026.

Evidências finais: suíte completa aprovada; busca, filtros e agregações validados
no MySQL Docker e PostgreSQL Neon com rollback e sem dados residuais.

Dependências: Issues `#29`, `#35` e `#39`.

## Resultado

A busca do colaborador será executada no repositório sem perder a restrição por
solicitante. O resumo administrativo exibirá agregações reais do MySQL por
status, categoria e prioridade.

## Regras

- Colaborador busca apenas por número ou título e combina a busca com status.
- A consulta sempre inclui `solicitante_id` derivado da sessão.
- Técnico e administrador mantêm busca por número, título ou solicitante e
  filtros de status, prioridade e categoria.
- O resumo administrativo considera todos os chamados e é acessível somente ao
  administrador.
- Totais são calculados por consultas agregadas, sem tabela de dashboard.
- Categorias inativas continuam aparecendo no resumo histórico quando possuem
  chamados vinculados.

## Critérios de aceite

- [x] Busca do colaborador é aplicada no banco e não expõe outro solicitante.
- [x] Status do colaborador combina com a busca persistida.
- [x] Filtros técnicos permanecem combináveis e parametrizados.
- [x] Totais por status vêm do banco e correspondem ao total geral.
- [x] Distribuições por categoria e prioridade vêm do banco.
- [x] Usuário não administrador não consulta o resumo.
- [x] Estados vazios e fixtures visuais permanecem aprovados.
- [x] Fluxo passa no MySQL Docker sem deixar dados residuais.

Evidências adicionais: busca, filtros e agregações também passaram no PostgreSQL
Neon com rollback e sem dados residuais.

## Fora do escopo

- Gráficos, SLA e tendências por período.
- Paginação avançada e exportação.
