<?php
// ATIVA EXIBIÇÃO DE ERROS
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexao.php');
include('protecao.php');

$mensagem = "";
$class = "";
$usuario_id = $_SESSION['id'];

// --- FUNÇÃO DE UPLOAD DE IMAGEM ---
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

    // Redimensiona para max 800px mantendo proporção
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

// --- SALVAR (CADASTRO / EDIÇÃO) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $nome = $_POST['nome'];
    $cnpj = $_POST['cnpj'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $observacoes = $_POST['observacoes'];

    // Upload
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
        if ($id) {
            // ATUALIZAR
            $sql = "UPDATE fornecedores SET nome_fornecedor=?, cnpj=?, email=?, telefone=?, endereco=?, observacoes=?";
            $params = [$nome, $cnpj, $email, $telefone, $endereco, $observacoes];
            $types = "ssssss";

            if ($imagem) {
                $sql .= ", imagem=?";
                $params[] = $imagem;
                $types .= "s";
            }

            $sql .= " WHERE id=? AND usuario_id=?";
            $params[] = $id;
            $params[] = $usuario_id;
            $types .= "ii";

            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $msg_sucesso = "Distribuidor atualizado!";
        } else {
            // INSERIR
            $sql = "INSERT INTO fornecedores (usuario_id, nome_fornecedor, cnpj, email, telefone, endereco, observacoes, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("isssssss", $usuario_id, $nome, $cnpj, $email, $telefone, $endereco, $observacoes, $imagem);
            $msg_sucesso = "Distribuidor cadastrado!";
        }

        if (isset($stmt) && $stmt->execute()) {
            $mensagem = $msg_sucesso;
            $class = "success";
            // Limpa o POST para não re-enviar ao atualizar
            $_POST = array();
        } else {
            $mensagem = "Erro no banco: " . $mysqli->error;
            $class = "error";
        }
    }
}

// --- EXCLUSÃO ---
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Verifica produtos vinculados
    $check = $mysqli->query("SELECT COUNT(*) as count FROM produtos WHERE fornecedor_id = '$delete_id'")->fetch_assoc();

    if ($check['count'] > 0) {
        $mensagem = "Não é possível excluir: Existem produtos vinculados a este distribuidor.";
        $class = "error";
    } else {
        $sql = "DELETE FROM fornecedores WHERE id=? AND usuario_id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ii", $delete_id, $usuario_id);

        if ($stmt->execute()) {
            $mensagem = "Distribuidor excluído!";
            $class = "success";
        } else {
            $mensagem = "Erro ao excluir.";
            $class = "error";
        }
    }
}

// --- PESQUISA ---
$busca = "";
$filtro_sql = "";
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $busca = $mysqli->real_escape_string($_GET['busca']);
    $filtro_sql = " AND (nome_fornecedor LIKE '%$busca%' OR cnpj LIKE '%$busca%' OR email LIKE '%$busca%')";
}

// --- LISTAGEM ---
$sql_lista = "SELECT * FROM fornecedores WHERE usuario_id = $usuario_id $filtro_sql ORDER BY id DESC";
$fornecedores = $mysqli->query($sql_lista);

