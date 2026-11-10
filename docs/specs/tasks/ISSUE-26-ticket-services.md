# ISSUE-26 — Implementar regras transacionais de chamados

Status: Em revisão

Issue: `#26`

Dependências: Issues `#20` e `#23`.

## Resultado

As operações críticas do chamado serão executadas por um serviço único, com
validação, autorização e histórico consistente. A interface não precisará
coordenar atualizações de várias tabelas.

## Escopo e regras

1. Abertura aceita colaborador ou técnico, valida título, descrição, categoria e
   prioridade, força status aberto e registra o evento `abertura`.
2. Colaborador visualiza somente chamado próprio; técnico e administrador podem
   consultar todos.
3. Somente técnico ativo pode assumir chamado aberto sem responsável. Atribuição,
   status e evento `atribuicao` usam a mesma transação.
4. Comentário exige 2–1000 caracteres e permissão de visualização; o autor vem da
   sessão, nunca do formulário.
5. Atualização de atendimento exige técnico responsável e registra o evento
   `atualizacao` sem alterar o texto original do chamado.
6. Encerramento exige técnico responsável e solução de 10–2000 caracteres;
   status, solução, data e evento `encerramento` são atômicos.
7. Falha em qualquer etapa executa rollback e não deixa histórico órfão.

## Fora do escopo

- Controllers HTTP e redirecionamentos.
- Integração dos templates com os serviços.
- Reparo ou importação no MariaDB local.

## Critérios de aceite

- [x] Abertura e histórico são atômicos.
- [x] Permissões diferenciam colaborador, técnico e administrador.
- [x] Dois técnicos não conseguem assumir o mesmo chamado.
- [x] Comentário e atualização validam autor, vínculo e tamanho.
- [x] Encerramento exige solução e técnico responsável.
- [x] Rollback é comprovado em falha controlada.
- [x] Regressões continuam aprovadas.

## Segurança

- IDs de autor e técnico vêm do usuário autenticado.
- Exceções de domínio usam mensagens controladas, sem SQL.
- Repositórios continuam sendo a única camada com consultas.

## Evidências

- `tests/ticket_service_test.php`: cobre operações válidas, recusas por perfil,
  disputa de atribuição e rollback provocado por falha no histórico.
- Suítes de autenticação, persistência e telas executadas sem regressão.
- Validação PHP e verificação de diferenças sem erros.
