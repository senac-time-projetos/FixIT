# ISSUE-15 — Implementar fluxo visual do técnico conforme Figma

Status: Em validação

Issue: `#15`

Cartões Trello:

- <https://trello.com/c/3lgKH5sJ/19-p1-desenvolver-fluxo-de-chamados>
- <https://trello.com/c/erf9OsDh/29-p2-implementar-coment%C3%A1rios-e-hist%C3%B3rico>
- <https://trello.com/c/77ChL6M6/30-p2-implementar-busca-filtros-e-resumo>

Responsável: agente de desenvolvimento

## Resultado para o usuário

O técnico poderá consultar e filtrar a fila, analisar um chamado sem responsável,
assumir o atendimento, registrar atualizações e uma solução e consultar o estado
encerrado. Até a integração do backend, nenhuma ação será simulada como persistida.

## Contexto e fontes

- `docs/specs/PRODUCT_SPEC.md` e `docs/specs/FRONTEND_MVP_SPEC.md`.
- Figma: `72:432`, `72:515`, `72:555` e `72:613`.
- Componentes compartilhados entregues pelas Issues `#9` e `#12`.

## Escopo

- Cabeçalho autenticado configurável para a área do técnico.
- Fila com busca, filtros, tabela desktop, cards mobile e estado vazio.
- Estado anterior à atribuição com ação de assumir.
- Atendimento com atualização, solução obrigatória e encerramento.
- Estado encerrado com solução e histórico completo.
- Contratos PHP preparados para dados e permissões do backend.
- Validação, escape, responsividade, teclado e testes automatizados.

## Fora do escopo

- Consultas MySQL, transações, sessão, autorização e CSRF compartilhado.
- Persistir atribuição, atualização, solução ou encerramento.
- Administração de usuários e categorias.

## Fluxos e regras

1. A fila recebe somente chamados autorizados pelo backend e filtra os dados
   recebidos por busca, status, prioridade e categoria.
2. Somente chamado aberto e sem responsável apresenta “Assumir chamado”.
3. Assumir usa POST; sem backend, informa indisponibilidade e não altera o estado.
4. O atendimento somente é exibido quando o técnico informado é o responsável.
5. Atualização exige 2 a 1000 caracteres; solução exige 10 a 2000 caracteres.
6. Encerramento sem solução falha junto ao campo. Um POST válido também falha de
   forma segura enquanto não houver persistência.
7. Chamado encerrado é somente leitura e mantém solução e histórico.

## Dados e contratos

- Técnico: `name` e identificador interno.
- Chamado: contrato da Issue `#12`, acrescido de `department`, `assignee_id`,
  `resolved_by`, `resolved_at` e eventos de histórico.
- Filtros GET limitados a valores conhecidos e busca de até 120 caracteres.
- Ações de escrita usam POST e identificador do chamado fora de campos editáveis.

## Interface e acessibilidade

- Hierarquia e medidas seguem os quatro frames em desktop.
- Em telas abaixo de 720 px, filtros e ações empilham e a tabela vira cards.
- Labels, erros associados, foco no primeiro erro e status sempre textual.
- Um `h1` por página, títulos descritivos e foco visível.

## Segurança, privacidade e recuperação

- Toda saída dinâmica é escapada.
- A interface não decide autorização; o servidor deverá revalidar técnico e estado.
- Atribuição e encerramento deverão usar transação na integração.
- Nenhum POST local altera arrays para fingir persistência.
- Rollback é a reversão do PR; não há migração de banco.

## Plano de implementação

1. Criar testes falhando para fila, atribuição, atendimento e encerramento.
2. Generalizar o cabeçalho e reutilizar helpers/status existentes.
3. Implementar fila e cards mobile.
4. Implementar estados aberto e em atendimento com validação/falha segura.
5. Implementar o estado encerrado no detalhe compartilhado.
6. Comparar os quatro frames em desktop e testar 390/320 px.
7. Executar lint, testes e registrar evidências.

## Critérios de aceite

- [x] Fila corresponde ao frame `72:432`.
- [x] Atribuição corresponde ao frame `72:515`.
- [x] Atendimento corresponde ao frame `72:555`.
- [x] Encerrado corresponde ao frame `72:613`.
- [x] Escritas não simulam persistência e validam os campos.
- [x] Estados vazio, sem permissão e erro são claros.
- [x] Desktop, 390 px, 320 px e teclado foram verificados.
- [ ] Testes, PR e Trello possuem evidências.

## Verificação e evidências

- `php -l` nos PHP alterados.
- `php tests/technician_flow_test.php` e regressões existentes.
- `git diff --check`.
- comparação visual dos quatro frames.
- busca por dados sensíveis, sucesso fictício e URLs temporárias do Figma.

## Decisões e pendências

- Em 9 de novembro de 2026, os quatro estados foram agrupados por compartilharem
  o mesmo chamado, layout e contrato, mantendo uma única Issue vertical.
- O frame `72:613` volta ao cabeçalho do colaborador; ele será implementado como
  estado encerrado do detalhe já existente, evitando uma página duplicada.
