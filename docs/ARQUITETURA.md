# Organização do código

O FixIT mantém PHP puro e separação simples entre telas, regras e banco. A conexão
PDO e os repositórios estão implementados; controllers e services de negócio são
a próxima camada.

| Pasta | Responsabilidade |
| --- | --- |
| `frontend/pages/` | Telas e formulários PHP/HTML |
| `frontend/pages/admin/` | Telas administrativas, que deverão verificar sessão e perfil |
| `frontend/components/` | Cabeçalho, navegação e outras partes compartilhadas |
| `frontend/css/`, `js/`, `assets/` | Estilos, interações e arquivos visuais |
| `backend/config/` | Configuração local, fábrica PDO e bootstrap da conexão |
| `backend/controllers/` | Receber requisições, validar sessão e encaminhar operações |
| `backend/services/` | Regras como assumir e encerrar um chamado |
| `backend/models/` | Repositórios com consultas SQL e parâmetros preparados |
| `database/` | Estrutura SQL, alterações e dados de teste |

## Pontos de entrada

O `index.php` da raiz redireciona para `frontend/index.php`. A página inicial aponta para `frontend/pages/login.php`, que ainda é provisória.

`database.example.php` define os parâmetros locais, `PdoConnection.php` cria a conexão
e controla transações e `connection.php` é o bootstrap compartilhado. As páginas
mantêm contratos de apresentação estáveis e são ligadas aos controllers por fluxo.
O `compose.yaml` oferece MySQL 8.4 isolado em `127.0.0.1:3307`, inicializado pelo
mesmo schema e seed usados na instalação manual, sem depender do MySQL do XAMPP.
Quando `DATABASE_URL` está definida, `connection.php` usa PostgreSQL com TLS no
lugar do arquivo local. O schema equivalente fica em `database/postgresql.sql`;
IDs de inserts usam `RETURNING` nesse driver e `lastInsertId()` nos demais.

`backend/bootstrap.php` entrega a conexão, os cinco repositórios, o
`TicketService` e os controllers de chamados em um único array, evitando
instanciação e conexão duplicadas.
O serviço centraliza abertura, atribuição, comentários, atualizações e
encerramento, incluindo autorização e transações com o histórico.
`CollaboratorTicketController` adapta os registros persistidos ao contrato das
telas e coordena lista, abertura, detalhe e comentários do colaborador.
`TechnicianTicketController` faz a mesma fronteira para a fila geral, abertura
pelo técnico, atribuição, atualização e encerramento. As mutações permanecem no
`TicketService`, que valida perfil, vínculo e executa chamado e histórico na
mesma transação.
`AdministrationController` expõe usuários e categorias somente ao perfil
administrador. O `AdministrationService` centraliza validação, unicidade, hash de
senha e desativação; os repositórios continuam responsáveis pelo SQL preparado.
O mesmo controller entrega o resumo administrativo calculado por agregações do
`TicketRepository`, sem tabela de dashboard ou totais mantidos manualmente.

`backend/security/` concentra sessão, CSRF e autorização; `AuthService` valida
credenciais e mantém na sessão somente os dados mínimos do usuário. As páginas
autenticadas carregam `frontend/components/session_context.php`, que nega acesso
por padrão e prepara navegação e logout conforme o perfil.

## Fluxo previsto

```text
Formulário → Controller → Service (quando necessário) → Model → MySQL
                  ↓
          Resposta ou redirecionamento para a tela
```

Use caminhos baseados em `__DIR__` para incluir arquivos PHP. Evite SQL nas telas e conexão duplicada em cada página.

Permissões precisam ser verificadas no servidor, inclusive em requisições POST. Ao assumir ou encerrar um chamado, grave a alteração e o histórico na mesma transação.

As áreas autenticadas negam acesso por padrão e restringem cada perfil no
servidor. Os fluxos persistidos do colaborador e do técnico exigem sessão e CSRF
e derivam solicitante, autor e responsável do contexto autenticado.
Os cadastros administrativos também exigem sessão, CSRF e perfil administrativo;
usuários e categorias são desativados sem exclusão física.
