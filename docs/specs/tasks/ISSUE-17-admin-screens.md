# ISSUE-17 — Concluir telas administrativas conforme Figma

Status: Em implementação

Issue: `#17`

## Resultado para o usuário

O administrador poderá consultar o resumo dos atendimentos, navegar entre as
áreas administrativas e usar as interfaces de gestão de usuários e categorias.
Até a integração do backend, as ações de escrita falham com segurança e não
simulam persistência.

## Fontes

- `docs/specs/PRODUCT_SPEC.md` e `docs/specs/FRONTEND_MVP_SPEC.md`.
- Figma: `72:647`, `72:711`, `72:781`, `72:864`, `75:432`, `75:490`,
  `75:567`, `75:625` e `75:686`.
- Componentes e tokens entregues pelas Issues `#9`, `#12` e `#15`.

## Escopo

- Cabeçalho administrativo com Chamados, Resumo, Usuários e Categorias.
- Resumo com quatro indicadores e distribuições por categoria e prioridade.
- Lista responsiva, estado vazio e estados de sucesso de usuários.
- Cadastro e edição de usuário, inclusive ação de desativação.
- Cadastro, edição e inativação de categorias.
- Validação, escape de saída, falha segura e testes automatizados.

## Fora do escopo

- Consultas MySQL, sessão, autorização e CSRF compartilhado.
- Persistência real de usuário, senha, categoria ou desativação.
- Confirmação modal definitiva, que depende da ação segura do backend.

## Regras e contratos

1. Templates recebem somente dados já autorizados pelo backend.
2. Resumo é calculado a partir de chamados recebidos, nunca de dados fixos.
3. Nome de usuário: 2–120 caracteres; e-mail válido e até 160 caracteres.
4. Perfil permitido: `colaborador`, `tecnico` ou `administrador`.
5. Situação permitida: `ativo` ou `inativo`.
6. Senha inicial obrigatória no cadastro, com no mínimo 8 caracteres; nova senha
   é opcional na edição, mas, quando informada, segue o mesmo limite.
7. Categoria exige nome entre 2 e 80 caracteres.
8. Escritas usam POST e falham com mensagem clara sem backend.
9. Desativação e inativação preservam histórico e nunca são tratadas como exclusão.

## Critérios de aceite

- [ ] Resumo corresponde ao frame `72:647`.
- [ ] Usuários e estados correspondem a `72:711`, `75:432` e `75:567`.
- [ ] Cadastro/edição correspondem a `72:781` e `75:490`.
- [ ] Categorias e estados correspondem a `72:864`, `75:625` e `75:686`.
- [ ] Nenhum template inventa dados ou sucesso em produção.
- [ ] Estados vazio, inválido, servidor indisponível e permissão negada existem.
- [ ] Desktop, 390 px e 320 px foram verificados.
- [ ] Testes e PR registram as evidências.

## Verificação

- `php -l` nos PHP alterados.
- `php tests/admin_screens_test.php` e regressões existentes.
- `git diff --check`.
- comparação visual desktop e móvel.

## Decisão

Os nove frames pertencem ao mesmo fluxo administrativo e compartilham cabeçalho,
contratos e componentes. Eles formam uma única entrega vertical, assim como o
fluxo técnico da Issue `#15`, sem incluir o backend persistente.
