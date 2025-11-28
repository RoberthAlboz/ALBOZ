-- 1. CRIAÇÃO DO BANCO DE DADOS
CREATE DATABASE IF NOT EXISTS alboz;
USE alboz;

-- 2. TABELA DE USUÁRIOS
-- Quem acessa o sistema (Admin)
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    inscricao_estadual VARCHAR(30),
    cnpj VARCHAR(20) NOT NULL UNIQUE,
    rua VARCHAR(150),
    numero VARCHAR(20),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    uf CHAR(2),
    email VARCHAR(150) NOT NULL UNIQUE, -- Usado para login
    celular VARCHAR(20),
    senha VARCHAR(255) NOT NULL,        -- Senha (pode ser hash ou texto puro dependendo do login.php)
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. TABELA DE FORNECEDORES (Distribuidores)
-- Vinculado a quem cadastrou (usuario_id)
CREATE TABLE fornecedores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,            -- Dono do fornecedor
    
    nome_fornecedor VARCHAR(100) NOT NULL,
    cnpj VARCHAR(20) NOT NULL,
    endereco VARCHAR(255),
    telefone VARCHAR(20),
    email VARCHAR(100),
    observacoes TEXT,
    imagem VARCHAR(255),                -- Caminho da logo (img_fornecedores/...)
    
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuario_fornecedor
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE
);

-- 4. TABELA DE PRODUTOS
-- Vinculado a um fornecedor específico
CREATE TABLE produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fornecedor_id INT NOT NULL,         -- De qual fornecedor é
    
    nome_produto VARCHAR(100) NOT NULL,
    codigo_produto VARCHAR(50),         -- SKU
    descricao TEXT,
    quantidade_estoque INT DEFAULT 0,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    imagem VARCHAR(255),                -- Caminho da foto (img_produtos/...)
    
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_fornecedor_produto
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
    ON DELETE CASCADE
);

-- 5. TABELA DE SUPORTE
-- Registra os chamados abertos pelos usuários
CREATE TABLE suporte (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    
    assunto VARCHAR(200) NOT NULL,
    mensagem TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'Aberto', -- Aberto, Em andamento, Fechado
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE
);

-- (OPCIONAL) Inserir um Usuário Admin de Teste Inicial e dados básicos
-- LEMBRE-SE DO E-MAIL E DA SENHA PARA LOGIN NO FUTURO
-- Senha "12345"
INSERT INTO usuarios (nome, email, senha, cnpj) 
VALUES ('Admin Inicial', 'admin@teste.com', '12345', '00.000.000/0001-99');

-- FIM DO ARQUIVO DE CRIAÇÃO DO BANCO DE DADOS ALBOZ