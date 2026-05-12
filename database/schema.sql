-- ============================================================
-- BANCO DE DADOS: IPIL News Portal
-- Instituto Politécnico Industrial de Luanda
-- ============================================================

CREATE DATABASE IF NOT EXISTS ipil_news CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ipil_news;
-- ------------------------------------------------------------
-- TABELA: roles
-- Define os tipos de utilizadores do sistema
-- ------------------------------------------------------------
CREATE TABLE roles (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(255)
) ENGINE=InnoDB;

INSERT INTO roles (nome, descricao) VALUES
    ('admin',    'Administrador do sistema – pode gerir tudo'),
    ('diretor',  'Diretor da instituição'),
    ('professor','Docente do IPIL'),
    ('aluno',    'Estudante matriculado no IPIL');

-- ------------------------------------------------------------
-- TABELA: validation_codes
-- O Admin cria estes códigos antes de qualquer cadastro.
-- Para alunos: número de matrícula (ex: 20240001)
-- Para professores/diretores: código de funcionário (ex: F-2024-042)
-- Um código é marcado como "usado" assim que o utilizador se regista.
-- ------------------------------------------------------------
CREATE TABLE validation_codes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    codigo     VARCHAR(100) NOT NULL UNIQUE,
    role_id    INT NOT NULL,
    usado      TINYINT(1) NOT NULL DEFAULT 0,
    criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: users
-- Armazena todos os utilizadores do portal.
-- O campo validation_code_id garante que o registo foi validado.
-- ------------------------------------------------------------
CREATE TABLE users (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    nome                 VARCHAR(150) NOT NULL,
    email                VARCHAR(191) NOT NULL UNIQUE,
    senha_hash           VARCHAR(255) NOT NULL,
    role_id              INT NOT NULL,
    validation_code_id   INT UNIQUE,            -- NULL apenas para o admin inicial
    ativo                TINYINT(1) NOT NULL DEFAULT 1,
    criado_em            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id)            REFERENCES roles(id),
    FOREIGN KEY (validation_code_id) REFERENCES validation_codes(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: news
-- Apenas utilizadores com role = 'admin' podem inserir registos.
-- ------------------------------------------------------------
CREATE TABLE news (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(255) NOT NULL,
    corpo       TEXT NOT NULL,
    imagem_url  VARCHAR(500),
    autor_id    INT NOT NULL,
    publicado   TINYINT(1) NOT NULL DEFAULT 1,
    criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ADMIN INICIAL (senha padrão: Admin@IPIL2024 – MUDAR no 1.º login)
-- Gerado com PHP: password_hash('Admin@IPIL2024', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO users (nome, email, senha_hash, role_id, validation_code_id)
SELECT
    'Administrador IPIL',
    'admin@ipil.ao',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    r.id,
    NULL
FROM roles r WHERE r.nome = 'admin';

-- Exemplos de códigos de validação para teste
-- O admin cria estes via painel; aqui estão apenas exemplos de seed
INSERT INTO validation_codes (codigo, role_id) VALUES
    ('20240001', (SELECT id FROM roles WHERE nome = 'aluno')),
    ('20240002', (SELECT id FROM roles WHERE nome = 'aluno')),
    ('F-2024-001', (SELECT id FROM roles WHERE nome = 'professor')),
    ('D-2024-001', (SELECT id FROM roles WHERE nome = 'diretor'));
