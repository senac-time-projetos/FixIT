-- Migration 001 — cria o modelo FixIT sem remover a tabela legada `users`.
-- Execute a partir da raiz do repositório com o cliente mysql.
-- Recuperação: restaure o backup; não remova tabelas com dados sem revisão.

SOURCE database/database.sql;

-- A tabela `users` do template, quando existir, é preservada deliberadamente.
-- Migre e confira seus dados antes de removê-la em uma migration futura.
