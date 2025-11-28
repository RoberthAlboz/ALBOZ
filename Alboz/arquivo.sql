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

-- ==========================================================
-- 1. INSERINDO 5 FORNECEDORES (Assumindo usuario_id = 1)
-- ==========================================================

INSERT INTO fornecedores (usuario_id, nome_fornecedor, cnpj, endereco, telefone, email, observacoes) VALUES 
(1, 'Royal Golf Imports', '12.345.678/0001-90', 'Av. das Nações Unidas, 1000, São Paulo - SP', '(11) 3030-1010', 'contato@royalgolf.com.br', 'Especialista em equipamentos de Golfe profissionais.'),
(1, 'Grand Slam Tennis', '98.765.432/0001-15', 'Rua Oscar Freire, 500, São Paulo - SP', '(11) 3030-2020', 'vendas@grandslam.com.br', 'Importadora oficial de raquetes e acessórios de quadra.'),
(1, 'Haras Equitação & Cia', '45.123.789/0001-22', 'Rodovia Raposo Tavares, Km 25, Cotia - SP', '(11) 4700-5050', 'comercial@harasequip.com.br', 'Focada em selaria e vestuário para hipismo clássico.'),
(1, 'Náutica Blue Sea', '33.555.777/0001-88', 'Marina da Glória, Loja 5, Rio de Janeiro - RJ', '(21) 2555-8888', 'contato@bluesea.com.br', 'Equipamentos de segurança e vestuário para vela.'),
(1, 'Gentleman Sports', '10.203.405/0001-01', 'Av. Batel, 150, Curitiba - PR', '(41) 3232-9090', 'admin@gentlemansports.com.br', 'Distribuidora de artigos diversos: Polo, Bilhar e Tiro com Arco.');

-- ==========================================================
-- 2. INSERINDO 100 PRODUTOS (Distribuídos entre os IDs 1 a 5)
-- ==========================================================

-- Nota: Certifique-se de que os fornecedores acima foram criados com IDs 1, 2, 3, 4 e 5.
-- Se seus IDs forem diferentes, ajuste a coluna 'fornecedor_id' abaixo.

INSERT INTO produtos (fornecedor_id, nome_produto, codigo_produto, descricao, quantidade_estoque, preco_unitario) VALUES 

-- --- FORNECEDOR 1: GOLFE (20 Produtos) ---
(1, 'Taco Driver Titanium Pro', 'GLF-001', 'Driver de titânio para longas distâncias, shaft de grafite.', 15, 2500.00),
(1, 'Conjunto de Ferros Forjados', 'GLF-002', 'Set completo do ferro 3 ao PW, acabamento cromado.', 8, 4800.00),
(1, 'Putter Balanceado Precision', 'GLF-003', 'Putter com face fresada para maior controle no green.', 25, 1200.00),
(1, 'Bolas de Golfe Tour (Dúzia)', 'GLF-004', 'Caixa com 12 bolas de alta performance e compressão média.', 100, 250.00),
(1, 'Saco de Golfe Stand Bag', 'GLF-005', 'Saco leve com tripé automático e 5 divisórias.', 30, 1100.00),
(1, 'Sapato de Golfe Impermeável', 'GLF-006', 'Couro legítimo com spikes substituíveis.', 40, 890.00),
(1, 'Luva de Couro Cabretta', 'GLF-007', 'Luva macia para mão esquerda, aderência superior.', 150, 120.00),
(1, 'Telêmetro a Laser Bushnell', 'GLF-008', 'Mede distâncias com precisão de 1 jarda.', 20, 1500.00),
(1, 'Boné Performance Dry', 'GLF-009', 'Tecido respirável com logo bordado.', 80, 150.00),
(1, 'Camisa Polo Técnica', 'GLF-010', 'Proteção UV50 e secagem rápida.', 60, 280.00),
(1, 'Calça Chino Bege', 'GLF-011', 'Corte clássico para prática de golfe.', 50, 350.00),
(1, 'Tees de Madeira (Pacote)', 'GLF-012', 'Pacote com 100 tees biodegradáveis.', 200, 45.00),
(1, 'Toalha de Golfe Microfibra', 'GLF-013', 'Com gancho para prender no saco.', 90, 80.00),
(1, 'Guarda-Chuva Resistente', 'GLF-014', 'Dupla camada, resistente a ventos fortes.', 35, 220.00),
(1, 'Cinto de Couro Reversível', 'GLF-015', 'Preto e marrom, fivela ajustável.', 45, 180.00),
(1, 'Kit Reparo de Divot', 'GLF-016', 'Ferramenta em aço inox com marcador de bola.', 70, 90.00),
(1, 'Capa para Driver (Headcover)', 'GLF-017', 'Proteção interna acolchoada, design vintage.', 40, 160.00),
(1, 'Rede de Treino Indoor', 'GLF-018', 'Montagem rápida para treino de swing em casa.', 10, 600.00),
(1, 'Tapete de Putt Portátil', 'GLF-019', 'Com retorno de bola automático.', 15, 450.00),
(1, 'Carrinho Manual 3 Rodas', 'GLF-020', 'Dobrável e leve, com freio de pé.', 12, 1200.00),

