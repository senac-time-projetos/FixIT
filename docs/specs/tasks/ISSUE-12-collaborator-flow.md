# ISSUE-12 — Implementar fluxo do colaborador conforme Figma

Status: Em implementação

Issue: `#12`

Cartões Trello:

- <https://trello.com/c/0aYXCk2X/17-p2-desenvolver-meus-chamados>
- <https://trello.com/c/3lgKH5sJ/19-p1-desenvolver-fluxo-de-chamados>

Responsável: agente de desenvolvimento

## Resultado para o usuário

O colaborador poderá consultar seus chamados, iniciar uma nova solicitação e
acompanhar os detalhes, comentários e histórico em uma interface fiel ao Figma.
Enquanto o backend não estiver integrado, as telas permanecerão seguras e
honestas: não criarão chamados nem comentários fictícios.

## Contexto e fontes

- Produto: `docs/specs/PRODUCT_SPEC.md`.
- Frontend: `docs/specs/FRONTEND_MVP_SPEC.md`.
- Figma desktop: `72:244`, `72:324` e `72:385`.
- Figma mobile: `75:829`, `75:855` e `75:894`.
- Figma de validação: `87:827`.
- Layout e tokens iniciados pela Issue `#9`.

## Escopo

- Cabeçalho autenticado reutilizável com área, usuário e ações permitidas.
- Lista com indicadores, busca, filtro, tabela desktop, cards mobile e vazio.
- Formulário de abertura com título, categoria, prioridade e descrição.
- Detalhe com status, metadados, descrição, responsável, histórico e comentário.
- Contratos de dados por variáveis PHP preparados para o backend.
- Validação no servidor, escape de saída, responsividade e acessibilidade.
- Testes automatizados de markup, estados, validação e segurança.

## Fora do escopo

- Persistência MySQL, autenticação, sessão, CSRF compartilhado e autorização real.
- Criar número de chamado, comentário ou histórico sem backend.
- Fluxos do técnico e do administrador.
- Controles de demonstração presentes somente no protótipo.

## Fluxos e regras

1. `meus-chamados.php` recebe usuário, indicadores e chamados preparados pelo
   backend; sem dados, mostra orientação clara em vez de registros simulados.
2. Busca e status usam GET, preservam valores e nunca ampliam o conjunto além do
   usuário autenticado quando o backend for conectado.
3. `novo-chamado.php` usa POST e valida todos os campos. Sem persistência, um
   envio válido informa indisponibilidade temporária e não simula sucesso.
4. `chamado.php` recebe somente um chamado autorizado pelo backend; sem chamado,
   mostra estado indisponível/sem permissão sem revelar dados.
5. Comentário usa POST, exige texto e não é anexado localmente como se persistido.

## Dados e contratos

- Usuário: `name` e identificador interno; nome sempre escapado.
- Chamado: `id`, `number`, `title`, `category`, `priority`, `status`,
  `description`, `requester`, `assignee`, `solution`, `history`.
- Título: obrigatório, de 5 a 120 caracteres.
- Categoria: obrigatória e limitada ao conjunto ativo recebido do backend.
- Prioridade: `baixa`, `media` ou `alta`.
- Descrição: obrigatória, de 10 a 2000 caracteres.
- Comentário: obrigatório, de 2 a 1000 caracteres.
- Busca: até 120 caracteres; status limitado aos valores conhecidos.

## Interface e acessibilidade

- Um `h1` por página; títulos e regiões descritivos.
- Componentes compartilhados para header, campos, badges, cards e mensagens.
- Labels persistentes, erros associados, foco no primeiro erro e foco visível.
- Tabela vira cards rotulados abaixo de 720 px.
- Layout utilizável a partir de 320 px sem rolagem horizontal da página.
- Status e prioridade sempre comunicados por texto, não apenas por cor.

## Segurança, privacidade e recuperação

- Toda saída dinâmica usa `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- Escritas aceitam somente POST e não persistem sem o serviço de backend.
- A autorização real continuará obrigatória no servidor após a integração.
- Nenhum dado pessoal ou credencial é versionado; os testes usam fixtures locais.
- Rollback é a reversão do PR; não há alteração de banco nesta tarefa.

## Plano de implementação

1. Registrar contratos e testes falhando para os três templates.
2. Extrair header autenticado e componentes visuais reutilizáveis.
3. Implementar lista, vazio, busca e adaptação mobile.
4. Implementar formulário, validação e estado de falha segura.
5. Implementar detalhe, histórico e comentário com falha segura.
6. Comparar os sete estados com o Figma em desktop e mobile.
7. Executar lint, testes, inspeção de teclado e registrar evidências no PR.

## Critérios de aceite

- [ ] Lista corresponde aos frames `72:244` e `75:829`.
- [ ] Formulário corresponde aos frames `72:324`, `75:855` e `87:827`.
- [ ] Detalhe corresponde aos frames `72:385` e `75:894`.
- [ ] Estado vazio e falha segura não apresentam dados ou sucesso fictícios.
- [ ] Contratos, validações, escape e foco possuem cobertura automatizada.
- [ ] Layout funciona a partir de 320 px e por teclado.
- [ ] PR contém verificações e evidências comparativas.
- [ ] Cartões Trello foram atualizados com Issue, PR e resultado.

## Verificação e evidências

- `php -l` em todos os PHP alterados.
- `php tests/collaborator_flow_test.php`.
- `git diff --check`.
- inspeção desktop e viewports de 390 px e 320 px.
- comparação visual com os sete frames.
- busca por dados sensíveis, URLs temporárias do Figma e sucesso simulado.

## Decisões e pendências

- Em 9 de novembro de 2026, as três telas foram agrupadas por formarem um único
  fluxo vertical pequeno e compartilharem os mesmos contratos/componentes.
- O CSS existente será ampliado sem adicionar uma etapa de build. A autorização
  do usuário para Tailwind permanece registrada, mas não é necessária para este
  projeto PHP simples.
- Persistência, sessão, CSRF e autorização serão integrados nas Issues de backend;
  até lá, os POSTs falham de forma explícita e recuperável.
