# Instruções para o backend

Estas regras complementam o `AGENTS.md` da raiz para arquivos em `backend/`.

## Organização

- Controllers recebem a requisição, validam sessão/método e coordenam a resposta.
- Services concentram regras de negócio e transações.
- Models concentram consultas preparadas e mapeamento de dados.
- Configuração e conexão são centralizadas; páginas não abrem conexões próprias.
- Não coloque SQL ou regra de autorização em templates do frontend.

## Contratos e segurança

- Use tipos e formatos definidos na spec e no esquema SQL; não invente valores de
  status, prioridade ou perfil em pontos isolados.
- Negue acesso por padrão e valide o perfil no servidor.
- O colaborador só consulta chamados próprios; técnico e administrador seguem a
  matriz de permissões da spec.
- Normalize, valide e limite entradas antes de persistir.
- Mensagens ao usuário não devem expor exceções, SQL, caminhos ou credenciais.
- Logs não devem conter senhas, hashes, cookies, tokens ou conteúdo sensível.

## Operações críticas

Assumir chamado, alterar status, registrar solução, encerrar e gerar histórico
devem manter consistência. Quando mais de uma gravação representa a mesma ação,
use transação e rollback em falha.
