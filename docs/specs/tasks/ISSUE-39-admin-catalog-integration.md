# ISSUE-39 — Integrar cadastro de usuários e categorias

Status: Pronta para merge

Issue: `#39`

Trello: `[P2] Implementar cadastro de usuários e categorias`

Dependências: Issues `#20` e `#23`.

## Resultado

O administrador autenticado poderá listar, cadastrar, editar e desativar usuários
e categorias usando os dados persistidos no MySQL. As telas existentes continuam
seguindo o Figma e passam a usar controllers e services reais.

## Usuários

- Nome possui 2–120 caracteres e e-mail válido possui no máximo 160 caracteres.
- E-mail é normalizado em minúsculas e deve ser único.
- Perfil aceita somente `colaborador`, `tecnico` ou `administrador`.
- Situação aceita somente `ativo` ou `inativo`.
- Novo usuário exige senha com ao menos 8 caracteres, armazenada com
  `password_hash()`.
- Na edição, senha vazia preserva o hash existente.
- Desativação preserva todos os chamados e históricos vinculados.
- O administrador não pode desativar a própria conta pela interface.

## Categorias

- Nome possui 2–80 caracteres e deve ser único sem diferença entre maiúsculas e
  minúsculas conforme a collation do banco.
- Descrição opcional possui no máximo 255 caracteres.
- Cadastro inicia ativo; edição pode alterar nome, descrição e situação.
- Desativação preserva chamados existentes e remove a categoria das opções de
  novos chamados.

## Segurança e erros

- Somente o perfil `administrador` pode ler ou alterar esses cadastros.
- Toda escrita exige POST, token CSRF e usuário derivado da sessão.
- IDs nunca definem permissão e registros inexistentes retornam estado seguro.
- Duplicidade recebe mensagem específica sem expor SQL.
- Redirecionamento após sucesso evita repetição do POST.

## Critérios de aceite

- [x] Lista de usuários vem do banco e mantém estado vazio responsivo.
- [x] Cadastro e edição de usuário validam dados, hash e unicidade.
- [x] Desativação de usuário preserva vínculos e bloqueia auto-desativação.
- [x] Lista de categorias vem do banco e mantém estado vazio responsivo.
- [x] Cadastro e edição de categoria validam nome, descrição e unicidade.
- [x] Categoria inativa não aparece na abertura de chamado.
- [x] Toda escrita sem CSRF ou sem perfil administrativo é rejeitada.
- [x] Fluxo completo passa no MySQL Docker sem deixar dados de teste.
- [x] Regressões visuais e funcionais permanecem aprovadas.

## Fora do escopo

- Exclusão física de usuários ou categorias.
- Resumo administrativo calculado no banco.
- Paginação além do limite atual de 100 usuários.
