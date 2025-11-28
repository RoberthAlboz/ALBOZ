<?php
// ATIVA EXIBIÇÃO DE ERROS
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexao.php');
include('protecao.php');

$mensagem = "";
$class = "";
$usuario_id = $_SESSION['id'];
$produto_edit = null;

// --- 1. SE FOR EDIÇÃO, BUSCA OS DADOS ANTIGOS ---
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    
    $sql_busca = "SELECT p.* FROM produtos p 
                  JOIN fornecedores f ON p.fornecedor_id = f.id 
                  WHERE p.id = ? AND f.usuario_id = ?";
                  
    $stmt = $mysqli->prepare($sql_busca);
    $stmt->bind_param("ii", $edit_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $produto_edit = $result->fetch_assoc();
    
    if(!$produto_edit) {
        die("Produto não encontrado ou você não tem permissão para editá-lo.");
    }
}

// --- 2. PROCESSAR O FORMULÁRIO (SALVAR) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $fornecedor_id = $_POST['fornecedor_id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $estoque = (int)$_POST['estoque']; // Força ser número inteiro
    
    // --- CORREÇÃO DO PREÇO (CRÍTICO) ---
    $preco_entrada = $_POST['preco'];
    $preco_limpo = preg_replace('/[^0-9,]/', '', $preco_entrada);
    $preco = str_replace(',', '.', $preco_limpo);
    if(!is_numeric($preco)) $preco = 0.00;

    // --- UPLOAD DE IMAGEM ---
    $imagem = $produto_edit['imagem'] ?? ''; 
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $pasta = "img_produtos/";
        if (!is_dir($pasta)) mkdir($pasta, 0777, true); 
        
        $nome_arquivo = uniqid() . "_" . basename($_FILES['imagem']['name']);
        $caminho_completo = $pasta . $nome_arquivo;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_completo)) {
            $imagem = $caminho_completo;
        } else {
            $mensagem = "Erro ao fazer upload da imagem.";
            $class = "error";
        }
    }

    if ($id) {
        // ATUALIZAR
        $sql = "UPDATE produtos SET fornecedor_id=?, nome_produto=?, descricao=?, preco_unitario=?, quantidade_estoque=?, imagem=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        // Tipos: i=int, s=string, d=double
        $stmt->bind_param("issdisi", $fornecedor_id, $nome, $descricao, $preco, $estoque, $imagem, $id);
    } else {
        // INSERIR NOVO
        $sql = "INSERT INTO produtos (fornecedor_id, nome_produto, descricao, preco_unitario, quantidade_estoque, imagem) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("issdis", $fornecedor_id, $nome, $descricao, $preco, $estoque, $imagem);
    }

    if (isset($stmt) && $stmt->execute()) {
        header("Location: listar_produtos.php");
        exit;
    } else {
        $mensagem = "Erro no Banco: " . $mysqli->error;
        $class = "error";
    }
}

// --- 3. BUSCAR FORNECEDORES ---
$sql_forn = "SELECT id, nome_fornecedor FROM fornecedores WHERE usuario_id = '$usuario_id'";
$fornecedores = $mysqli->query($sql_forn);

