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
    // 1. Pega o valor bruto
    $preco_entrada = $_POST['preco'];
    // 2. Remove tudo que NÃO for número ou vírgula (tira R$, pontos, espaços)
    $preco_limpo = preg_replace('/[^0-9,]/', '', $preco_entrada);
    // 3. Troca a vírgula por ponto para o padrão americano do banco (Ex: 1500,50 -> 1500.50)
    $preco = str_replace(',', '.', $preco_limpo);
    
    // Validação extra: se ficou vazio, vira 0
    if(!is_numeric($preco)) $preco = 0.00;

    // --- UPLOAD DE IMAGEM ---
    $imagem = $produto_edit['imagem'] ?? ''; 
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $pasta = "img_produtos/";
        if (!is_dir($pasta)) mkdir($pasta); 
        
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
    <link rel="stylesheet" href="style.css">
    <style>
        .container { max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 8px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #28a745; color: white; padding: 12px; border: none; cursor: pointer; width: 100%; font-size: 16px; border-radius: 4px; transition: 0.3s; }
        button:hover { background-color: #218838; }
        .btn-cancelar { background-color: #6c757d; text-align: center; display: block; text-decoration: none; color: white; padding: 12px; border-radius: 4px; margin-top: 10px; }
        .btn-cancelar:hover { background-color: #5a6268; }
        .img-preview { max-width: 150px; margin-top: 10px; border: 1px solid #ddd; padding: 5px; border-radius: 4px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .error { background-color: #f8d7da; color: #721c24; }
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

        <form method="post" enctype="multipart/form-data" class="form-grid">
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