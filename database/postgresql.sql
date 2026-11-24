-- Schema PostgreSQL do FixIT. Idempotente e sem remoção de dados.

CREATE TABLE IF NOT EXISTS usuarios (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL DEFAULT 'colaborador' CHECK (perfil IN ('colaborador', 'tecnico', 'administrador')),
    situacao VARCHAR(10) NOT NULL DEFAULT 'ativo' CHECK (situacao IN ('ativo', 'inativo')),
    criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_usuarios_perfil_situacao ON usuarios (perfil, situacao);
CREATE UNIQUE INDEX IF NOT EXISTS uq_usuarios_email_lower ON usuarios (LOWER(email));

CREATE TABLE IF NOT EXISTS categorias (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(80) NOT NULL UNIQUE,
    descricao VARCHAR(255),
    situacao VARCHAR(10) NOT NULL DEFAULT 'ativo' CHECK (situacao IN ('ativo', 'inativo')),
    criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_categorias_situacao_nome ON categorias (situacao, nome);
CREATE UNIQUE INDEX IF NOT EXISTS uq_categorias_nome_lower ON categorias (LOWER(nome));

CREATE TABLE IF NOT EXISTS chamados (
    numero BIGSERIAL PRIMARY KEY,
    solicitante_id BIGINT NOT NULL REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    categoria_id BIGINT NOT NULL REFERENCES categorias(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    tecnico_id BIGINT REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    titulo VARCHAR(160) NOT NULL,
    descricao TEXT NOT NULL,
    prioridade VARCHAR(10) NOT NULL CHECK (prioridade IN ('baixa', 'media', 'alta')),
    status VARCHAR(20) NOT NULL DEFAULT 'aberto' CHECK (status IN ('aberto', 'em_atendimento', 'encerrado')),
    solucao TEXT,
    aberto_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    encerrado_em TIMESTAMPTZ,
    CONSTRAINT ck_chamados_atendimento CHECK (status <> 'em_atendimento' OR tecnico_id IS NOT NULL),
    CONSTRAINT ck_chamados_encerramento CHECK (status <> 'encerrado' OR (tecnico_id IS NOT NULL AND solucao IS NOT NULL AND encerrado_em IS NOT NULL))
);

CREATE INDEX IF NOT EXISTS idx_chamados_solicitante_status ON chamados (solicitante_id, status);
CREATE INDEX IF NOT EXISTS idx_chamados_tecnico_status ON chamados (tecnico_id, status);
CREATE INDEX IF NOT EXISTS idx_chamados_categoria_prioridade ON chamados (categoria_id, prioridade);
CREATE INDEX IF NOT EXISTS idx_chamados_aberto_em ON chamados (aberto_em DESC);

CREATE TABLE IF NOT EXISTS comentarios (
    id BIGSERIAL PRIMARY KEY,
    chamado_numero BIGINT NOT NULL REFERENCES chamados(numero) ON UPDATE RESTRICT ON DELETE RESTRICT,
    autor_id BIGINT NOT NULL REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    conteudo TEXT NOT NULL,
    criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_comentarios_chamado_data ON comentarios (chamado_numero, criado_em, id);

CREATE TABLE IF NOT EXISTS historico_chamados (
    id BIGSERIAL PRIMARY KEY,
    chamado_numero BIGINT NOT NULL REFERENCES chamados(numero) ON UPDATE RESTRICT ON DELETE RESTRICT,
    autor_id BIGINT REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    evento VARCHAR(40) NOT NULL,
    descricao VARCHAR(500) NOT NULL,
    estado_anterior VARCHAR(100),
    estado_novo VARCHAR(100),
    criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_historico_chamado_data ON historico_chamados (chamado_numero, criado_em, id);

CREATE OR REPLACE FUNCTION fixit_touch_atualizado_em()
RETURNS TRIGGER AS $$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_usuarios_atualizado_em ON usuarios;
CREATE TRIGGER trg_usuarios_atualizado_em BEFORE UPDATE ON usuarios
FOR EACH ROW EXECUTE FUNCTION fixit_touch_atualizado_em();

DROP TRIGGER IF EXISTS trg_categorias_atualizado_em ON categorias;
CREATE TRIGGER trg_categorias_atualizado_em BEFORE UPDATE ON categorias
FOR EACH ROW EXECUTE FUNCTION fixit_touch_atualizado_em();

DROP TRIGGER IF EXISTS trg_chamados_atualizado_em ON chamados;
CREATE TRIGGER trg_chamados_atualizado_em BEFORE UPDATE ON chamados
FOR EACH ROW EXECUTE FUNCTION fixit_touch_atualizado_em();
