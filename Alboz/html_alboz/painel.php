<?php
include('protecao.php'); // Garante que o usuário está logado
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Principal - ALBOZ</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* CSS EXCLUSIVO PARA O PAINEL DE CARDS */
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            min-height: 60vh; /* Garante altura mínima */
        }

        .welcome-section {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 40px;
            border-left: 5px solid #007bff;
        }

        .welcome-section h2 { margin-top: 0; color: #333; }
        .welcome-section p { color: #666; margin-bottom: 0; }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .card h3 {
            color: #007bff;
            margin-top: 0;
            font-size: 1.4rem;
        }

        .card p {
            color: #555;
            font-size: 0.95rem;
            line-height: 1.5;
            flex-grow: 1; /* Empurra o botão para baixo */
            margin-bottom: 20px;
        }

        .btn-card {
            display: inline-block;
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn-card:hover { background-color: #0056b3; }

        /* Cores diferentes para variar */
        .card.destaque h3 { color: #28a745; }
        .card.destaque .btn-card { background-color: #28a745; }
        .card.destaque .btn-card:hover { background-color: #218838; }

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
                    <div class="logo-container"><div class="logo">ALBOZ</div></div>
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
                        <a href="logout.php" class="botao-login" style="color:white; font-size:12px;">SAIR</a>
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
            <p>Gerencie seus Distribuidores e Produtos com eficiência e praticidade.</p>
        </div>

        <div class="cards-grid">
            
            <!-- Card 1: Cadastrar Produto -->
            <div class="card destaque">
                <h3>Cadastrar Produtos</h3>
                <p>Adicione novos itens ao seu catálogo, defina preços e controle o estoque.</p>
                <a href="cadastrar_produto.php" class="btn-card">Novo Produto</a>
            </div>

            <!-- Card 2: Cadastrar Fornecedor -->
            <div class="card">
                <h3>Cadastrar Distribuidores</h3>
                <p>Registre novos parceiros e fornecedores para manter sua rede atualizada.</p>
                <a href="cadastrar_fornecedor.php" class="btn-card">Novo Distribuidor</a>
            </div>

            <!-- Card 3: Listar Produtos -->
            <div class="card">
                <h3>Meus Produtos</h3>
                <p>Visualize, edite ou exclua itens do seu catálogo completo.</p>
                <a href="listar_produtos.php" class="btn-card">Ver Catálogo</a>
            </div>

             <!-- Card 4: Listar Fornecedores -->
             <div class="card">
                <h3>Meus Distribuidores</h3>
                <p>Gerencie a lista de empresas parceiras cadastradas no sistema.</p>
                <a href="listar_fornecedores.php" class="btn-card">Ver Parceiros</a>
            </div>

        </div>
    </main>

    <!-- RODAPÉ -->
    <div class="rodape">
        <div class="limitador rodape-content">
            <ul class="sobre_nos">
                <strong>Sobre nós</strong>
                <a href="index.html">ALBOZ</a><br>
                <a href="suporte.php">Suporte</a><br>
            </ul>
            <div class="logo">Alboz</div>
            <p>© 2025 Alboz.com</p>
        </div>
    </div>

    <script src="java.js" defer></script>
    <script>
        const hamburger = document.getElementById('hamburgerBtn');
        const nav = document.getElementById('cardNav');
        if(hamburger && nav){
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