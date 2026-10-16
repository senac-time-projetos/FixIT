# Banco de dados

## Estado atual

`database.sql` veio do template. Cria o banco `projeto_integrador` e a tabela `users`, sem dados de teste. O nome do banco é o mesmo de `backend/config/database.example.php`.

Importar o script prepara a base inicial, mas não habilita o login.

## Modelo planejado

O [diagrama no tldraw](https://www.tldraw.com/f/HfDG_NoWIqczhMU4bl14H), na página “FixIT • Banco de dados”, prevê:

| Tabela | Conteúdo |
| --- | --- |
| `usuarios` | Nome, e-mail, hash da senha, perfil e situação |
| `categorias` | Classificação dos problemas e situação |
| `chamados` | Solicitação, prioridade, status, técnico e solução |
| `comentarios` | Mensagens vinculadas ao chamado e ao autor |
| `historico_chamados` | Registro de abertura e alterações |

Solicitante e técnico referenciam usuarios; cada chamado tem uma categoria. Comentários e histórico referenciam um chamado e um usuário.

**O modelo ainda precisa ser implementado no SQL.** A tabela users do template será substituída em uma tarefa própria, após conferir se há dados locais a preservar.

## Arquivos

- `database.sql`: estrutura para instalação nova.
- `migrations/`: alterações futuras em bancos existentes.
- `seeds/`: dados fictícios de teste; ainda não há seeds.

Versione mudanças de esquema junto com o código que depende delas. Não envie dados pessoais ou senhas em texto puro. Use `password_hash()` e `password_verify()` no PHP.
