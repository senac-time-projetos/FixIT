# Definição do FixIT

## Proposta

Sistema de suporte de TI interno para uma empresa de médio porte. Pedidos por mensagens, telefone ou pessoalmente dificultam o acompanhamento. O FixIT reúne essas solicitações em chamados com responsável, status e histórico.

O público são os colaboradores e a equipe de TI. A persona é Mariana Oliveira, analista administrativa de 34 anos, que precisa registrar um problema e saber quem está cuidando dele.

## Perfis

| Perfil | Acesso previsto |
| --- | --- |
| Colaborador | Abrir chamados, consultar os próprios registros e comentar |
| Técnico | Consultar a fila, assumir, atualizar e encerrar chamados com solução |
| Administrador | Consultar todos os chamados e gerenciar usuários e categorias |

## Escopo inicial

- Autenticação com sessão PHP e permissões por perfil.
- Cadastro, edição e desativação de usuários e categorias.
- Chamados com título, descrição, categoria e prioridade.
- Busca e filtros por status, categoria e prioridade.
- Atribuição de técnico, comentários e histórico.
- Encerramento com solução e consulta aos atendimentos anteriores.
- Resumo dos chamados abertos, em atendimento e encerrados.

Status: `aberto → em_atendimento → encerrado`. Prioridades: `baixa`, `media` e `alta`.

## Regras principais

- Todo chamado tem um solicitante e uma categoria.
- Um chamado em atendimento precisa de técnico responsável.
- O colaborador acessa somente os próprios chamados.
- O encerramento exige uma solução.
- Alterações relevantes ficam no histórico.
- Usuários e categorias são desativados para preservar os registros anteriores.

A disciplina pede CRUD completo de uma entidade principal. A equipe deve confirmar com o professor se a desativação atende à operação de exclusão esperada.

## Fora da primeira versão

Aplicativo nativo, WhatsApp, IA, diagnóstico automático, acesso remoto, monitoramento, inventário, estoque, financeiro e SLA avançado.

## Equipe e referências

Os integrantes e as responsabilidades estão no [README](../README.md#equipe). O escopo completo e a persona estão no [Notion do grupo](https://app.notion.com/p/FixIT-3c4e668ab69a80e8b30ace47a10c5cce).