-- --- FORNECEDOR 2: TÊNIS (20 Produtos) ---
(2, 'Raquete Pro Staff RF97', 'TNS-001', 'Raquete profissional, peso 340g, padrão 16x19.', 20, 1400.00),
(2, 'Raquete Pure Drive', 'TNS-002', 'Potência e spin, ideal para fundo de quadra.', 25, 1350.00),
(2, 'Raqueteira Térmica x9', 'TNS-003', 'Capacidade para 9 raquetes, proteção térmica.', 15, 650.00),
(2, 'Tubo Bolas Championship', 'TNS-004', 'Tubo com 3 bolas para todos os tipos de quadra.', 300, 45.00),
(2, 'Corda Poliéster (Rolo)', 'TNS-005', 'Rolo de 200m, alta durabilidade e controle.', 10, 800.00),
(2, 'Overgrip Emborrachado (Pote)', 'TNS-006', 'Pote com 30 unidades, toque seco.', 40, 200.00),
(2, 'Antivibrador Silicone', 'TNS-007', 'Reduz vibração das cordas, pacote com 2.', 100, 30.00),
(2, 'Tênis Clay Court', 'TNS-008', 'Solado espinha de peixe para saibro.', 35, 720.00),
(2, 'Tênis Hard Court', 'TNS-009', 'Alta durabilidade para quadras rápidas.', 35, 720.00),
(2, 'Munhequeira Atoalhada', 'TNS-010', 'Absorção máxima de suor, par.', 80, 50.00),
(2, 'Testeira Nike', 'TNS-011', 'Evita suor nos olhos, ajuste perfeito.', 60, 55.00),
(2, 'Rede de Tênis Oficial', 'TNS-012', 'Medidas oficiais ITF, fio 2.5mm.', 5, 900.00),
(2, 'Carrinho Coletor Bolas', 'TNS-013', 'Capacidade para 70 bolas, vira tripé.', 8, 450.00),
(2, 'Viseira Feminina', 'TNS-014', 'Proteção UV, fechamento em velcro.', 50, 90.00),
(2, 'Saia Short Plissada', 'TNS-015', 'Com bolso interno para bola.', 45, 220.00),
(2, 'Vestido Performance', 'TNS-016', 'Tecido tecnológico, costas nadador.', 30, 350.00),
(2, 'Bermuda com Bolso Fundo', 'TNS-017', 'Ideal para guardar a segunda bola.', 50, 180.00),
(2, 'Máquina Lançadora de Bolas', 'TNS-018', 'Bateria recarregável, controle remoto.', 2, 5500.00),
(2, 'Raquete Beach Tennis Carbon', 'TNS-019', 'Full Carbon 3K, tratamento áspero.', 20, 980.00),
(2, 'Bolas Beach Tennis (Pack)', 'TNS-020', 'Baixa pressão, pacote com 3.', 150, 75.00),

