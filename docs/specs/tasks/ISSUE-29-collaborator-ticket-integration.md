# ISSUE-29 — Integrar fluxo real do colaborador aos chamados

Status: Concluída

Issue: `#29`

Dependências: Issues `#20`, `#23` e `#26`.

## Resultado

O colaborador autenticado poderá listar, abrir, consultar e comentar os próprios
chamados usando dados persistidos. As telas e os estados visuais existentes serão
preservados; dados de outro solicitante continuarão indisponíveis.

## Fluxos

### Lista

- Carregar somente chamados cujo `solicitante_id` seja o usuário da sessão.
- Aplicar busca e status somente sobre o conjunto já restrito ao solicitante.
- Manter resumo, estado vazio, tabela desktop e cartões mobile.

### Abertura

- Carregar somente categorias ativas.
- Exigir CSRF válido no POST.
- Usar o ID, nome e perfil exclusivamente da sessão.
- Delegar validação, transação e histórico ao `TicketService`.
- Redirecionar após sucesso para o detalhe, evitando reenvio do formulário.

### Detalhe e comentário

- Aceitar apenas número inteiro positivo.
- Consultar o chamado e aplicar `TicketService::canView` antes de carregar dados
  relacionados.
- Combinar eventos do histórico e comentários em ordem cronológica para a tela.
- Exigir CSRF válido e chamar `TicketService::comment` no envio.
- Redirecionar após sucesso e não aceitar comentário em chamado encerrado.

## Compatibilidade visual

- O HTML e as classes atuais devem permanecer estáveis salvo campos ocultos,
  mensagens e ajustes estritamente necessários para o fluxo real.
- Fixtures com `$skipAuth` continuam sem conexão com banco.
- Chaves do banco serão adaptadas para o contrato de apresentação existente.

## Segurança

- Autorização é validada no servidor em leitura e escrita.
- CSRF protege abertura e comentário.
- IDs de autor e solicitante nunca vêm do formulário.
- Exceções SQL não são exibidas ao usuário.

## Critérios de aceite

- [x] Lista real contém apenas chamados do usuário autenticado.
- [x] Abertura persiste chamado e histórico e redireciona para o detalhe.
- [x] Categorias inativas não podem ser usadas.
- [x] Detalhe de outro colaborador é tratado como indisponível.
- [x] Comentário autorizado persiste e comentário em encerrado é recusado.
- [x] POST sem CSRF válido não altera dados.
- [x] Fixtures visuais e todas as regressões continuam aprovadas.

## Fora do escopo

- Fluxo de atendimento do técnico.
- Administração de usuários e categorias.
- Reparo ou importação no MariaDB local.

## Evidências

- Implementação revisada e integrada pelo PR `#30`.
- Issue `#29` encerrada automaticamente após o merge.
- `tests/collaborator_ticket_controller_test.php` comprova isolamento por
  solicitante, categorias ativas, abertura, detalhe, comentário e recusa após
  encerramento.
- A suíte de CSRF valida tokens corretos e incorretos; os dois formulários reais
  incluem o token da sessão.
- Oito suítes e o lint PHP foram executados sem regressões.
