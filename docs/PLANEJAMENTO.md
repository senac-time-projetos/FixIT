# Planejamento do FixIT

Responsável pela organização: Alexandre Santana. O [Trello](https://trello.com/b/ihV3ZVG8/pi-senac-fixit) acompanha a situação atual de cada tarefa.

## Fluxo do quadro

Backlog → Doing → Em revisão → Done. Usar Blocking quando houver um impedimento, registrando o motivo e o que permite retomar. Evitar mais de uma tarefa ativa por pessoa.

P1 indica o que precisa ser resolvido primeiro; P2 vem depois. Uma tarefa só entra em Done quando os critérios forem atendidos e houver evidência. Um roteiro de teste preparado não significa teste executado.

## Backlog e critérios principais

| História | Prioridade | Aceite | Responsável |
| --- | --- | --- | --- |
| Como usuário, quero entrar e sair do sistema para acessar minhas funções. | P1 | Senha validada por hash, sessão, logout, bloqueio de inativos e acesso por perfil. | David; Douglas na tela |
| Como colaborador, quero registrar um problema para receber suporte. | P1 | Campos obrigatórios validados; chamado recebe número, solicitante da sessão e status aberto. | David e Douglas |
| Como técnico, quero assumir e encerrar chamados para organizar o atendimento. | P1 | Técnico atribuído; status em atendimento; solução obrigatória ao encerrar; histórico na mesma transação. | David e Douglas |
| Como colaborador, quero consultar meus chamados e acompanhar atualizações. | P2 | Somente meus registros são exibidos; detalhes, estado vazio e layout mobile disponíveis. | Douglas e David |
| Como participante do atendimento, quero comentar e consultar o histórico. | P2 | Autor e data registrados, permissão conferida e texto exibido com escape. | David e Douglas |
| Como técnico, quero buscar e filtrar a fila para encontrar solicitações. | P2 | Busca por número/título e filtros combinados, sempre respeitando o perfil. | David e Douglas |
| Como administrador, quero manter usuários e categorias. | P2 | Cadastro e edição, e-mail/nome únicos, perfis válidos e desativação sem perder vínculos. | David e Douglas |
| Como administrador, quero um resumo dos atendimentos. | P2 | Totais e distribuição calculados do banco e consistentes com as listas. | David e Douglas |

Preparação técnica P1: revisar DER e criar o SQL das cinco tabelas. Pedro fica responsável pela revisão técnica e implementação SQL. O diagrama preparado por Alexandre está no [tldraw](https://www.tldraw.com/f/HfDG_NoWIqczhMU4bl14H), na página “FixIT • Banco de dados”.

Confirmar com o professor qual entidade/operação atende à exclusão exigida no CRUD completo. Não apagar chamados nem seu histórico para cumprir esse requisito sem revisão.

## Proposta da próxima sprint

Objetivo: importar o banco FixIT e autenticar os três perfis no ambiente local. As datas e a capacidade devem ser confirmadas pelo grupo antes de iniciar.

1. Pedro revisa o DER e implementa o SQL.
2. Douglas prepara o formulário de login em paralelo.
3. David integra PDO, autenticação, sessão, logout e acesso por perfil.
4. Alexandre revisa entregas e executa os casos de teste.

Dependências: SQL → autenticação → páginas protegidas. O formulário pode ser desenvolvido antes da integração.

Na conversa diária, registrar o que foi feito, o próximo passo e o impedimento. Ao final, demonstrar instalação e login e anotar um ajuste de processo para a sprint seguinte.

Critério de fechamento: instalação reproduzível, autenticação dos três perfis, rejeição de senha inválida/conta inativa, acesso direto sem sessão bloqueado e evidências dos testes.

## Entregas de planejamento e design

Datas informadas na disciplina:

| Entrega | Data prevista | Peso | Conferência necessária |
| --- | --- | --- | --- |
| 01 — Planejamento, backlog e identidade da equipe | 04/09/2026 | 5% | Escopo, persona, equipe, responsabilidades e backlog |
| 02 — Design system e baixa/média fidelidade | 04/10/2026 | 5% | Manual visual e versões de wireframes identificadas |
| 03 — Alta fidelidade | 12/10/2026 | 10% | Fluxos, logo nas telas, mobile e validação do protótipo |

Essas datas não comprovam envio ou aprovação. Confirmar a situação com o professor; não substituir por novos prazos sem acordo.

O complemento de alta fidelidade preparado por Alexandre contém 12 telas principais, estados e três referências mobile, preservando os protótipos anteriores do grupo. Ainda faltam aplicação da logo, revisão mobile final, versões separadas de wireframes e validação real com pessoas. A conexão do Figma está limitada para novas edições; os dois cartões de edição estão em Blocking.

O manual original já estava marcado como concluído pelo grupo. A conferência final deve verificar versões da marca, fundos claro/escuro, cores, tipografia, botões, campos, cards, navegação e mensagens de sucesso/erro.

## Roteiros preparados

### Protótipo

Pedir a duas pessoas que abram um chamado e acompanhem seu status sem instruções de clique. No papel técnico, pedir que assumam e encerrem o atendimento. Registrar sucesso, dificuldades e sugestões por tarefa. Informar que os dados são simulados. Ainda não há resultado de teste registrado.

### Aplicação

Testar login válido/inválido, conta inativa, acesso sem sessão, restrição por perfil e separação dos chamados de dois colaboradores. Testar abertura com campos vazios, atribuição, comentário, histórico, encerramento com/sem solução, filtros e registros inativos. Conferir desktop, celular e navegação por teclado. Registrar falhas e retestar após correções.

Os roteiros estão preparados; a execução depende das telas ou funcionalidades correspondentes. Backend, CRUD e o SQL FixIT ainda não foram implementados nesta revisão.
