# Como contribuir

Registre nas Issues o problema, o que será feito e como conferir a entrega.

## Branch e commit

Partindo da main atualizada:

```bash
git switch main
git pull origin main
git switch -c feature/abertura-chamado
```

Use `feature/` para funcionalidades, `fix/` para correções e `docs/` para documentação.

```bash
git add caminho/do/arquivo
git diff --cached
git commit -m "feat: adiciona formulário de abertura de chamado"
git push -u origin feature/abertura-chamado
```

## Revisão

Abra um Pull Request para main, relacione a Issue quando houver e explique como testar. Peça a revisão de outro integrante antes do merge. Não desenvolva diretamente na main nem use force push em branches compartilhadas.

Antes de pedir revisão:

- Execute o fluxo alterado no XAMPP e confira os cenários de erro.
- Verifique a sintaxe dos PHP alterados: `php -l caminho/do/arquivo.php`.
- Atualize SQL e documentação quando necessário.
- Confira o Figma e a interface em telas menores, quando houver alteração visual.
- Não envie `backend/config/database.php`, senhas ou dados pessoais.

O projeto usa PHP puro. Combine com o grupo antes de incluir frameworks ou dependências.
