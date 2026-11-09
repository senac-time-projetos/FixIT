# FixIT — Especificação canônica do produto e MVP

Status: Aprovada para orientar o planejamento; implementação pendente

## Visão

O FixIT centraliza o suporte interno de TI de uma empresa de médio porte. O
colaborador registra um problema e acompanha o atendimento; o técnico organiza a
fila e resolve chamados; o administrador gerencia cadastros e acompanha o volume
de atendimento.

O MVP substitui solicitações informais por registros com solicitante, categoria,
prioridade, responsável, status, comentários, solução e histórico auditável.

## Usuários e permissões

### Colaborador

Pode entrar e sair, abrir chamado, consultar somente os próprios chamados,
visualizar detalhes e histórico e comentar nos chamados em que participa.

### Técnico

Possui as capacidades do colaborador e também pode consultar a fila, buscar,
filtrar, assumir chamados, registrar atendimento, alterar status e encerrar com
solução.

### Administrador

Pode visualizar todos os chamados e o resumo, além de cadastrar, editar e
desativar usuários e categorias. A administração não remove vínculos históricos.

Toda permissão é aplicada no servidor; ocultar uma ação na tela não é controle de
acesso.

## Vocabulário normativo

- Perfis: `colaborador`, `tecnico`, `administrador`.
- Prioridades: `baixa`, `media`, `alta`.
- Status do chamado: `aberto`, `em_atendimento`, `encerrado`.
- “Resolvido” pode aparecer em texto explicativo, mas o valor persistido e os
  filtros usam `encerrado`.
- Usuário ou categoria inativa permanece vinculada aos registros existentes, mas
  não pode ser selecionada em novos registros.

## Entidades mínimas

### Usuário

Nome, e-mail único, hash da senha, perfil, situação e datas de criação/atualização.

### Categoria

Nome único, descrição opcional, situação e datas.

### Chamado

Número público, solicitante, título, descrição, categoria, prioridade, status,
técnico opcional, solução opcional e datas de abertura/atualização/encerramento.

### Comentário

Chamado, autor, conteúdo e data. O autor deve ter permissão para visualizar o
chamado.

### Histórico

Chamado, autor da ação, tipo de evento, descrição objetiva, estado anterior e
novo estado quando aplicável, e data.

## Regras de negócio

1. Todo chamado pertence a um solicitante e uma categoria.
2. Título, descrição, categoria e prioridade são obrigatórios.
3. Novo chamado inicia como `aberto`, sem técnico e sem solução.
4. Um chamado `em_atendimento` possui técnico responsável.
5. Assumir um chamado aberto atribui o técnico, altera o status e registra
   histórico na mesma transação.
6. Encerrar exige solução não vazia, altera o status, registra data e histórico na
   mesma transação.
7. O colaborador nunca acessa chamado de outro solicitante apenas alterando URL ou
   parâmetros.
8. Comentários registram autor e data e são exibidos com escape.
9. Alterações relevantes geram histórico imutável para o usuário comum.
10. Desativação preserva todos os vínculos existentes.

## Requisitos funcionais do MVP

- RF01: login com e-mail e senha.
- RF02: logout, sessão e acesso por perfil.
- RF03: cadastro, edição e desativação de usuários.
- RF04: abertura de chamado.
- RF05: seleção de categoria ativa.
- RF06: seleção de prioridade.
- RF07: listagem e detalhe dos próprios chamados.
- RF08: fila de chamados para técnico.
- RF09: atribuição de técnico.
- RF10: alteração controlada de status.
- RF11: comentários.
- RF12: histórico.
- RF13: encerramento com solução.
- RF14: consulta de chamados anteriores.
- RF15: busca e filtros por status, prioridade e categoria.
- RF16: resumo com totais e distribuições por status, prioridade e categoria.

## Requisitos não funcionais

- Interface simples, responsiva e compatível com navegadores modernos.
- Autenticação segura, autorização no servidor, consultas preparadas, CSRF em
  escritas e escape de saída.
- Operações principais devem responder sem espera perceptível no volume acadêmico
  esperado; consultas de lista devem possuir índices adequados.
- Código separado entre apresentação, controle, regras e persistência.
- Instalação local reproduzível em XAMPP, sem credenciais versionadas.
- Mudanças críticas devem ser verificáveis e recuperáveis.

## Escopo do MVP

Inclui autenticação, usuários, categorias, chamados, comentários, histórico,
busca, filtros e resumo básico. Exclui aplicativo nativo, WhatsApp, IA, diagnóstico
automático, acesso remoto, inventário, estoque, financeiro, integrações externas e
SLA avançado.

## Critérios de aceite do produto

O MVP é demonstrável somente quando:

1. três contas fictícias representam os três perfis;
2. senha inválida, usuário inativo e acesso sem sessão são rejeitados;
3. um colaborador abre, lista e acompanha somente os próprios chamados;
4. um técnico encontra, assume, comenta e encerra com solução;
5. histórico registra abertura, atribuição, atualização e encerramento;
6. administrador mantém usuários/categorias e consulta números vindos do banco;
7. os fluxos funcionam em desktop e móvel, com teclado;
8. instalação limpa e roteiro de demonstração são reproduzíveis.

## Estado em 9 de novembro de 2026

Escopo, persona, identidade visual e protótipos estão documentados. O repositório
possui somente a estrutura inicial, página de demonstração, login provisório e SQL
de template. Banco FixIT, autenticação, permissões e CRUDs ainda não estão
implementados. Validação real do protótipo com usuários permanece pendente.

## Decisões abertas

- Confirmar com o professor qual operação satisfaz a exclusão do CRUD sem destruir
  histórico.
- Registrar o resultado da validação do DER.
- Definir versões mínimas suportadas de PHP e MySQL/MariaDB no ambiente da equipe.
- Validar o protótipo com usuários antes de considerar UX concluída.
