-- FixIT — instalação limpa do banco MySQL/MariaDB
-- Compatível com XAMPP. Execute este arquivo com um usuário autorizado a criar bancos.

CREATE DATABASE IF NOT EXISTS projeto_integrador
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE projeto_integrador;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('colaborador', 'tecnico', 'administrador') NOT NULL DEFAULT 'colaborador',
    situacao ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuarios_email UNIQUE (email),
    INDEX idx_usuarios_perfil_situacao (perfil, situacao)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    descricao VARCHAR(255) NULL,
    situacao ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_categorias_nome UNIQUE (nome),
    INDEX idx_categorias_situacao_nome (situacao, nome)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chamados (
    numero BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    solicitante_id INT UNSIGNED NOT NULL,
    categoria_id INT UNSIGNED NOT NULL,
    tecnico_id INT UNSIGNED NULL,
    titulo VARCHAR(160) NOT NULL,
    descricao TEXT NOT NULL,
    prioridade ENUM('baixa', 'media', 'alta') NOT NULL,
    status ENUM('aberto', 'em_atendimento', 'encerrado') NOT NULL DEFAULT 'aberto',
    solucao TEXT NULL,
    aberto_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    encerrado_em TIMESTAMP NULL,
    CONSTRAINT fk_chamados_solicitante FOREIGN KEY (solicitante_id)
        REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_chamados_categoria FOREIGN KEY (categoria_id)
        REFERENCES categorias (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_chamados_tecnico FOREIGN KEY (tecnico_id)
        REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_chamados_tecnico CHECK (status <> 'em_atendimento' OR tecnico_id IS NOT NULL),
    CONSTRAINT chk_chamados_solucao CHECK (status <> 'encerrado' OR (solucao IS NOT NULL AND CHAR_LENGTH(TRIM(solucao)) >= 10)),
    INDEX idx_chamados_solicitante_status_data (solicitante_id, status, aberto_em),
    INDEX idx_chamados_tecnico_status (tecnico_id, status),
    INDEX idx_chamados_categoria_status (categoria_id, status),
    INDEX idx_chamados_prioridade_status (prioridade, status),
    INDEX idx_chamados_status_data (status, aberto_em)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comentarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chamado_numero BIGINT UNSIGNED NOT NULL,
    autor_id INT UNSIGNED NOT NULL,
    conteudo VARCHAR(1000) NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comentarios_chamado FOREIGN KEY (chamado_numero)
        REFERENCES chamados (numero) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_comentarios_autor FOREIGN KEY (autor_id)
        REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_comentarios_chamado_data (chamado_numero, criado_em),
    INDEX idx_comentarios_autor (autor_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historico_chamados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chamado_numero BIGINT UNSIGNED NOT NULL,
    autor_id INT UNSIGNED NULL,
    evento VARCHAR(50) NOT NULL,
    descricao VARCHAR(500) NOT NULL,
    estado_anterior VARCHAR(50) NULL,
    estado_novo VARCHAR(50) NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historico_chamado FOREIGN KEY (chamado_numero)
        REFERENCES chamados (numero) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_historico_autor FOREIGN KEY (autor_id)
        REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_historico_chamado_data (chamado_numero, criado_em),
    INDEX idx_historico_evento (evento)
) ENGINE=InnoDB;
