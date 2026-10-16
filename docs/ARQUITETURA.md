# Organização do código

O FixIT mantém a estrutura inicial da disciplina: PHP puro e separação simples entre telas, regras e banco. Controllers, models e services ainda não estão implementados.

| Pasta | Responsabilidade |
| --- | --- |
| `frontend/pages/` | Telas e formulários PHP/HTML |
| `frontend/pages/admin/` | Telas administrativas, que deverão verificar sessão e perfil |
| `frontend/components/` | Cabeçalho, navegação e outras partes compartilhadas |
| `frontend/css/`, `js/`, `assets/` | Estilos, interações e arquivos visuais |
| `backend/config/` | Configuração local e exemplo de conexão PDO |
| `backend/controllers/` | Receber requisições, validar sessão e encaminhar operações |
| `backend/services/` | Regras como assumir e encerrar um chamado |
| `backend/models/` | Consultas SQL com parâmetros preparados |
| `database/` | Estrutura SQL, alterações e dados de teste |

## Pontos de entrada

O `index.php` da raiz redireciona para `frontend/index.php`. A página inicial aponta para `frontend/pages/login.php`, que ainda é provisória.

`database.example.php` define os parâmetros de banco e `connection.example.php` mostra uma conexão PDO. Nenhum deles está integrado às páginas atuais.

## Fluxo previsto

```text
Formulário → Controller → Service (quando necessário) → Model → MySQL
                  ↓
          Resposta ou redirecionamento para a tela
```

Use caminhos baseados em `__DIR__` para incluir arquivos PHP. Evite SQL nas telas e conexão duplicada em cada página.

Permissões precisam ser verificadas no servidor, inclusive em requisições POST. Ao assumir ou encerrar um chamado, grave a alteração e o histórico na mesma transação.

A área administrativa ainda não está protegida. Nesta etapa, o projeto é uma base para desenvolvimento local.