-- --- FORNECEDOR 3: HIPISMO (20 Produtos) ---
(3, 'Sela de Salto Couro', 'HIP-001', 'Couro legítimo argentino, assento raso.', 5, 4500.00),
(3, 'Sela Adestramento', 'HIP-002', 'Assento profundo, ideal para dressage.', 4, 5200.00),
(3, 'Capacete Aveludado', 'HIP-003', 'Certificação de segurança, ventilação frontal.', 25, 850.00),
(3, 'Bota Montaria Couro', 'HIP-004', 'Cano longo com zíper e elástico.', 30, 1200.00),
(3, 'Culote Bege Competição', 'HIP-005', 'Com reforço nos joelhos (grips).', 40, 450.00),
(3, 'Luvas Aderentes', 'HIP-006', 'Reforço nas rédeas, compatível com touch.', 60, 150.00),
(3, 'Chicote Dressage', 'HIP-007', 'Longo, ponta macia, cabo em gel.', 35, 180.00),
(3, 'Manta Acolchoada', 'HIP-008', 'Tecido respirável, absorve impacto.', 50, 320.00),
(3, 'Cabeçada com Rédeas', 'HIP-009', 'Couro inglês, fivelas em inox.', 20, 550.00),
(3, 'Freio Aço Inox', 'HIP-010', 'Bocal brando, hastes curtas.', 45, 200.00),
(3, 'Estribos de Segurança', 'HIP-011', 'Liberação rápida em caso de queda.', 30, 380.00),
(3, 'Perneira Couro', 'HIP-012', 'Proteção para a panturrilha.', 25, 250.00),
(3, 'Colete de Proteção', 'HIP-013', 'Placas articuladas, nível 3 de segurança.', 15, 900.00),
(3, 'Escova de Crina', 'HIP-014', 'Cerdas duras para limpeza pesada.', 80, 60.00),
(3, 'Rasqueadeira Borracha', 'HIP-015', 'Para massagem e limpeza do pelo.', 90, 40.00),
(3, 'Casaca de Competição', 'HIP-016', 'Tecido elástico, corte acinturado.', 20, 1100.00),
(3, 'Camisa Gola Alta', 'HIP-017', 'Branca para competição oficial.', 35, 280.00),
(3, 'Jogo Caneleiras', 'HIP-018', 'Proteção para patas dianteiras e traseiras.', 25, 420.00),
(3, 'Cabresto de Nylon', 'HIP-019', 'Reforçado com pelego sintético.', 60, 110.00),
(3, 'Caixa de Limpeza', 'HIP-020', 'Completa com escovas e limpador de cascos.', 15, 350.00),

-- --- FORNECEDOR 4: VELA/NÁUTICA (20 Produtos) ---
(4, 'Jaqueta Corta-Vento', 'VEL-001', 'Impermeável e respirável, costura selada.', 30, 890.00),
(4, 'Sapato Boat Shoe', 'VEL-002', 'Solado antiderrapante non-marking.', 40, 550.00),
(4, 'Luvas Dedo Curto', 'VEL-003', 'Reforço em kevlar na palma.', 55, 180.00),
(4, 'Óculos Polarizados', 'VEL-004', 'Flutuantes, lentes azuis espelhadas.', 25, 600.00),
(4, 'Bolsa Estanque 20L', 'VEL-005', 'Totalmente à prova d\'água.', 45, 220.00),
(4, 'Colete Salva-Vidas', 'VEL-006', 'Modelo esportivo, liberdade de movimento.', 35, 350.00),
(4, 'Relógio de Regata', 'VEL-007', 'Com timer de contagem regressiva.', 15, 1200.00),
(4, 'Boné com Prendedor', 'VEL-008', 'Cabo de segurança para não voar.', 60, 120.00),
(4, 'Calça Impermeável', 'VEL-009', 'Jardineira para tempo severo.', 20, 750.00),
(4, 'Bermuda Secagem Rápida', 'VEL-010', 'Tecido técnico ripstop.', 50, 300.00),
(4, 'Canivete Marinheiro', 'VEL-011', 'Com abridor de manilha e espicha.', 40, 280.00),
(4, 'Binóculo Marítimo', 'VEL-012', 'Com bússola interna e retículo.', 10, 1800.00),
(4, 'Lanterna Estanque', 'VEL-013', 'Submersível até 10 metros.', 30, 250.00),
(4, 'Mochila Náutica 30L', 'VEL-014', 'Soldada, sem costuras.', 25, 480.00),
(4, 'Camiseta UV Manga Longa', 'VEL-015', 'Proteção solar máxima.', 70, 160.00),
(4, 'Toalha Compacta', 'VEL-016', 'Secagem ultra rápida.', 80, 90.00),
(4, 'Saco de Dormir', 'VEL-017', 'Compacto para cabines pequenas.', 15, 350.00),
(4, 'Kit Primeiros Socorros', 'VEL-018', 'Caixa estanque, itens básicos.', 20, 200.00),
(4, 'Rádio VHF Portátil', 'VEL-019', 'Flutuante e à prova d\'água.', 8, 950.00),
(4, 'Bússola de Mão', 'VEL-020', 'Precisão profissional.', 12, 320.00),

