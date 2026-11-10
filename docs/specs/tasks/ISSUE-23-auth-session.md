# ISSUE-23 — Integrar autenticação, sessão e permissões

Status: Concluída

Issue: `#23`

Dependência: Issue `#20`.

## Resultado

Usuários ativos entram com e-mail e senha, recebem uma sessão regenerada e são
redirecionados conforme o perfil. Usuários inativos, credenciais inválidas,
sessões ausentes e perfis sem permissão são rejeitados sem vazamento de dados.

## Escopo

- Sessão PHP com cookie `HttpOnly`, `SameSite=Lax` e `Secure` em HTTPS.
- Autenticação por `password_verify()` e mensagem genérica de falha.
- Bloqueio de conta inativa e atualização opcional de hash antigo.
- Logout por POST, destruição de sessão e cookie.
- CSRF por sessão para login, logout e futuras escritas.
- Usuário autenticado armazenado sem hash de senha.
- Guardas reutilizáveis de sessão e perfil.
- Integração do login e redirecionamento por perfil.
- Testes com SQLite sem depender do MariaDB local.

## Fora do escopo

- Integração de todos os CRUDs às páginas.
- Recuperação de senha, cadastro público e MFA.
- Reparo dos arquivos InnoDB do XAMPP.

## Regras

1. A resposta de login não diferencia e-mail ausente, senha errada ou conta
   inativa.
2. Senha nunca entra em sessão, log ou mensagem.
3. Login bem-sucedido regenera o ID antes de salvar o usuário.
4. Sessão guarda `id`, `nome`, `email` e `perfil`.
5. Rotas sem sessão redirecionam ao login; perfil inválido recebe 403.
6. Logout exige POST e CSRF válido.
7. Destinos: colaborador → `meus-chamados.php`; técnico → `chamados.php`;
   administrador → `resumo.php`.
8. Destino informado pelo cliente é aceito somente se for uma rota interna
   permitida, evitando redirecionamento aberto.

## Critérios de aceite

- [x] Login válido cria sessão sem hash ou senha.
- [x] Senha inválida e conta inativa são rejeitadas genericamente.
- [x] Logout remove sessão e exige CSRF.
- [x] Guardas negam sessão ausente e perfil inadequado.
- [x] Tela de login mantém o Figma e integra o backend.
- [x] Testes cobrem três perfis, CSRF, sessão e redirecionamentos.
- [x] Regressões visuais continuam aprovadas.

## Segurança e recuperação

- Queries continuam preparadas pelo `UserRepository`.
- A sessão usa modo estrito e não aceita IDs fornecidos pela URL.
- CSRF usa `random_bytes()` e `hash_equals()`.
- Rollback é a reversão do PR; não há alteração de esquema.

## Limitação observada

Em 9 de novembro de 2026, o MariaDB 10.4 do XAMPP falhou ao iniciar por corrupção
InnoDB preexistente (`Missing MLOG_CHECKPOINT`). Nenhuma tentativa destrutiva de
reparo foi executada. Os testes desta Issue usam SQLite em memória.

## Evidências observadas

- `tests/auth_session_test.php`: três destinos por perfil, credencial inválida,
  conta inativa, sessão sanitizada, regeneração, logout, CSRF e autorização.
- `tests/login_page_test.php`: formulário, acessibilidade, CSRF e mensagem
  genérica preservados.
- Todas as suítes anteriores continuaram aprovadas.
- PR integrado: `#24`; Issue encerrada: `#23`.
