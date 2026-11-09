# FixIT — Especificação do frontend do MVP

Status: Pronta para decomposição em tarefas; implementação pendente

## Objetivo

Entregar uma interface web consistente com a identidade FixIT e integrada ao
backend real. O frontend deve permitir que cada perfil conclua suas tarefas sem
depender de dados simulados ou conhecimento técnico.

## Referência visual canônica

A alta fidelidade do Figma é a referência visual. Frames antigos, protótipos
iniciais e versões BF/MF servem apenas como histórico.

| Fluxo | Frame canônico |
| --- | --- |
| Login | `72:201` |
| Meus chamados | `72:244` |
| Novo chamado | `72:324` |
| Detalhe do chamado | `72:385` |
| Fila do técnico | `72:432` |
| Assumir chamado | `72:515` |
| Atendimento | `72:555` |
| Chamado encerrado | `72:613` |
| Resumo administrativo | `72:647` |
| Usuários | `72:711` |
| Cadastrar usuário | `72:781` |
| Categorias | `72:864` |
| Login inválido | `87:791` |
| Novo chamado inválido | `87:827` |
| Mobile: meus chamados | `75:829` |
| Mobile: novo chamado | `75:855` |
| Mobile: detalhe | `75:894` |

Arquivo: <https://www.figma.com/design/cDIySt35PWPGPdHDbzdkR0/FixIT>

## Identidade visual

- Fonte principal: Inter, com fallback de sistema.
- Primária: `#0A5FF8`.
- Texto principal: `#081529`.
- Texto secundário: `#52627B`.
- Fundo: `#F5F7FB`.
- Bordas: `#DCE3ED`.
- Cards: `#FFFFFF`.

Tokens devem ficar centralizados no CSS. Não copie valores diferentes para cada
tela. A logo aprovada deve ser exportada do Figma para assets locais antes da
entrega; a aplicação não depende do Figma em execução.

## Estrutura compartilhada

Todas as áreas autenticadas usam cabeçalho responsivo com marca, navegação
permitida, identificação do usuário e logout. Componentes mínimos:

- marca e cabeçalho;
- navegação por perfil;
- botão primário, secundário e ação textual;
- campo de texto, senha, seleção e textarea;
- mensagem de erro, sucesso e aviso;
- badge de status e prioridade com texto;
- card de indicador;
- lista/tabela responsiva;
- paginação ou limite explícito quando o volume exigir;
- estado vazio e confirmação de ação.

## Telas e comportamentos

### Login

Campos de e-mail e senha, envio único e mensagem genérica para credenciais
inválidas. Não oferecer cadastro público. Após sucesso, redirecionar conforme o
perfil. Sessão existente deve evitar novo login. O botão de demonstração do Figma
não integra a versão final, salvo decisão acadêmica explícita.

### Meus chamados

Exibir somente registros do usuário autenticado, indicadores por status, busca
por número/título, filtro de status e ação para novo chamado. Incluir estado vazio
com orientação clara.

### Novo chamado

Título, categoria ativa, prioridade e descrição. Mostrar significado das
prioridades. Erros aparecem junto aos campos, preservando valores válidos. Após
sucesso, mostrar número e link para o detalhe.

### Detalhe do colaborador

Exibir identificação, categoria, prioridade, status, responsável quando houver,
descrição, comentários, histórico e solução quando encerrado. Ações dependem da
permissão e do estado.

### Fila e atendimento do técnico

Busca por número, título ou solicitante; filtros de status, prioridade e
categoria; responsável/status e ações coerentes. Assumir deve impedir envio
duplicado. Atendimento permite atualização, comentário e solução. Encerramento
sem solução deve falhar com mensagem junto ao campo.

### Administração

Resumo calculado no banco; listas de usuários e categorias; formulários de
cadastro/edição; desativação com confirmação e explicação de preservação do
histórico. E-mail/nome duplicado e valores inválidos recebem mensagem específica.

## Responsividade

- O conteúdo deve funcionar a partir de 320 px sem rolagem horizontal da página.
- Navegação colapsa preservando nome da área e logout.
- Formulários de duas colunas passam para uma coluna.
- Tabelas viram cartões rotulados ou usam solução acessível equivalente.
- Ações importantes permanecem próximas ao conteúdo correspondente.
- Textos longos quebram sem sobrepor ou cortar controles.

Os três frames mobile de colaborador são referências explícitas. Telas de técnico
e administrador devem aplicar as mesmas regras, mesmo sem frames móveis próprios.

## Acessibilidade

- HTML semântico, idioma `pt-BR` e título de página descritivo.
- Um `h1` por página e hierarquia sem saltos artificiais.
- `label` associado a todo controle e mensagens ligadas com `aria-describedby`.
- Primeiro erro recebe foco após envio inválido; resumo de erros é recomendado em
  formulários longos.
- Foco visível, ordem lógica e operação completa por teclado.
- Status, prioridade e resultado nunca dependem só de cor.
- Mensagens dinâmicas relevantes usam região de status apropriada sem excesso.
- Contraste mínimo WCAG AA e alvos de toque adequados.

## Integração e segurança

Templates recebem dados preparados pelo backend e não executam SQL. Formulários de
escrita usam POST e token CSRF. Saídas são escapadas. A interface pode esconder
ações não permitidas, mas o servidor sempre revalida a autorização.

## Estados de qualidade

Cada fluxo deve demonstrar: conteúdo normal, vazio, validação, erro de servidor,
sem permissão, sessão expirada e sucesso. Operações não devem gerar duplicidade ao
recarregar ou enviar duas vezes.

## Estratégia de entrega

1. Fundação visual, assets, layout e componentes.
2. Login integrado e navegação por perfil.
3. Fluxo do colaborador: lista, abertura e detalhe.
4. Fluxo do técnico: fila, assumir, comentar e encerrar.
5. Administração: resumo, usuários e categorias.
6. Responsividade, acessibilidade, estados e testes de regressão.

Cada etapa é uma ou mais Issues pequenas, com spec de tarefa quando necessário.
Não implemente todas as telas em um único PR.

## Critérios de aceite do frontend

- Telas usam componentes e tokens compartilhados.
- Não restam textos de template nem dados simulados apresentados como reais.
- Todos os fluxos possuem estados de sucesso, vazio e erro aplicáveis.
- Desktop e mobile atendem aos comportamentos definidos.
- Fluxos principais são concluídos com teclado.
- Entradas inválidas preservam dados e identificam o campo.
- A interface reflete permissões, e testes confirmam a proteção no servidor.
- Capturas comparativas e roteiro funcional acompanham os PRs visuais.
