# Processo completo de desenvolvimento

Este processo garante que cada mudança do FixIT seja rastreável do planejamento à
entrega. Escrever código é uma etapa, não a conclusão da tarefa.

## 1. Preparar o trabalho

Para funcionalidades, correções e tarefas do produto, crie ou confirme um cartão
no Trello. O cartão deve ter objetivo, responsável, prioridade, critérios de
aceite e dependências. Specs e instruções de agentes são a única exceção ao
cartão obrigatório.

Crie uma Issue correspondente no GitHub. Não use uma Issue genérica para várias
entregas independentes. Vincule Trello e Issue nos dois sentidos quando houver
cartão.

Mudanças funcionais ou de risco relevante recebem uma spec em
`docs/specs/tasks/ISSUE-<numero>-<slug>.md`. A spec registra comportamento,
limites, dados, estados, segurança, verificações e decisões abertas.

## 2. Desenvolver

Crie a branch a partir da `main` atualizada:

```text
feature/<descricao>
fix/<descricao>
docs/<descricao>
test/<descricao>
chore/<descricao>
```

Mova o cartão para `Doing`, quando aplicável. Implemente somente o escopo aprovado
e mantenha a spec atualizada com descobertas materiais. Commits devem ser pequenos,
coerentes e escritos no imperativo, por exemplo:

```text
feat: adiciona abertura de chamados
fix: restringe chamados ao solicitante
docs: define contrato do frontend
test: cobre encerramento sem solução
```

## 3. Verificar

Execute as verificações da área e registre evidências observadas. No mínimo:

- `php -l` em todos os PHP alterados;
- testes automatizados relevantes;
- roteiro manual para comportamentos ainda sem automação;
- `git diff --check`;
- revisão de credenciais, dados pessoais e arquivos locais;
- conferência dos critérios de aceite.

Não use “deve funcionar” como evidência. Informe comando ou passo executado,
resultado e qualquer limitação.

## 4. Revisar e integrar

Faça push da branch e abra um PR com:

- `Closes #<issue>`;
- link do cartão, quando aplicável;
- caminho da spec;
- resumo da solução;
- verificações e resultados;
- capturas para mudanças visuais;
- riscos, migrations e passos de recuperação.

Mova o cartão para `Em revisão`. Resolva comentários sem ampliar silenciosamente
o escopo. Alterações de requisito voltam à Issue e à spec.

Depois da aprovação e das verificações verdes, integre o PR. Exclusão da branch é
opcional, mas a referência do PR deve permanecer.

## 5. Encerrar

Confirme que a Issue foi fechada pelo merge, que a `main` contém a mudança e que a
documentação descreve o estado real. No Trello, marque checklist, adicione links da
Issue e do PR e mova o cartão para `Done`.

Se qualquer etapa não puder ser concluída, a tarefa permanece em `Blocking` ou
`Em revisão`; registre o impedimento e a condição para retomada.
