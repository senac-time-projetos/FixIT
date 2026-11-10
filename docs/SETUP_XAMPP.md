# Executando no XAMPP

Instale o XAMPP com Apache, PHP e MySQL/MariaDB. A conexão de exemplo usa `pdo_mysql`. O Bootstrap é carregado por CDN e depende de internet enquanto não houver uma cópia local.

## Clonar

No PowerShell:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/senac-time-projetos/FixIT.git
cd FixIT
Copy-Item backend/config/database.example.php backend/config/database.php
```

Se já tiver o repositório, entre na pasta existente. Só copie a configuração se `database.php` ainda não existir, para preservar os seus ajustes.

## Banco

1. Inicie Apache e MySQL no XAMPP.
2. Abra [phpMyAdmin](http://localhost/phpmyadmin).
3. Na aba **Importar**, selecione `database/database.sql`.
4. O script cria `projeto_integrador` e as tabelas `usuarios`, `categorias`,
   `chamados`, `comentarios` e `historico_chamados`.
5. Em `backend/config/database.php`, ajuste host, porta, usuário e senha. O banco atual é `projeto_integrador`.
6. Opcionalmente, importe `database/seeds/demo.sql` para criar as três contas
   fictícias e quatro categorias de demonstração.

Não envie a configuração local ao GitHub. Ela está no .gitignore.

Se o template antigo já foi importado, faça backup e siga a migration descrita em
[database/README.md](../database/README.md). A tabela antiga `users` é preservada.

## Abrir

Acesse [localhost/FixIT](http://localhost/FixIT/). A raiz redireciona para a página inicial em frontend/.

As telas ainda não iniciam sessão automaticamente; a conexão e os repositórios
estão prontos para a próxima etapa de controllers e autenticação.

## Conferência

- A página inicial mostra FixIT.
- Login abre e Voltar retorna à página inicial.
- As cinco tabelas do FixIT aparecem no phpMyAdmin depois da importação.
- A configuração local não aparece em `git status --short`.

Se o PHP aparecer como texto, abra a página pelo Apache, não por duplo clique no arquivo. Se a URL não existir, confira a pasta dentro de htdocs. Se o Apache usar outra porta, inclua-a na URL: `http://localhost:8080/FixIT/`.
