<?php
include('protecao.php'); // Garante que o usuário está logado
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Principal - ALBOZ</title>

    <!-- CSS INTERNO COMPLETO -->
    <style>
        /* ==================================================================
           1. IMPORTAÇÃO E VARIÁVEIS
           ================================================================== */
        @import url('https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@800&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Monda:wght@400;700&display=swap');

        :root {
            --bg: #001826;
            --card: #003554;
            --card-hover: #014e7b;
            --muted: #bfc9ce;
            --accent: #dfe7e9;
            --gold: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Monda', sans-serif;
            background: var(--bg);
            color: var(--accent);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: 0.3s;
            cursor: pointer;
        }

        ul {
            list-style: none;
        }

        .limitador {
            max-width: 1100px;
            width: 90%;
            margin: 0 auto;
        }

        /* ==================================================================
           2. NAVBAR
           ================================================================== */
        .card-nav-container {
            position: absolute;
            top: 2em;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 600px;
            z-index: 1001;
        }

        .card-nav {
            position: relative;
            background-color: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: height 0.4s ease;
            color: var(--card);
        }

        .card-nav-top {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 1.5rem;
            position: relative;
        }

        .logo {
            font-family: 'Abhaya Libre', serif;
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .hamburger-menu {
            position: absolute;
            left: 20px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .hamburger-line {
            width: 25px;
            height: 2px;
            background-color: var(--card);
            transition: 0.3s;
        }

        .hamburger-menu.open .hamburger-line:first-child {
            transform: translateY(7px) rotate(45deg);
        }

        .hamburger-menu.open .hamburger-line:last-child {
            transform: translateY(0px) rotate(-45deg);
        }

        .card-nav-content {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }

        .nav-card {
            background: #f4f6f8;
            border: 1px solid #e0e0e0;
            padding: 10px;
            border-radius: 8px;
        }

        .nav-card-label {
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: var(--card);
        }

        .nav-card-links a {
            display: inline-block;
            margin-right: 10px;
            font-size: 0.9rem;
            color: #555;
            font-weight: 500;
        }

        .nav-card-links a:hover {
            text-decoration: underline;
            color: var(--card-hover);
        }

        /* ==================================================================
           3. HEADER
           ================================================================== */
        .header {
            position: relative;
            width: 100%;
            height: auto;
            min-height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding-bottom: 2rem;
        }

        .subtitulo {
            font-family: 'Great Vibes', cursive;
            font-size: 4rem;
            margin-top: 6rem;
            z-index: 10;
            text-align: center;
            font-weight: 400;
            cursor: default;
            background: linear-gradient(120deg, #001826 40%, rgba(255, 255, 255, 0.8) 50%, #001826 60%);
            background-size: 200% 100%;
            background-position: 100%;
            color: #001826;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            transition: background-position 0.5s;
        }

        .subtitulo:hover {
            animation: shine 2s linear infinite;
        }

        @keyframes shine {
            0% {
                background-position: 100%;
            }

            100% {
                background-position: -100%;
            }
        }

        /* ==================================================================
           4. DASHBOARD E CARDS
           ================================================================== */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto 60px auto;
            padding: 20px;
            width: 90%;
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--card) 0%, var(--bg) 100%);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin-bottom: 40px;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .welcome-section h2 {
            margin-top: 0;
            font-family: 'Abhaya Libre', serif;
            font-size: 2rem;
        }

        .welcome-section p {
            color: var(--muted);
            margin-bottom: 0;
            font-size: 1.1rem;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .card {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            border-top: 4px solid var(--card);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .card h3 {
            color: var(--card);
            margin-top: 0;
            font-size: 1.4rem;
            font-family: 'Abhaya Libre', serif;
        }

        .card p {
            color: #555;
            font-size: 0.95rem;
            line-height: 1.5;
            flex-grow: 1;
            margin-bottom: 25px;
        }

        .btn-card {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--card);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .btn-card:hover {
            background-color: var(--card-hover);
        }

        /* Estilo especial para cards de destaque */
        .card.destaque {
            border-top-color: #28a745;
        }

        .card.destaque h3 {
            color: #218838;
        }

        .card.destaque .btn-card {
            background-color: #28a745;
        }

        .card.destaque .btn-card:hover {
            background-color: #218838;
        }

        /* ==================================================================
           5. RODAPÉ
           ================================================================== */
        .rodape {
            background: #000c13;
            padding: 3rem 0;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .rodape-content {
            display: flex;
            justify-content: center;
            gap: 4rem;
            text-align: center;
            flex-wrap: wrap;
        }

        .rodape ul strong {
            display: block;
            color: #fff;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .rodape li {
            color: var(--muted);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            transition: 0.2s;
        }

        .rodape li:hover {
            color: var(--gold);
        }
    </style>
</head>

<body class="body">

    <!-- NAVBAR ALBOZ -->
    <div class="header">
        <div class="card-nav-container">
            <div class="card-nav" id="cardNav">
                <div class="card-nav-top">
                    <div class="hamburger-menu" id="hamburgerBtn">
                        <div class="hamburger-line"></div>
                        <div class="hamburger-line"></div>
                    </div>
                    <div class="logo-container">
                        <div class="logo">ALBOZ</div>
                    </div>
                </div>
                <div class="card-nav-content">
                    <div class="nav-card">
                        <div class="nav-card-label">Início</div>
                        <div class="nav-card-links">
                            <a class="nav-card-link" href="index.html">Página Inicial</a>
                            <a class="nav-card-link" href="painel.php">Meu Painel</a>
                        </div>
                    </div>
                    <div class="nav-card">
                        <div class="nav-card-label">Serviços</div>
                        <div class="nav-card-links">
                            <a class="nav-card-link" href="cadastrar_produtos.php">Cadastro de Produtos</a>
                            <a class="nav-card-link" href="cadastrar_fornecedor.php">Cadastro de Fornecedores</a>
                            <a class="nav-card-link" href="listar_produtos.php">Buscar meus produtos</a>
                            <a class="nav-card-link" href="listar_fornecedores.php">Buscar meus fornecedores</a>
                        </div>
                    </div>
                    <div class="nav-card">
                        <a href="logout.php" class="botao-login" style="color:red; font-size:12px; font-weight:bold;">SAIR DO SISTEMA</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="subtitulo">Painel de Controle</div>
    </div>

    <!-- CONTEÚDO DO DASHBOARD -->
    <main class="dashboard-container">

        <div class="welcome-section">
            <h2>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h2>
            <p>Este é o seu painel administrativo. Gerencie seus distribuidores e produtos com eficiência e praticidade.</p>
        </div>

        <div class="cards-grid">

            <!-- Card 1: Cadastrar Produto (Destaque) -->
            <div class="card destaque">
                <h3>Cadastrar Produtos</h3>
                <p>Adicione novos itens ao seu catálogo, defina preços, estoque e imagens.</p>
                <a href="cadastrar_produtos.php" class="btn-card">Novo Produto</a>
            </div>

            <!-- Card 2: Cadastrar Fornecedor -->
            <div class="card">
                <h3>Cadastrar Distribuidores</h3>
                <p>Registre novos parceiros comerciais e fornecedores para manter sua rede atualizada.</p>
                <a href="cadastrar_fornecedor.php" class="btn-card">Novo Distribuidor</a>
            </div>

            <!-- Card 3: Listar Produtos -->
            <div class="card">
                <h3>Meus Produtos</h3>
                <p>Visualize, pesquise, edite ou exclua itens do seu catálogo completo.</p>
                <a href="listar_produtos.php" class="btn-card">Ver Catálogo</a>
            </div>

            <!-- Card 4: Listar Fornecedores -->
            <div class="card">
                <h3>Meus Distribuidores</h3>
                <p>Gerencie a lista de empresas parceiras e acesse seus contatos.</p>
                <a href="listar_fornecedores.php" class="btn-card">Ver Parceiros</a>
            </div>

            <!-- Card 5: Suporte -->
            <div class="card">
                <h3>Suporte Técnico</h3>
                <p>Precisa de ajuda? Abra um chamado para nossa equipe de suporte.</p>
                <a href="suporte.php" class="btn-card" style="background-color: #6c757d;">Abrir Chamado</a>
            </div>

        </div>
    </main>

    <!-- RODAPÉ -->
    <div class="rodape">
        <div class="limitador rodape-content">
            <ul class="sobre_nos">
                <strong>Sobre nós</strong>
                <a href="index.html">ALBOZ</a><br>
                <a href="informacoes_corporativas.html">Informações Corporativas</a><br>
                <a href="acessibilidade.html">Acessibilidade</a><br>
                <a href="suporte.php">Suporte</a><br>
            </ul>
            <ul class="paginas">
                <strong>Páginas</strong>
                <a href="login.php">Login/Cadastro de Usuários</a><br>
                <a href="cadastrar_produtos.php">Cadastro de produtos</a><br>
                <a href="cadastrar_fornecedor.php">Cadastro de ditribuidores</a><br>
                <a href="listar_produtos.php">Meus Produtos</a><br>
                <a href="listar_fornecedores.php">Meus fornecedores</a><br>
            </ul>
            <ul class="contato">
                <strong>Contato</strong>
                <li>Telefone: (11) 9999-9999</li>
                <li>E-mail: contato@alboz.com</li>
                <li>Endereço: Rua Exemplo, 123 - São Paulo, SP</li>
            </ul>
        </div>
    </div>

    <script src="java.js" defer></script>
    <script>
        const hamburger = document.getElementById('hamburgerBtn');
        const nav = document.getElementById('cardNav');
        if (hamburger && nav) {
            hamburger.addEventListener('click', () => {
                nav.classList.toggle('open');
                hamburger.classList.toggle('open');
                const content = nav.querySelector('.card-nav-content');
                nav.style.height = nav.classList.contains('open') ? (content.scrollHeight + 60) + 'px' : '60px';
            });
            nav.style.height = '60px';
        }
    </script>
</body>

</html>