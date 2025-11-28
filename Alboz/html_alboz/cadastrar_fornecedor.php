<?php
// ATIVA EXIBIÇÃO DE ERROS
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexao.php');
include('protecao.php');

$mensagem = "";
$class = "";
$usuario_id = $_SESSION['id'];

// --- FUNÇÃO DE UPLOAD (A mesma usada nas outras páginas) ---
function redimensionarESalvarImagem($arquivo)
{
    $diretorio_destino = "img_fornecedores/";
    if (!file_exists($diretorio_destino)) {
        mkdir($diretorio_destino, 0777, true);
    }
    $nome_arquivo = uniqid() . '_' . basename($arquivo["name"]);
    $caminho_completo = $diretorio_destino . $nome_arquivo;
    $tipo_arquivo = strtolower(pathinfo($caminho_completo, PATHINFO_EXTENSION));

    $check = getimagesize($arquivo["tmp_name"]);
    if ($check === false) return "Erro: Arquivo inválido.";
    if ($arquivo["size"] > 5000000) return "Erro: Arquivo muito grande (Max 5MB).";
    if (!in_array($tipo_arquivo, ['jpg', 'jpeg', 'png', 'gif'])) return "Erro: Apenas JPG, PNG ou GIF.";

    if ($tipo_arquivo == "jpg" || $tipo_arquivo == "jpeg") $imagem_original = imagecreatefromjpeg($arquivo["tmp_name"]);
    elseif ($tipo_arquivo == "png") $imagem_original = imagecreatefrompng($arquivo["tmp_name"]);
    elseif ($tipo_arquivo == "gif") $imagem_original = imagecreatefromgif($arquivo["tmp_name"]);

    $largura_original = imagesx($imagem_original);
    $altura_original = imagesy($imagem_original);

    // Redimensiona para max 800px
    $largura_max = 800;
    $altura_max = 800;
    $ratio = min($largura_max / $largura_original, $altura_max / $altura_original);
    $nova_largura = $largura_original * $ratio;
    $nova_altura = $altura_original * $ratio;

    $nova_imagem = imagecreatetruecolor($nova_largura, $nova_altura);

    if ($tipo_arquivo == "png" || $tipo_arquivo == "gif") {
        imagecolortransparent($nova_imagem, imagecolorallocatealpha($nova_imagem, 0, 0, 0, 127));
        imagealphablending($nova_imagem, false);
        imagesavealpha($nova_imagem, true);
    }

    imagecopyresampled($nova_imagem, $imagem_original, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);

    if ($tipo_arquivo == "jpg" || $tipo_arquivo == "jpeg") imagejpeg($nova_imagem, $caminho_completo, 90);
    elseif ($tipo_arquivo == "png") imagepng($nova_imagem, $caminho_completo);
    elseif ($tipo_arquivo == "gif") imagegif($nova_imagem, $caminho_completo);

    imagedestroy($imagem_original);
    imagedestroy($nova_imagem);

    return $caminho_completo;
}

// --- PROCESSAR O FORMULÁRIO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome_fornecedor'];
    $cnpj = $_POST['cnpj'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $obs = $_POST['observacoes'];

    // Upload da Imagem
    $imagem = "";
    $upload_ok = true;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $resultado = redimensionarESalvarImagem($_FILES['imagem']);
        if (strpos($resultado, 'img_fornecedores/') === 0) {
            $imagem = $resultado;
        } else {
            $mensagem = $resultado;
            $class = "error";
            $upload_ok = false;
        }
    }

    if ($upload_ok) {
        $sql = "INSERT INTO fornecedores (usuario_id, nome_fornecedor, cnpj, endereco, telefone, email, observacoes, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("isssssss", $usuario_id, $nome, $cnpj, $endereco, $telefone, $email, $obs, $imagem);

            if ($stmt->execute()) {
                // Redireciona para a lista para ver o resultado
                header("Location: listar_fornecedores.php");
                exit;
            } else {
                $mensagem = "Erro ao cadastrar: " . $stmt->error;
                $class = "error";
            }
        } else {
            $mensagem = "Erro no SQL: " . $mysqli->error;
            $class = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Fornecedor - ALBOZ</title>

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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        a {
            text-decoration: none;
            color: inherit;
            transition: 0.3s;
            cursor: pointer;
        }

        ul {
            list-style: none;
        }

        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background-color: var(--card);
            border-radius: 5px;
            border: 2px solid var(--bg);
        }

        .limitador {
            max-width: 1100px;
            width: 90%;
            margin: 0 auto;
        }

        /* ==================================================================
           3. NAVBAR
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
           4. HEADER
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
           5. CONTAINER E FORMULÁRIO
           ================================================================== */
        .container {
            max-width: 1000px;
            margin: 0 auto 60px auto;
            padding: 30px;
            background: #fff;
            color: #333;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        .form-crud {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--card);
            font-size: 0.9rem;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
            color: #333;
            font-family: inherit;
        }

        input:focus,
        textarea:focus {
            border-color: var(--card);
            outline: none;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-cancelar {
            display: inline-block;
            padding: 12px 25px;
            background: #6c757d;
            color: white;
            border-radius: 4px;
            font-weight: bold;
            margin-left: 10px;
        }

        .btn-cancelar:hover {
            background: #5a6268;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* ==================================================================
           6. RODAPÉ
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

        @media (max-width: 768px) {
            .form-crud {
                grid-template-columns: 1fr;
            }
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
                    <div class="logo-container">
                        <div class="logo">ALBOZ</div>
                    </div>
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
        <div class="subtitulo">Cadastro de Fornecedores</div>
    </div>

    <!-- CONTEÚDO -->
    <div class="container">

        <?php if ($mensagem) echo "<div class='message $class'>$mensagem</div>"; ?>

        <form method="POST" enctype="multipart/form-data" class="form-crud">

            <div>
                <label>Nome do Fornecedor:</label>
                <input type="text" name="nome_fornecedor" required placeholder="Ex: TechLog Brasil">
            </div>

            <div>
                <label>CNPJ:</label>
                <input type="text" name="cnpj" required placeholder="00.000.000/0001-00">
            </div>

            <div>
                <label>Email:</label>
                <input type="email" name="email" placeholder="contato@fornecedor.com">
            </div>

            <div>
                <label>Telefone:</label>
                <input type="text" name="telefone" placeholder="(00) 0000-0000">
            </div>

            <div class="full-width">
                <label>Endereço Completo:</label>
                <input type="text" name="endereco" placeholder="Rua, Número, Bairro, Cidade - UF">
            </div>

            <div class="full-width">
                <label>Observações:</label>
                <textarea name="observacoes" rows="3" placeholder="Informações adicionais..."></textarea>
            </div>

            <div class="full-width">
                <label>Logotipo da Empresa:</label>
                <input type="file" name="imagem" accept="image/*">
            </div>

            <div class="full-width">
                <button type="submit">Salvar Fornecedor</button>
                <a href="painel.php" class="btn-cancelar">Cancelar</a>
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
    </div>>
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