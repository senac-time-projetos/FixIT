-- Dados totalmente fictícios para demonstração local.
-- As senhas estão armazenadas somente como hashes bcrypt gerados pelo PHP.

USE projeto_integrador;

INSERT INTO usuarios (nome, email, senha_hash, perfil, situacao) VALUES
    ('Mariana Oliveira', 'mariana@fixit.test', '$2y$12$kmT6uevTPpZAMZxOUFZR5uw7uuydgBgf1PIphV37XHoyb5hmKfmSy', 'colaborador', 'ativo'),
    ('Douglas Gomes', 'douglas@fixit.test', '$2y$12$CId64KZGh1GUy5miI1JLNOM.rvrSzW27WyDtoTsVcqrSeU3JHIQfm', 'tecnico', 'ativo'),
    ('Administrador FixIT', 'admin@fixit.test', '$2y$12$hVh9MBbz5kS7F0fQ6R8RnOV8qbuktp9o7AMJrJd7xSxprsteBTYLO', 'administrador', 'ativo')
ON DUPLICATE KEY UPDATE nome = VALUES(nome), perfil = VALUES(perfil), situacao = VALUES(situacao);

INSERT INTO categorias (nome, descricao, situacao) VALUES
    ('Impressora', 'Impressoras, scanners e filas de impressão.', 'ativo'),
    ('Acesso', 'Contas, permissões e acesso a sistemas.', 'ativo'),
    ('Software', 'Instalação e suporte a programas.', 'ativo'),
    ('Rede', 'Conectividade cabeada, Wi-Fi e VPN.', 'ativo')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao), situacao = VALUES(situacao);
