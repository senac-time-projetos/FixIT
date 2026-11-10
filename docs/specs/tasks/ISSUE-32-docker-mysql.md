# ISSUE-32 — Adicionar ambiente MySQL local com Docker

Status: Em revisão

Issue: `#32`

## Contexto

O MariaDB do XAMPP local não inicia por corrupção preexistente do InnoDB. O
desenvolvimento continuará em MySQL Docker sem modificar ou reparar os arquivos
do XAMPP.

## Solução

- MySQL 8.4 exposto somente em `127.0.0.1:3307`.
- Volume nomeado `fixit_mysql_data`, independente do XAMPP.
- Schema e seed atuais montados como inicializadores somente leitura.
- Healthcheck antes do uso pela aplicação.
- Exemplo de configuração PDO específico para Docker, sem credencial real.
- Teste de integração executado contra o MySQL real.
- IDs de entidades usam `ON UPDATE RESTRICT`, permitindo que o MySQL 8.4 aplique
  os `CHECK` da tabela de chamados e preservando identificadores imutáveis.

## Critérios de aceite

- [x] Compose é válido e não usa a porta `3306`.
- [x] Container alcança estado saudável.
- [x] Instalação limpa cria as cinco tabelas e aplica o seed.
- [x] PHP conecta com `pdo_mysql` em `127.0.0.1:3307`.
- [x] Banco usa `utf8mb4` e executa transação com rollback.
- [x] Configuração local da aplicação aponta para o container.
- [x] Instruções de iniciar, parar, diagnosticar e recriar estão documentadas.
- [x] Testes existentes continuam aprovados.
- [x] XAMPP permanece intocado.

## Segurança e recuperação

- A porta é publicada apenas no loopback local.
- A conta root sem senha é aceita somente neste ambiente local isolado.
- Nenhuma senha real ou dado pessoal entra no repositório.
- `docker compose down` preserva o volume; `--volumes` exige descarte consciente.
- O caminho `C:\xampp\mysql\data` não faz parte de nenhum volume ou comando.

## Processo

O cartão Trello aguarda somente a conclusão do login da conta Primary já aberto
no navegador. Issue, branch, spec, testes e PR permanecem rastreáveis enquanto
isso.

## Evidências

- Container `fixit-db-1` saudável com MySQL `8.4.13` e porta
  `127.0.0.1:3307`.
- Instalação limpa refeita após detectar e corrigir a incompatibilidade entre
  `CHECK` e `ON UPDATE CASCADE` no MySQL 8.4.
- `tests/docker_mysql_test.php` aprovou tabelas, seed, autenticação bcrypt,
  `utf8mb4` e rollback no MySQL real.
- Oito suítes anteriores e a nova suíte Docker passaram sem regressões.