// --- DADOS PARA EDIÇÃO ---
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $mysqli->prepare("SELECT * FROM fornecedores WHERE id=? AND usuario_id=?");
    $stmt->bind_param("ii", $edit_id, $usuario_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Distribuidores - ALBOZ</title>

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
            /* Fallback visual */
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
           5. CONTAINER E FORMULÁRIO (CRUD)
           ================================================================== */
        .container {
            max-width: 1100px;
            margin: 0 auto 60px auto;
            padding: 30px;
            background: #fff;
            color: #333;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            min-height: 400px;
        }

        .container h2 {
            color: var(--card);
            font-family: 'Abhaya Libre', serif;
            font-size: 2rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        /* Form Grid */
        .form-crud {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 40px;
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

        /* ==================================================================
           6. PESQUISA E TABELA
           ================================================================== */
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background: #e9ecef;
            border-radius: 8px;
            align-items: center;
        }

        .btn-search {
            background-color: var(--card);
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            font-weight: bold;
        }

        .btn-limpar {
            background-color: #6c757d;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        th {
            background-color: var(--card);
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        tr:hover {
            background-color: #f1f5f8;
        }

        .img-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
        }

        .no-img {
            font-size: 11px;
            color: #999;
            font-style: italic;
        }

        .acoes a {
            margin-right: 10px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .btn-edit {
            color: #d69e2e;
        }

        .btn-delete {
            color: #dc3545;
        }

        /* Mensagens */
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
           7. RODAPÉ
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

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                background: #fff;
            }

            td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
            }

            td:before {
                position: absolute;
                top: 15px;
                left: 10px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
                color: var(--card);
                content: attr(data-label);
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
        <div class="subtitulo">Gerenciamento de Distribuidores</div>
    </div>

    <!-- CONTEÚDO -->
    <div class="container">

        <?php if ($mensagem): ?>
            <div class="message <?php echo $class; ?>"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE CADASTRO/EDIÇÃO -->
        <h2><?php echo $edit_data ? 'Editar Distribuidor' : 'Cadastrar Novo Distribuidor'; ?></h2>

        <form method="post" action="" enctype="multipart/form-data" class="form-crud">
            <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">

            <div>
                <label>Nome da Empresa:</label>
                <input type="text" name="nome" value="<?php echo $edit_data['nome_fornecedor'] ?? ''; ?>" required placeholder="Ex: TechLog Brasil">
            </div>

            <div>
                <label>CNPJ:</label>
                <input type="text" name="cnpj" value="<?php echo $edit_data['cnpj'] ?? ''; ?>" required placeholder="00.000.000/0001-00">
            </div>

            <div>
                <label>E-mail de Contato:</label>
                <input type="email" name="email" value="<?php echo $edit_data['email'] ?? ''; ?>" placeholder="contato@empresa.com">
            </div>

            <div>
                <label>Telefone:</label>
                <input type="text" name="telefone" value="<?php echo $edit_data['telefone'] ?? ''; ?>" placeholder="(00) 0000-0000">
            </div>

            <div class="full-width">
                <label>Endereço Completo:</label>
                <input type="text" name="endereco" value="<?php echo $edit_data['endereco'] ?? ''; ?>" placeholder="Rua, Número, Bairro, Cidade - UF">
            </div>

            <div class="full-width">
                <label>Observações:</label>
                <textarea name="observacoes" rows="3"><?php echo $edit_data['observacoes'] ?? ''; ?></textarea>
            </div>

            <div class="full-width">
                <label>Logotipo da Empresa:</label>
                <input type="file" name="imagem" accept="image/*">
                <?php if (isset($edit_data['imagem']) && $edit_data['imagem']): ?>
                    <div style="margin-top:10px;">
                        <img src="<?php echo $edit_data['imagem']; ?>" width="100" style="border-radius:4px; border:1px solid #ccc;">
                        <span style="font-size:12px; color:#666;">Imagem Atual</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="full-width">
                <button type="submit"><?php echo $edit_data ? 'Salvar Alterações' : 'Cadastrar Distribuidor'; ?></button>
                <?php if ($edit_data): ?>
                    <a href="listar_fornecedores.php" class="btn-cancelar">Cancelar Edição</a>
                <?php endif; ?>
            </div>
        </form>

        <hr style="border:0; border-top:1px solid #eee; margin: 40px 0;">

        <!-- ÁREA DE LISTAGEM -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0; border:none;">Meus Distribuidores</h2>
        </div>

        <!-- BARRA DE PESQUISA -->
        <form method="GET" class="search-box">
            <input type="text" name="busca" placeholder="Pesquisar por nome, CNPJ ou email..." value="<?php echo htmlspecialchars($busca); ?>">
            <button type="submit" class="btn-search">🔍 Pesquisar</button>
            <?php if (!empty($busca)): ?>
                <a href="listar_fornecedores.php" class="btn-limpar">Limpar</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($busca)): ?>
            <p style="margin-bottom:15px; color:#555;">Resultados para: <strong><?php echo htmlspecialchars($busca); ?></strong></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th width="80">Logo</th>
                    <th>Nome</th>
                    <th>Contato</th>
                    <th>CNPJ</th>
                    <th width="150">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($fornecedores->num_rows > 0): ?>
                    <?php while ($row = $fornecedores->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Logo">
                                <?php if (!empty($row['imagem']) && file_exists($row['imagem'])): ?>
                                    <img src="<?php echo $row['imagem']; ?>" class="img-thumb">
                                <?php else: ?>
                                    <span class="no-img">Sem logo</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Nome">
                                <strong><?php echo $row['nome_fornecedor']; ?></strong>
                            </td>
                            <td data-label="Contato">
                                <?php echo $row['email']; ?><br>
                                <small><?php echo $row['telefone']; ?></small>
                            </td>
                            <td data-label="CNPJ"><?php echo $row['cnpj']; ?></td>
                            <td class="acoes" data-label="Ações">
                                <a href="?edit_id=<?php echo $row['id']; ?>" class="btn-edit">Editar</a>
                                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Tem certeza? Se houver produtos vinculados, eles impedirão a exclusão.')">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:#666;">
                            <?php if (!empty($busca)): ?>
                                Nenhum distribuidor encontrado na busca.
                            <?php else: ?>
                                Nenhum distribuidor cadastrado. Utilize o formulário acima.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top:30px;">
            <a href="painel.php" style="text-decoration:none; color:var(--card); font-weight:bold;">&larr; Voltar ao Painel</a>
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