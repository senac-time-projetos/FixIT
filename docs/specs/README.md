# Especificações do FixIT

As specs são os contratos versionados do produto. Elas complementam o Notion e o
Figma com comportamento verificável e mantêm implementação, testes e revisão
alinhados.

## Documentos canônicos

- [Produto e MVP](PRODUCT_SPEC.md)
- [Frontend do MVP](FRONTEND_MVP_SPEC.md)
- [Template de tarefa](TASK_SPEC_TEMPLATE.md)

Specs de tarefas devem ser criadas em `docs/specs/tasks/` com o formato
`ISSUE-<numero>-<slug>.md` quando a mudança for funcional, envolver risco
relevante, alterar contratos ou exigir várias etapas. Ajustes documentais simples
podem usar apenas a Issue e o PR.

Uma spec não comprova implementação. O estado executado e as evidências ficam na
Issue e no PR; a spec registra o contrato e as decisões que precisam sobreviver.
