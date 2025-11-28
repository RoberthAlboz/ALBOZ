<?php
// ATIVA EXIBIÇÃO DE ERROS
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexao.php');
include('protecao.php');

$mensagem = "";
$class = "";
$usuario_id = $_SESSION['id'];

// --- LÓGICA DE EXCLUSÃO ---
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $sql = "DELETE p FROM produtos p 
            JOIN fornecedores f ON p.fornecedor_id = f.id 
            WHERE p.id=? AND f.usuario_id=?";
            
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ii", $delete_id, $usuario_id);
        if ($stmt->execute()) {
            $mensagem = "Produto excluído com sucesso!";
            $class = "success";
        } else {
            $mensagem = "Erro ao excluir: " . $stmt->error;
            $class = "error";
        }
    } else {
        $mensagem = "Erro no SQL de Exclusão: " . $mysqli->error;
        $class = "error";
    }
}

// --- LÓGICA DE PESQUISA ---
$busca = "";
$filtro_sql = "";

if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $busca = $mysqli->real_escape_string($_GET['busca']);
    // Pesquisa no nome OU na descrição
    $filtro_sql = " AND (p.nome_produto LIKE '%$busca%' OR p.descricao LIKE '%$busca%')";
}

// --- LISTAGEM (COM FILTRO) ---
$sql_listagem = "SELECT p.*, f.nome_fornecedor 
                 FROM produtos p 
                 JOIN fornecedores f ON p.fornecedor_id = f.id 
                 WHERE f.usuario_id = '$usuario_id'
                 $filtro_sql 
                 ORDER BY p.id DESC";

$produtos = $mysqli->query($sql_listagem);

