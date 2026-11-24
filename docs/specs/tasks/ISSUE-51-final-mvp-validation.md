# ISSUE-51 — Executar validação final do MVP

Status: Concluída

Issue: `#51`

Trello: `[P2] Executar testes da aplicação`

Entrega: PR `#52`, integrado à `main` em 24/11/2026.

Evidências finais: 61 arquivos PHP no lint, 17 suítes locais sem falhas e três
suítes adicionais aprovadas no Neon, com rollback e sem resíduos.

## Resultado

Uma execução reproduzível consolidará lint, integridade do diff, testes de
unidade/integração e validações nos bancos MySQL e PostgreSQL Neon. O relatório
final separará evidência automatizada de validações humanas ainda pendentes.

## Critérios de aceite

- [x] Login válido, inválido, inativo e acesso sem sessão estão cobertos.
- [x] Permissões entre perfis e isolamento por solicitante estão cobertos.
- [x] Abertura, atribuição, atualização, comentário e encerramento estão cobertos.
- [x] Validações de campos e solução obrigatória estão cobertas.
- [x] Busca, filtros, resumo, usuários e categorias estão cobertos.
- [x] MySQL Docker e PostgreSQL Neon passam sem resíduos.
- [x] PHP lint, `git diff --check` e varredura de segredos passam.
- [x] Estados vazio, erro e apresentação responsiva possuem regressão de fixture.
- [x] Relatório final registra resultados e limitações sem inventar evidências.

## Limite

Teste com pessoas e aprovação do professor dependem de participação externa e
não podem ser substituídos por teste automatizado.
