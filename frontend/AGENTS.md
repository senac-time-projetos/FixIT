# Instruções para o frontend

Estas regras complementam o `AGENTS.md` da raiz para arquivos em `frontend/`.

## Referências

- Leia `docs/specs/FRONTEND_MVP_SPEC.md` antes de alterar telas.
- Use como referência canônica os frames de alta fidelidade listados nessa spec.
- Não implemente protótipos antigos ou wireframes BF/MF como versão final.

## Estrutura e componentes

- Use PHP/HTML sem framework adicional, CSS e JavaScript progressivo.
- Extraia cabeçalho, navegação, mensagens, campos, botões, cards e listas/tabelas
  repetidos para componentes compartilhados.
- Evite estilos inline e JavaScript embutido, exceto quando a spec justificar.
- Mantenha HTML semântico e navegação funcional mesmo sem JavaScript.
- Toda saída vinda do usuário ou banco deve ser escapada com
  `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

## Estados obrigatórios

Cada tela deve tratar, quando aplicável: carregando, vazio, sucesso, validação,
erro inesperado, sessão expirada e permissão negada. Dados demonstrativos não
podem permanecer como se fossem dados reais após a integração.

## Responsividade e acessibilidade

- Implemente primeiro a estrutura semântica e depois a adaptação visual.
- Tabelas devem continuar utilizáveis em telas pequenas; quando virarem cartões,
  preserve rótulos e ações.
- Todo campo precisa de `label`, mensagem de erro associada e foco visível.
- Use teclado para todas as ações, ordem de foco lógica e um único `h1` por página.
- Não dependa apenas de cor para comunicar prioridade, status, erro ou sucesso.
- Mantenha contraste adequado e respeite redução de movimento.

## Verificação

Confira desktop e largura móvel, teclado, campos vazios, textos longos, estado sem
registros e mensagens do servidor.
