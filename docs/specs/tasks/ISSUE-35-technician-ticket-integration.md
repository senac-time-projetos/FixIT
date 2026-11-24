# ISSUE-35 — Integrar fluxo técnico aos chamados persistidos

Status: Concluída

Issue: `#35`

Trello: `[P1] Desenvolver fluxo de chamados`

Entrega: PR `#36`, integrado à `main` em 24/11/2026.

Evidências: lint PHP aprovado nos oito arquivos alterados, `git diff --check`
sem erros, onze suítes PHP aprovadas e ciclo completo validado no MySQL 8.4
Docker sem dados residuais.

Dependências: Issues `#23`, `#26`, `#29` e `#32`.

## Resultado

Técnicos autenticados consultarão a fila real, poderão abrir chamados, assumir
um atendimento livre, registrar atualizações e encerrar com solução. Todas as
escritas usam CSRF, o usuário da sessão e as transações do `TicketService`.

## Fluxos

### Fila

- Técnico e administrador consultam todos os chamados.
- Busca por número, título ou solicitante e filtros de status, prioridade e
  categoria são aplicados no repositório.
- A tela preserva tabela desktop, cartões mobile e estado vazio.

### Atendimento

- Somente técnico acessa ações de atendimento.
- Chamado aberto e sem responsável pode ser assumido atomicamente.
- Chamado em atendimento só pode ser alterado pelo técnico responsável.
- Atualização exige 2–1000 caracteres e entra no histórico.
- Encerramento exige solução de 10–2000 caracteres e grava status, solução, data
  e histórico na mesma transação.
- Chamado encerrado fica disponível para consulta, sem nova alteração.

### Abertura pelo técnico

- A tela existente de novo chamado aceita colaborador ou técnico.
- Categoria ativa, solicitante, autor e perfil vêm do servidor/sessão.
- O mesmo serviço transacional registra chamado e histórico.

## Decisão sobre exclusão

Chamados não são apagados fisicamente. O encerramento com solução representa o
fim do ciclo de vida exigido pelo CRUD, preservando comentários e histórico. A
decisão segue a regra canônica de rastreabilidade do projeto e foi registrada na
Issue e no cartão Trello.

## Segurança

- IDs de solicitante, técnico e autor nunca vêm do formulário.
- Todo POST exige token CSRF válido.
- Acesso e vínculo do técnico são verificados novamente no servidor.
- Exceções SQL não são exibidas.
- Redirecionamento após POST evita repetição de ações.

## Critérios de aceite

- [x] Fila real e filtros retornam dados persistidos.
- [x] Técnico pode abrir chamado usando categoria ativa.
- [x] Atribuição concorrente mantém apenas um responsável.
- [x] Outro técnico não consegue atualizar ou encerrar o chamado.
- [x] Atualização válida aparece no histórico.
- [x] Encerramento exige solução e fica disponível em modo leitura.
- [x] POST sem CSRF não altera dados.
- [x] Fluxo completo passa no MySQL Docker sem deixar dados de teste.
- [x] Fixtures visuais e regressões permanecem aprovadas.

## Fora do escopo

- CRUD administrativo de usuários e categorias.
- Exclusão física de chamados.
