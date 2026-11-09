# ISSUE-9 — Desenvolver tela de Login conforme Figma

Status: Concluída

Issue: `#9`

Cartão Trello: <https://trello.com/c/iqOnVbqK/15-p1-desenvolver-tela-de-login>

Responsável: Douglas Gomes / agente de desenvolvimento

## Resultado para o usuário

O usuário acessará uma tela de Login fiel à identidade FixIT, preencherá e-mail e
senha e receberá validação clara. A tela ficará pronta para a integração posterior
com autenticação real, sem simular sessão ou credenciais válidas.

## Contexto e fontes

- Produto: `docs/specs/PRODUCT_SPEC.md`, RF01 e RF02.
- Frontend: `docs/specs/FRONTEND_MVP_SPEC.md`.
- Figma principal: frame `72:201`.
- Figma de erro: frame `87:791`.
- Trello: checklist “Implementação” com cinco itens.
- Código atual: `frontend/pages/login.php` é apenas um placeholder.

## Escopo

- Estrutura HTML semântica da tela.
- Logo e assets aprovados exportados para o repositório.
- Tokens e componentes reutilizáveis necessários ao Login.
- Campos de e-mail e senha com labels.
- Validação básica no servidor e mensagens acessíveis.
- Estado de credenciais inválidas somente como resposta preparada para a futura
  autenticação, sem credenciais demonstrativas.
- Responsividade a partir de 320 px e operação completa por teclado.

## Fora do escopo

- Consultar usuários no MySQL.
- Criar sessão, logout ou redirecionamento por perfil.
- Implementar botões de demonstração do protótipo.
- Implementar outras telas do MVP.

## Fluxos e regras

1. GET exibe o formulário vazio.
2. POST sem e-mail, com e-mail inválido ou sem senha preserva dados não sensíveis e
   associa mensagem ao campo correspondente.
3. A senha nunca volta preenchida após erro.
4. Enquanto autenticação não existir, o formulário não concede acesso nem anuncia
   login bem-sucedido.
5. O estado visual de credenciais inválidas deve estar preparado para a integração
   da Issue de autenticação, sem diferenciar usuário inexistente de senha errada.

## Dados e contratos

- `email`: obrigatório, formato de e-mail, normalizado sem alterar o conteúdo
  significativo e limitado a 180 caracteres.
- `password`: obrigatória, tratada como segredo e nunca incluída em URL, HTML,
  log, flash persistente ou captura de teste.
- Método de envio: POST.

## Interface e acessibilidade

- Um único `h1` e título de documento descritivo.
- Labels persistentes; placeholder não substitui label.
- Mensagens ligadas por `aria-describedby` e `aria-invalid` quando aplicável.
- Primeiro campo inválido recebe foco após o envio.
- Foco visível e contraste WCAG AA.
- Layout sem rolagem horizontal a partir de 320 px.
- Estados normal e inválido devem corresponder aos frames canônicos.

## Segurança, privacidade e recuperação

- Não registrar a senha ou repeti-la na resposta.
- Escapar o e-mail antes de renderizar.
- Preparar o formulário para token CSRF quando a infraestrutura compartilhada for
  introduzida; não inventar token local incompatível nesta tarefa.
- A mudança não altera banco; rollback é a reversão do PR.

## Plano de implementação

1. Obter `get_design_context` dos frames `72:201` e `87:791`, incluindo captura e
   assets.
2. Inventariar componentes/tokens existentes e exportar assets exatos.
3. Criar teste ou verificação falhando para markup, validação e responsividade.
4. Implementar estrutura e validação mínima.
5. Aplicar tokens e responsividade, sem copiar posicionamento absoluto.
6. Renderizar em servidor local e comparar com o Figma em desktop e mobile.
7. Corrigir diferenças, executar verificações e anexar evidências ao PR.

## Critérios de aceite

- [x] Tela normal corresponde ao frame `72:201`.
- [x] Estado inválido corresponde ao frame `87:791`.
- [x] Assets locais são os assets exatos do Figma.
- [x] Campos, validações e foco atendem aos contratos desta spec.
- [x] Layout funciona a partir de 320 px.
- [x] Não há autenticação fictícia ou senha exposta.
- [x] Verificações e capturas comparativas estão no PR.
- [x] Checklist do cartão Trello foi concluído com evidências.

## Verificação e evidências

- `php -l frontend/pages/login.php` e demais PHP alterados.
- testes automatizados introduzidos para validação/markup;
- `git diff --check`;
- navegação por teclado;
- capturas desktop e 320/390 px;
- comparação lado a lado com os dois frames do Figma;
- busca por senha, credenciais e URLs temporárias do Figma.

## Decisões e pendências

- Em 9 de novembro de 2026, o escopo foi ampliado para todas as telas do MVP, mas
  cada conjunto será entregue em Issue e PR próprios.
- O limite temporário do Figma foi resolvido em nova tentativa. Os contextos de
  alta fidelidade e as capturas dos frames `72:201` e `87:791` foram obtidos antes
  da implementação.
- A comparação visual foi concluída em desktop e em viewport móvel de 390 px. A
  cobertura automatizada valida também os contratos necessários para 320 px.
