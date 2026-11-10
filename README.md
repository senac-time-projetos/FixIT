# FixIT

Sistema de chamados de suporte de TI desenvolvido no Projeto Integrador II do Senac.

O colaborador registra um problema e acompanha o atendimento. O técnico organiza a fila, assume chamados e informa a solução. O administrador gerencia os usuários e as categorias.

## Situação atual

As telas do MVP, autenticação, permissões, schema, repositórios, regras
transacionais e o fluxo persistido do colaborador estão implementados. Os fluxos
persistidos do técnico e da administração continuam nas próximas entregas.

## Tecnologias

- HTML, CSS, JavaScript e Bootstrap na interface.
- PHP puro, com organização MVC simplificada.
- MySQL e PDO para acesso aos dados.
- XAMPP para Apache/PHP e MySQL via XAMPP ou Docker no ambiente local.
- Git e GitHub para versionamento; Figma para as telas.

## Primeira versão

- Login, logout e acesso por perfil: colaborador, técnico e administrador.
- Abertura de chamados com título, descrição, categoria e prioridade.
- Consulta, busca e filtros por status, prioridade e categoria.
- Atribuição de técnico, comentários e histórico.
- Registro de solução e encerramento.
- Cadastro, edição e desativação de usuários e categorias.
- Resumo da quantidade de chamados por situação.

Inventário, SLA, inteligência artificial e integrações ficam fora da primeira versão. Veja o [escopo](docs/PROJECT_DEFINITION.md).

## Como executar

1. Inicie o Apache no XAMPP. Para o banco, use o MySQL do XAMPP ou o ambiente
   Docker documentado abaixo.
2. Clone dentro de `htdocs`:

   ```powershell
   cd C:\xampp\htdocs
   git clone https://github.com/senac-time-projetos/FixIT.git
   cd FixIT
   Copy-Item backend/config/database.example.php backend/config/database.php
   ```

3. No [phpMyAdmin](http://localhost/phpmyadmin), importe `database/database.sql`.
4. Ajuste usuário, senha e porta em `backend/config/database.php`. O nome do banco atual é `projeto_integrador`. Não envie esse arquivo ao GitHub.
5. Acesse [localhost/FixIT](http://localhost/FixIT/).

Se já tiver uma configuração local, preserve-a em vez de copiar o exemplo novamente.

Instruções completas: [configuração do XAMPP](docs/SETUP_XAMPP.md).

Se o MySQL do XAMPP não estiver disponível, siga o
[ambiente MySQL com Docker](docs/SETUP_DOCKER.md). Ele usa a porta `3307` e não
altera os dados do XAMPP.

## Estrutura

```text
FixIT/
├── .github/                 # Templates de Issues e Pull Requests
├── frontend/
│   ├── index.php            # Página inicial
│   ├── pages/              # Telas; admin/ para a área administrativa
│   ├── components/         # Partes compartilhadas das páginas
│   ├── css/
│   ├── js/
│   └── assets/             # Logos, imagens e ícones
├── backend/
│   ├── config/             # Configuração local e exemplo de conexão PDO
│   ├── controllers/        # Recebimento de requisições
│   ├── models/             # Consultas ao banco
│   └── services/           # Regras de negócio
├── database/
│   ├── database.sql        # Instalação limpa das cinco tabelas do FixIT
│   ├── migrations/         # Alterações futuras no banco
│   └── seeds/              # Dados fictícios de demonstração
├── docs/
│   └── referencias/        # Material original do professor
├── index.php               # Redireciona para frontend/
└── README.md
```

Pastas com `.gitkeep` ainda estão vazias. A [arquitetura](docs/ARQUITETURA.md) explica onde colocar cada parte do código.

## Equipe

| Integrante | Responsabilidade |
| --- | --- |
| Alexandre Vieira Santana | Organização, testes e complementos de modelagem e protótipo |
| David de Melo Ferreira | Backend PHP e regras de negócio |
| Douglas Gomes dos Santos | Frontend e UX/UI |
| Pedro Henrique Santiago Ferreira | Revisão do modelo e implementação do banco de dados |

Essa divisão indica a responsabilidade principal; todos participam do desenvolvimento e das revisões.

## Links

- [Telas no Figma](https://www.figma.com/design/cDIySt35PWPGPdHDbzdkR0/FixIT)
- [Documentação do grupo no Notion](https://app.notion.com/p/FixIT-3c4e668ab69a80e8b30ace47a10c5cce)
- [Modelagem no tldraw](https://www.tldraw.com/f/HfDG_NoWIqczhMU4bl14H) — página “FixIT • Banco de dados”
- [Orientações da disciplina](https://deluxe-blue-bc1.notion.site/Projeto-Integrador-II-Aplica-o-Web-Din-mica-305dfca74d8d80ee9cf3dbbb08257c7c)
- [Issues](https://github.com/senac-time-projetos/FixIT/issues) e [Pull Requests](https://github.com/senac-time-projetos/FixIT/pulls)

- [Quadro de tarefas no Trello](https://trello.com/b/ihV3ZVG8/pi-senac-fixit)

O [planejamento](docs/PLANEJAMENTO.md) reúne prioridades, histórias de usuário e a proposta da próxima sprint.

## Contribuição

Use branches e Pull Requests para integrar alterações à `main`. Não versione credenciais ou dados pessoais. Veja [CONTRIBUTING.md](CONTRIBUTING.md).

Outras referências: [documentação](docs/README.md), [banco de dados](database/README.md) e [identidade visual](docs/IDENTIDADE_VISUAL.md).

Para desenvolver uma tarefa, siga o [processo completo](docs/PROCESSO_DESENVOLVIMENTO.md)
e as [especificações canônicas](docs/specs/README.md). Agentes e ferramentas de
desenvolvimento devem respeitar as instruções em `AGENTS.md`.