if (!$fornecedores) {
    die("Erro ao buscar fornecedores: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produto - ALBOZ</title>
    
    <!-- CSS INTERNO COMPLETO -->
    <style>
        /* ==================================================================
           1. IMPORTAÇÃO E VARIÁVEIS
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
           2. GERAL
           ================================================================== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: var(--card) var(--bg);
        }

        body {
            margin: 0;
            font-family: 'Monda', sans-serif;
            background: var(--bg);
            color: var(--accent);
            line-height: 1.6;
        }

        a { text-decoration: none; color: inherit; transition: 0.3s; cursor: pointer; }
        ul { list-style: none; }
        
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background-color: var(--card); border-radius: 5px; border: 2px solid var(--bg); }

        .limitador { max-width: 1100px; width: 90%; margin: 0 auto; }

        /* ==================================================================
           3. NAVBAR
           ================================================================== */
        .card-nav-container {
            position: absolute; top: 2em; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 600px; z-index: 1001;
        }
        .card-nav {
            position: relative; background-color: #ffffff; border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden;
            transition: height 0.4s ease; color: var(--card);
        }
        .card-nav-top {
            height: 60px; display: flex; align-items: center; justify-content: center;
            padding: 0 1.5rem; position: relative;
        }
        .logo {
            font-family: 'Abhaya Libre', serif; font-weight: 800; font-size: 1.5rem;
            letter-spacing: 1px; text-transform: uppercase;
        }
        .hamburger-menu {
            position: absolute; left: 20px; cursor: pointer; display: flex; flex-direction: column; gap: 5px;
        }
        .hamburger-line { width: 25px; height: 2px; background-color: var(--card); transition: 0.3s; }
        .hamburger-menu.open .hamburger-line:first-child { transform: translateY(7px) rotate(45deg); }
        .hamburger-menu.open .hamburger-line:last-child { transform: translateY(0px) rotate(-45deg); }

        .card-nav-content { padding: 1rem; display: flex; flex-direction: column; gap: 10px; margin-top: 10px; }
        .nav-card { background: #f4f6f8; border: 1px solid #e0e0e0; padding: 10px; border-radius: 8px; }
        .nav-card-label { font-weight: bold; font-size: 0.9rem; margin-bottom: 5px; color: var(--card); }
        .nav-card-links a { display: inline-block; margin-right: 10px; font-size: 0.9rem; color: #555; font-weight: 500; }
        .nav-card-links a:hover { text-decoration: underline; color: var(--card-hover); }

        /* ==================================================================
           4. HEADER
           ================================================================== */
        .header {
            position: relative; width: 100%; height: auto; min-height: 250px;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding-bottom: 2rem;
        }
        .subtitulo {
            font-family: 'Great Vibes', cursive; font-size: 4rem; margin-top: 6rem; z-index: 10;
            text-align: center; font-weight: 400; cursor: default;
            background: linear-gradient(120deg, #001826 40%, rgba(255, 255, 255, 0.8) 50%, #001826 60%);
            background-size: 200% 100%; background-position: 100%; color: #001826;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            text-shadow: 0 0 10px rgba(255,255,255,0.1);
            transition: background-position 0.5s;
        }
        .subtitulo:hover { animation: shine 2s linear infinite; }
        @keyframes shine { 0% { background-position: 100%; } 100% { background-position: -100%; } }

        /* ==================================================================
           5. CONTAINER E FORMULÁRIO (CRUD)
           ================================================================== */
        .container {
            max-width: 1000px; margin: 0 auto 60px auto; padding: 30px;
            background: #fff; color: #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); border-radius: 8px;
        }
        
        .form-crud {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
            background: #f9f9f9; padding: 25px; border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .full-width { grid-column: 1 / -1; }
        
        label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--card); font-size: 0.9rem; }
        input, select, textarea {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;
            background: #fff; color: #333; font-family: inherit;
        }
        input:focus, textarea:focus { border-color: var(--card); outline: none; }

        button {
            background-color: #28a745; color: white; padding: 12px 25px; border: none;
            border-radius: 4px; font-weight: bold; font-size: 1rem; cursor: pointer;
            transition: 0.3s;
        }
        button:hover { background-color: #218838; transform: translateY(-2px); }

        .btn-cancelar {
            display: inline-block; padding: 12px 25px; background: #6c757d; color: white;
            border-radius: 4px; font-weight: bold; margin-left: 10px;
        }
        .btn-cancelar:hover { background: #5a6268; }

        .img-preview { max-width: 150px; margin-top: 10px; border: 1px solid #ddd; padding: 5px; border-radius: 4px; }

        .message { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* ==================================================================
           6. RODAPÉ
           ================================================================== */
        .rodape { background: #000c13; padding: 3rem 0; margin-top: auto; border-top: 1px solid rgba(255,255,255,0.05); }
        .rodape-content { display: flex; justify-content: center; gap: 4rem; text-align: center; flex-wrap: wrap; }
        .rodape ul strong { display: block; color: #fff; margin-bottom: 1rem; font-size: 1.1rem; }
        .rodape li { color: var(--muted); margin-bottom: 0.5rem; font-size: 0.9rem; transition: 0.2s; }
        .rodape li:hover { color: var(--gold); }

        @media (max-width: 768px) {
            .form-crud { grid-template-columns: 1fr; }
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
                            <a class="nav-card-link" href="painel.php">Voltar ao Painel</a>
                            <a class="nav-card-link" href="logout.php" style="color:red">Sair</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="subtitulo"><?php echo $produto_edit ? 'Editar Produto' : 'Novo Produto'; ?></div>
    </div>

    <!-- CONTEÚDO -->
    <div class="container">
        
        <?php if ($mensagem) echo "<div class='message $class'>$mensagem</div>"; ?>

        <form method="post" enctype="multipart/form-data" class="form-crud">
            <input type="hidden" name="id" value="<?php echo $produto_edit['id'] ?? ''; ?>">

            <div class="full-width">
                <label>Fornecedor / Distribuidor:</label>
                <select name="fornecedor_id" required>
                    <option value="">Selecione...</option>
                    <?php 
                    if ($fornecedores->num_rows > 0) {
                        while($f = $fornecedores->fetch_assoc()): ?>
                            <option value="<?php echo $f['id']; ?>" 
                                <?php if($produto_edit && $produto_edit['fornecedor_id'] == $f['id']) echo 'selected'; ?>>
                                <?php echo $f['nome_fornecedor']; ?>
                            </option>
                        <?php endwhile; 
                    } else {
                        echo "<option value='' disabled>Nenhum fornecedor cadastrado. Cadastre um primeiro!</option>";
                    }
                    ?>
                </select>
                <?php if ($fornecedores->num_rows == 0): ?>
                    <small><a href="cadastrar_fornecedor.php" style="color:blue;">Clique aqui para cadastrar um fornecedor</a></small>
                <?php endif; ?>
            </div>

            <div>
                <label>Nome do Produto:</label>
                <input type="text" name="nome" value="<?php echo $produto_edit['nome_produto'] ?? ''; ?>" required placeholder="Ex: Raquete de Tênis">
            </div>

            <div>
                <label>Preço Unitário (R$):</label>
                <!-- O input aceita texto para permitir vírgulas do usuário -->
                <input type="text" name="preco" value="<?php echo $produto_edit ? number_format($produto_edit['preco_unitario'], 2, ',', '') : ''; ?>" required placeholder="0,00">
            </div>

            <div>
                <label>Estoque (Quantidade):</label>
                <input type="number" name="estoque" value="<?php echo $produto_edit['quantidade_estoque'] ?? ''; ?>" placeholder="0">
            </div>

            <div>
                <label>Imagem do Produto:</label>
                <input type="file" name="imagem" accept="image/*">
            </div>

            <?php if (isset($produto_edit['imagem']) && $produto_edit['imagem']): ?>
                <div class="full-width">
                    <label>Imagem Atual:</label>
                    <img src="<?php echo $produto_edit['imagem']; ?>" class="img-preview">
                </div>
            <?php endif; ?>

            <div class="full-width">
                <label>Descrição Detalhada:</label>
                <textarea name="descricao" rows="5"><?php echo $produto_edit['descricao'] ?? ''; ?></textarea>
            </div>

            <div class="full-width">
                <button type="submit"><?php echo $produto_edit ? 'Salvar Alterações' : 'Cadastrar Produto'; ?></button>
                <a href="listar_produtos.php" class="btn-cancelar">Cancelar</a>
            </div>
        </form>
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