# FixIT

Sistema de chamados de suporte de TI desenvolvido no Projeto Integrador II do Senac.

O colaborador registra um problema e acompanha o atendimento. O técnico organiza a fila, assume chamados e informa a solução. O administrador gerencia os usuários e as categorias.

## Situação atual

O projeto está na fase de estruturação. Existem uma página inicial em PHP, uma tela provisória de login, Bootstrap e exemplos de configuração do banco. **Autenticação, permissões e CRUD ainda não estão implementados.**

O SQL atual veio do template: cria o banco `projeto_integrador` e a tabela `users`. As cinco tabelas do modelo do FixIT ainda precisam ser implementadas.

## Tecnologias

- HTML, CSS, JavaScript e Bootstrap na interface.
- PHP puro, com organização MVC simplificada.
- MySQL e PDO para acesso aos dados.
- XAMPP e phpMyAdmin no ambiente local.
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

1. Inicie Apache e MySQL no XAMPP.
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

Se já tiver uma configuração local, preserve-a em vez de copiar o exemplo novamente. A página inicial confirma que o PHP executa; o login é provisório e não consulta o banco. Não há conta de teste nesta etapa.

Instruções completas: [configuração do XAMPP](docs/SETUP_XAMPP.md).

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
│   ├── database.sql        # SQL inicial; modelo FixIT ainda pendente
│   ├── migrations/         # Alterações futuras no banco
│   └── seeds/              # Dados de teste futuros
├── docs/
│   └── referencias/        # Material original do professor
├── index.php               # Redireciona para frontend/
└── README.md
```

Pastas com `.gitkeep` ainda estão vazias. A [arquitetura](docs/ARQUITETURA.md) explica onde colocar cada parte do código.

## Equipe

| Integrante | Responsabilidade |
| --- | --- |
| Alexandre Vieira Santana | Organização do projeto e testes |
| David de Melo Ferreira | Backend PHP e regras de negócio |
| Douglas Gomes dos Santos | Frontend e UX/UI |
| Pedro Henrique Santiago Ferreira | Modelagem e banco de dados |

Essa divisão indica a responsabilidade principal; todos participam do desenvolvimento e das revisões.

## Links

- [Telas no Figma](https://www.figma.com/design/cDIySt35PWPGPdHDbzdkR0/FixIT)
- [Documentação do grupo no Notion](https://app.notion.com/p/FixIT-3c4e668ab69a80e8b30ace47a10c5cce)
- [Modelagem no tldraw](https://www.tldraw.com/f/HfDG_NoWIqczhMU4bl14H) — página “FixIT • Banco de dados”
- [Orientações da disciplina](https://deluxe-blue-bc1.notion.site/Projeto-Integrador-II-Aplica-o-Web-Din-mica-305dfca74d8d80ee9cf3dbbb08257c7c)
- [Issues](https://github.com/senac-time-projetos/FixIT/issues) e [Pull Requests](https://github.com/senac-time-projetos/FixIT/pulls)

O link de um board específico de tarefas ainda precisa ser informado pela equipe.

## Contribuição

Use branches e Pull Requests para integrar alterações à `main`. Não versione credenciais ou dados pessoais. Veja [CONTRIBUTING.md](CONTRIBUTING.md).

Outras referências: [documentação](docs/README.md), [banco de dados](database/README.md) e [identidade visual](docs/IDENTIDADE_VISUAL.md).