if (!$produtos) {
    die("Erro no SQL: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Produtos - ALBOZ</title>
    <!-- Removi o link externo style.css para garantir que o CSS interno tenha prioridade -->
    <style>
        /* ==================================================================
           1. IMPORTAÇÃO DE FONTES E VARIÁVEIS (DO SEU CSS)
           ================================================================== */
        @import url('https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@800&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Monda:wght@400;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=League+Script&display=swap');

        :root {
            --bg: #001826;        
            --card: #003554;      
            --card-hover: #014e7b;
            --muted: #bfc9ce;
            --accent: #dfe7e9;
            --gold: #ffffff;      
        }

        /* ==================================================================
           2. RESET E CONFIGURAÇÕES GERAIS
           ================================================================== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: var(--card) var(--bg);
            overflow-x: hidden;
        }

        body {
            margin: 0;
            font-family: 'Monda', sans-serif;
            background: var(--bg);
            color: var(--accent);
            line-height: 1.6;
            cursor: default;
        }

        a { text-decoration: none; color: inherit; transition: 0.3s; cursor: pointer; }
        ul { list-style: none; }
        button, .btn-submit, input[type="submit"] { cursor: pointer; }

        /* --- SCROLLBAR --- */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb {
            background-color: var(--card);
            border-radius: 5px;
            border: 2px solid var(--bg);
        }
        ::-webkit-scrollbar-thumb:hover { background-color: var(--card-hover); }

        .limitador {
            max-width: 1100px;
            width: 90%;
            margin: 0 auto;
        }

        ::selection {
            background-color: var(--gold);
            color: var(--bg);
            text-shadow: none;
        }

        /* ==================================================================
           3. MENU DE NAVEGAÇÃO
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
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            transition: height 0.4s cubic-bezier(0.25, 1, 0.5, 1);
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
        .hamburger-menu.open .hamburger-line:first-child { transform: translateY(7px) rotate(45deg); }
        .hamburger-menu.open .hamburger-line:last-child { transform: translateY(0px) rotate(-45deg); }

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
        .nav-card-label { font-weight: bold; font-size: 0.9rem; margin-bottom: 5px; color: var(--card); }
        .nav-card-links a {
            display: inline-block;
            margin-right: 10px;
            font-size: 0.9rem;
            color: #555;
            font-weight: 500;
        }
        .nav-card-links a:hover { text-decoration: underline; color: var(--card-hover); }

        /* ==================================================================
           4. HEADER
           ================================================================== */
        .header {
            position: relative;
            width: 100%;
            /* Altura ajustada para páginas internas (menor que a home) */
            height: auto; 
            min-height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding-top: 0;
            padding-bottom: 2rem;
        }

        .subtitulo {
            font-family: 'Great Vibes', cursive;
            font-size: 4rem; /* Reduzido levemente para páginas internas */
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
            /* Se o fundo for escuro, precisamos forçar uma cor visível se o gradiente falhar, 
               mas aqui vamos confiar na cor definida pelo tema ou usar branco */
            -webkit-text-fill-color: var(--gold); /* Ajuste para aparecer no fundo escuro */
            transition: background-position 0.5s;
        }
        .subtitulo:hover { animation: shine 2s linear infinite; }

        @keyframes shine {
          0% { background-position: 100%; }
          100% { background-position: -100%; }
        }

        /* ==================================================================
           5. RODAPÉ
           ================================================================== */
        .rodape {
            background: #000c13;
            padding: 3rem 0;
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        .rodape-content {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 4rem;
            text-align: center;
        }
        .rodape ul { min-width: 200px; }
        .rodape ul strong {
            display: block;
            color: #fff;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }
        .rodape li {
            color: var(--muted);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.2s;
        }
        .rodape li:hover { color: var(--gold); transform: scale(1.05); }

        /* ==================================================================
           6. ESTILOS ESPECÍFICOS DA PÁGINA (TABELA E CONTAINER)
           ================================================================== */
        .container { 
            max-width: 1100px; 
            margin: 0 auto 60px auto; 
            padding: 30px; 
            background: #fff; /* Fundo branco para leitura clara da tabela */
            color: #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
            border-radius: 8px; 
            min-height: 400px; 
        }
        
        .container h2 {
            margin: 0;
            color: var(--card); /* Azul do tema */
            font-family: 'Abhaya Libre', serif;
            font-size: 2rem;
        }

        /* Barra de Pesquisa */
        .search-box { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 20px; 
            align-items: center; 
            background: #f4f6f8; 
            padding: 15px; 
            border-radius: 8px; 
            border: 1px solid #e0e0e0;
        }
        .search-box input { 
            flex: 1; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            background: #fff;
            color: #333;
        }
        .btn-search { 
            background-color: var(--card); 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 4px; 
            font-weight: bold; 
            transition: 0.3s;
        }
        .btn-search:hover { background-color: var(--card-hover); }
        
        .btn-limpar { 
            background-color: var(--muted); 
            color: #333; 
            text-decoration: none; 
            padding: 10px 15px; 
            border-radius: 4px; 
            font-size: 14px; 
        }
        .btn-limpar:hover { background-color: #aebdc4; }

        /* Tabela */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid #ddd; 
            vertical-align: middle; 
            color: #444;
        }
        th { 
            background-color: var(--card); 
            color: white; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        tr:hover { background-color: #f1f5f8; }

        .img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
        .no-img { font-size: 11px; color: #999; font-style: italic; }

        .btn-novo { 
            background-color: #28a745; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 4px; 
            display: inline-block; 
            font-weight: bold; 
            transition: 0.3s; 
        }
        .btn-novo:hover { background-color: #218838; }

        .acoes a { text-decoration: none; font-weight: bold; margin-right: 10px; font-size: 14px; transition: 0.2s; }
        .btn-edit { color: #d69e2e; } /* Amarelo escuro */
        .btn-edit:hover { color: #b77f1d; }
        .btn-delete { color: #dc3545; } /* Vermelho */
        .btn-delete:hover { color: #a71d2a; }

        .message { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Responsividade */
        @media (max-width: 768px) {
            .rodape-content { flex-direction: column; gap: 2rem; }
            .header { height: auto; min-height: 200px; }
            .subtitulo { font-size: 3rem; margin-top: 5rem; }
            .container { padding: 15px; margin: 0 auto 30px auto; width: 95%; }
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { border: 1px solid #ccc; margin-bottom: 10px; background: #fff; }
            td { border: none; border-bottom: 1px solid #eee; position: relative; padding-left: 50%; }
            td:before { position: absolute; top: 12px; left: 12px; width: 45%; padding-right: 10px; white-space: nowrap; font-weight: bold; color: var(--card); content: attr(data-label); }
        }
    </style>
</head>
<body class="body">

    <!-- HEADER -->
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
                        <div class="nav-card-links">
                            <a class="nav-card-link" href="painel.php">Meu Painel</a>
                            <a class="nav-card-link" href="logout.php" style="color:red">Sair</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="subtitulo">Gerenciamento de Produtos</div>
    </div>

    <!-- ÁREA DE CONTEÚDO -->
    <div class="container">
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
            <h2>Meus Produtos</h2>
            <a href="cadastrar_produto.php" class="btn-novo">+ Novo Produto</a>
        </div>

        <?php if ($mensagem) echo "<div class='message $class'>$mensagem</div>"; ?>

        <!-- BARRA DE PESQUISA -->
        <form method="GET" class="search-box">
            <input type="text" name="busca" placeholder="Pesquisar produto por nome ou descrição..." value="<?php echo htmlspecialchars($busca); ?>">
            <button type="submit" class="btn-search">🔍 Pesquisar</button>
            <?php if (!empty($busca)): ?>
                <a href="listar_produtos.php" class="btn-limpar">Limpar Filtro</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($busca)): ?>
            <p style="margin-bottom:15px; color:#555;">Exibindo resultados para: <strong><?php echo htmlspecialchars($busca); ?></strong></p>
        <?php endif; ?>

        <!-- TABELA DE PRODUTOS -->
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Imagem</th>
                    <th>Produto</th>
                    <th>Distribuidor</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th style="width: 150px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($produtos->num_rows > 0): ?>
                    <?php while ($row = $produtos->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Imagem">
                            <?php 
                            if (!empty($row['imagem']) && file_exists($row['imagem'])) {
                                echo "<img src='" . $row['imagem'] . "' class='img-thumb'>";
                            } else {
                                echo "<span class='no-img'>Sem foto</span>";
                            }
                            ?>
                        </td>
                        <td data-label="Produto">
                            <strong><?php echo $row['nome_produto']; ?></strong>
                            <?php if(!empty($row['descricao'])): ?>
                                <br><small style="color:#666;"><?php echo substr($row['descricao'], 0, 40) . '...'; ?></small>
                            <?php endif; ?>
                        </td>
                        <td data-label="Distribuidor"><?php echo $row['nome_fornecedor']; ?></td>
                        <td data-label="Preço">R$ <?php echo number_format($row['preco_unitario'], 2, ',', '.'); ?></td>
                        <td data-label="Estoque">
                            <?php 
                                echo $row['quantidade_estoque']; 
                                echo ($row['quantidade_estoque'] <= 5) ? " <span style='color:red; font-size:10px;'>(Baixo)</span>" : "";
                            ?>
                        </td>
                        <td class="acoes" data-label="Ações">
                            <a href="cadastrar_produto.php?edit_id=<?php echo $row['id']; ?>" class="btn-edit">Editar</a>
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Tem certeza?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:#666;">
                            <?php if(!empty($busca)): ?>
                                Nenhum produto encontrado para sua busca.
                            <?php else: ?>
                                Nenhum produto cadastrado. <a href="cadastrar_produto.php" style="color:var(--card); font-weight:bold;">Cadastrar Agora</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top:20px;">
            <a href="painel.php" style="text-decoration:none; color:var(--card); font-weight:bold;">&larr; Voltar ao Painel Principal</a>
        </div>
    </div>
    
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