-- --- FORNECEDOR 5: POLO E GERAL (20 Produtos) ---
(5, 'Taco de Polo Bambu', 'OUT-001', 'Cana selecionada, grip de couro.', 15, 450.00),
(5, 'Bola de Polo Oficial', 'OUT-002', 'Plástico de alto impacto.', 100, 80.00),
(5, 'Capacete de Polo', 'OUT-003', 'Modelo argentino, aba larga.', 10, 1100.00),
(5, 'Botas Polo com Zíper', 'OUT-004', 'Couro marrom, proteção frontal.', 12, 1400.00),
(5, 'Joelheira de Couro', 'OUT-005', 'Proteção grossa para montaria.', 20, 650.00),
(5, 'Camisa Time Polo', 'OUT-006', 'Personalizada, numerada.', 30, 300.00),
(5, 'Calça Branca Jeans', 'OUT-007', 'Resistente, para montaria.', 40, 400.00),
(5, 'Luva Polo Direita', 'OUT-008', 'Aderência para o taco.', 50, 140.00),
(5, 'Mala de Viagem Couro', 'OUT-009', 'Estilo vintage esportivo.', 8, 1800.00),
(5, 'Relógio Cronógrafo', 'OUT-010', 'Caixa de aço, pulseira de borracha.', 15, 2500.00),
(5, 'Óculos Aviação', 'OUT-011', 'Lentes verdes clássicas.', 30, 700.00),
(5, 'Cinto Lona Listrado', 'OUT-012', 'Cores do clube, fivela D.', 45, 150.00),
(5, 'Kit Frescobol Madeira', 'OUT-013', 'Raquetes envernizadas, bola pro.', 25, 350.00),
(5, 'Jogo de Bocha', 'OUT-014', 'Maleta com 8 bolas e bolim.', 10, 600.00),
(5, 'Mesa de Bilhar Snooker', 'OUT-015', 'Madeira maciça, tecido inglês.', 2, 4500.00),
(5, 'Taco Bilhar Desmontável', 'OUT-016', 'Rosca de metal, ponteira sola.', 35, 380.00),
(5, 'Dardo Profissional', 'OUT-017', 'Corpo de tungstênio 90%.', 40, 250.00),
(5, 'Alvo de Dardos Sisal', 'OUT-018', 'Oficial, sem grampos.', 15, 400.00),
(5, 'Arco Recurvo Olímpico', 'OUT-019', 'Empunhadura de alumínio.', 5, 3200.00),
(5, 'Flechas Carbono (Set)', 'OUT-020', 'Conjunto com 6 flechas.', 20, 450.00);
-- FIM DO ARQUIVO DE CRIAÇÃO DO BANCO DE DADOS ALBOZ