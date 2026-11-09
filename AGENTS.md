# Instruções para agentes do FixIT

Estas instruções se aplicam a todo o repositório. Instruções em `AGENTS.md` de
subpastas complementam e podem tornar estas regras mais específicas.

## Objetivo do projeto

O FixIT é uma aplicação web acadêmica para abertura, acompanhamento e gestão de
chamados internos de TI. O MVP usa PHP puro, MySQL, HTML, CSS, JavaScript e
Bootstrap, com três perfis: colaborador, técnico e administrador.

## Fontes de verdade

Antes de planejar ou alterar o projeto, leia, nesta ordem:

1. a Issue da tarefa e a spec vinculada em `docs/specs/tasks/`, quando existir;
2. `docs/specs/PRODUCT_SPEC.md`;
3. a spec da área afetada, como `docs/specs/FRONTEND_MVP_SPEC.md`;
4. `docs/PROJECT_DEFINITION.md`, `docs/ARQUITETURA.md` e `docs/PLANEJAMENTO.md`;
5. o código e os testes atuais;
6. Figma, Notion ou Trello somente para completar evidências não versionadas.

O Trello controla o andamento, não substitui requisitos. O Figma controla a
referência visual, não substitui regras de negócio. Se duas fontes divergirem,
não escolha silenciosamente: registre a decisão na Issue e atualize a spec
canônica no mesmo PR.

## Processo obrigatório de tarefa

Uma funcionalidade, correção ou mudança técnica só está concluída quando percorre
o fluxo documentado em `docs/PROCESSO_DESENVOLVIMENTO.md`:

`Trello → Issue → spec → branch → implementação → verificação → PR → revisão → merge → encerramento`

Exceção: mudanças exclusivamente nas próprias specs ou instruções de agentes não
precisam de cartão no Trello, mas continuam exigindo Issue, branch, PR e
verificação.

- Nunca desenvolva diretamente na `main`.
- Uma branch deve atender uma única Issue e um escopo delimitado.
- Use `feature/`, `fix/`, `docs/`, `test/` ou `chore/` seguido de nome curto.
- Para trabalho funcional ou de risco relevante, crie
  `docs/specs/tasks/ISSUE-<numero>-<slug>.md` a partir do template.
- Não misture refatorações não necessárias à entrega.
- Não marque tarefa como concluída apenas porque o código foi escrito.
- Se não houver acesso para atualizar Trello, Issue ou PR, entregue o código como
  pendente e informe exatamente qual etapa externa falta.

## Planejamento e implementação

Antes de escrever:

1. confira o estado do Git e preserve alterações do usuário;
2. leia a spec e os arquivos diretamente afetados;
3. identifique critérios de aceite, riscos, dependências e verificações;
4. confirme que a Issue e a branch correspondem ao trabalho;
5. registre dúvidas que alterem produto, segurança ou banco antes de assumir uma
   resposta.

Implemente o menor fluxo vertical que gere comportamento observável. Prefira
red → green → refactor: primeiro uma verificação que falha pelo comportamento
ausente, depois a implementação mínima, depois refatoração com os testes verdes.

## Qualidade obrigatória

- Todo PHP alterado deve passar por `php -l`.
- Toda mudança funcional deve ter teste automatizado quando a estrutura permitir;
  caso contrário, registre roteiro manual reproduzível e resultado observado.
- Execute `git diff --check` antes do commit.
- Não declare teste, tela, integração ou requisito como concluído sem evidência.
- Valide cenários feliz, vazio, erro, permissão negada e dados inválidos quando
  aplicáveis.
- Não versione credenciais, dados pessoais, configuração local ou artefatos
  temporários.
- Preserve compatibilidade com XAMPP, PHP e MySQL definidos pelo projeto.

## Segurança e integridade

- Autorização deve ser verificada no servidor em todas as rotas e ações.
- Use `password_hash()` e `password_verify()`; nunca armazene senha em texto puro.
- Use consultas preparadas e escape de saída HTML.
- Requisições de escrita devem validar método, sessão, permissão, entrada e CSRF.
- Atribuição, mudança de status, encerramento e histórico devem usar transação
  quando formarem uma única operação de negócio.
- Usuários, categorias, chamados e histórico não devem ser apagados de forma que
  quebre rastreabilidade. Prefira desativação quando previsto na spec.

## Definition of Done

Uma tarefa só pode ser encerrada quando:

- critérios de aceite da Issue e da spec foram atendidos;
- verificações relevantes passaram e suas evidências estão no PR;
- documentação, SQL e contratos foram atualizados quando necessário;
- o PR referencia a Issue com `Closes #<numero>`;
- houve revisão e os apontamentos foram resolvidos;
- o PR foi integrado à `main`;
- a Issue foi fechada e, quando aplicável, o cartão foi movido para `Done` com os
  links da Issue e do PR.
