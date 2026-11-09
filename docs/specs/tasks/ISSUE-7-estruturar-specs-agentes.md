# ISSUE-7 — Estruturar specs e instruções dos agentes

Status: Em verificação

Issue: `#7`

Cartão Trello: Não aplicável, conforme decisão do responsável pelo projeto

Responsável: Alexandre Santana / agente de desenvolvimento

## Resultado para o usuário

O projeto passa a ter contratos versionados para produto e frontend, instruções
persistentes por área e um processo verificável para conduzir cada tarefa desde a
Issue até o merge e encerramento.

## Contexto e fontes

- Notion: escopo, persona, requisitos, regras e critérios de aceite.
- Figma: alta fidelidade, estados, manual visual e referências mobile.
- Trello: 20 cartões em `Done`, 12 em `Backlog` e fluxo de listas do projeto.
- Repositório: `README.md`, `docs/PROJECT_DEFINITION.md`,
  `docs/ARQUITETURA.md`, `docs/PLANEJAMENTO.md` e código inicial.
- Issue GitHub `#7`.

## Escopo

- Integrar o planejamento que estava apenas na branch remota de documentação.
- Criar instruções gerais e específicas para frontend, backend, banco e docs.
- Consolidar a spec canônica do produto e a spec detalhada do frontend.
- Criar template de spec por tarefa e processo completo de desenvolvimento.
- Reforçar rastreabilidade e evidências no template de PR.

## Fora do escopo

- Implementar telas, banco, autenticação ou regras de negócio.
- Criar ou alterar cartões Trello para esta tarefa.
- Declarar requisitos funcionais como implementados.

## Segurança, privacidade e recuperação

A mudança é documental e não acessa dados de produção. Não inclui credenciais nem
dados pessoais. A recuperação consiste em reverter o PR; o histórico anterior do
planejamento é preservado pelos commits originais incorporados.

## Plano de implementação

1. Auditar fontes e trabalho concluído.
2. Abrir Issue e branch própria.
3. Integrar os commits pendentes de planejamento.
4. Criar instruções, specs e fluxo.
5. Validar links, formatação, consistência e sintaxe PHP existente.
6. Abrir PR com evidências, revisar e integrar.

## Critérios de aceite

- [x] Existe fonte canônica versionada para requisitos do MVP.
- [x] O frontend possui contrato de telas, estados e qualidade.
- [x] Agentes possuem limites e verificações específicas por área.
- [x] O processo completo da tarefa está documentado.
- [x] O planejamento pendente foi incorporado à branch.
- [x] Validações foram executadas e serão registradas no PR.
- [ ] PR foi revisado e integrado à `main`.

## Verificação e evidências

- `git diff --check`.
- conferência de links Markdown locais;
- `php -l` em todos os PHP versionados;
- revisão do diff e busca por credenciais;
- confirmação de Issue, branch, PR e estado final da `main`.

## Decisões e pendências

- Em 9 de novembro de 2026, foi decidido que specs e instruções de agentes não
  precisam de cartão no Trello.
- O resultado detalhado da validação do DER ainda precisa ser registrado em fonte
  versionada, apesar de o cartão correspondente estar em `Done`.
