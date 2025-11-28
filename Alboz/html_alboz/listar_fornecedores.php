<?php
include('conexao.php');
include('protecao.php'); // Substitui o valida_sessao.php do seu exemplo

// --- FUNÇÃO DE UPLOAD DE IMAGEM (Adaptada para img_fornecedores/) ---
function redimensionarESalvarImagem($arquivo, $largura = 800, $altura = 800) {
    $diretorio_destino = "img_fornecedores/";
    if (!file_exists($diretorio_destino)) {
        mkdir($diretorio_destino, 0777, true);
    }
    $nome_arquivo = uniqid() . '_' . basename($arquivo["name"]);
    $caminho_completo = $diretorio_destino . $nome_arquivo;
    $tipo_arquivo = strtolower(pathinfo($caminho_completo, PATHINFO_EXTENSION));

    $check = getimagesize($arquivo["tmp_name"]);
    if($check === false) return "O arquivo não é uma imagem válida.";
    if ($arquivo["size"] > 5000000) return "O arquivo é muito grande. O tamanho máximo permitido é 5MB.";
    if($tipo_arquivo != "jpg" && $tipo_arquivo != "png" && $tipo_arquivo != "jpeg" && $tipo_arquivo != "gif" ) return "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";

    if ($tipo_arquivo == "jpg" || $tipo_arquivo == "jpeg") $imagem_original = imagecreatefromjpeg($arquivo["tmp_name"]);
    elseif ($tipo_arquivo == "png") $imagem_original = imagecreatefrompng($arquivo["tmp_name"]);
    elseif ($tipo_arquivo == "gif") $imagem_original = imagecreatefromgif($arquivo["tmp_name"]);

    $largura_original = imagesx($imagem_original);
    $altura_original = imagesy($imagem_original);

    $ratio = min($largura / $largura_original, $altura / $altura_original);
    $nova_largura = $largura_original * $ratio;
    $nova_altura = $altura_original * $ratio;

    $nova_imagem = imagecreatetruecolor($nova_largura, $nova_altura);
    
    // Preserva transparência
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

// --- LÓGICA DE CADASTRO / EDIÇÃO ---
$mensagem = "";
$class = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $nome = $_POST['nome'];
    $cnpj = $_POST['cnpj'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $observacoes = $_POST['observacoes'];
    
    $imagem = "";
    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $resultado_upload = redimensionarESalvarImagem($_FILES['imagem']);
        if(strpos($resultado_upload, 'img_fornecedores/') === 0) {
            $imagem = $resultado_upload;
        } else {
            $mensagem = $resultado_upload; // Erro de upload
        }
    }

    if ($id) {
        // ATUALIZAR (Garante que só edita se for do usuário logado)
        $sql = "UPDATE fornecedores SET nome_fornecedor=?, cnpj=?, email=?, telefone=?, endereco=?, observacoes=?";
        $params = [$nome, $cnpj, $email, $telefone, $endereco, $observacoes];
        
        if($imagem) {
            $sql .= ", imagem=?";
            $params[] = $imagem;
        }
        
        $sql .= " WHERE id=? AND usuario_id=?";
        $params[] = $id;
        $params[] = $_SESSION['id'];
        
        $types = str_repeat('s', count($params) - 2) . "ii"; // Tipos aproximados
        $stmt = $mysqli->prepare($sql);
        
        // Ajuste dinâmico de bind_param
        // Como o mysqli requer tipos, vamos simplificar assumindo string 's' para tudo exceto IDs
        $types = "ssssss" . ($imagem ? "s" : "") . "ii";
        
        $stmt->bind_param($types, ...$params);
        $mensagem = "Fornecedor atualizado com sucesso!";
    } else {
        // INSERIR NOVO
        $sql = "INSERT INTO fornecedores (usuario_id, nome_fornecedor, cnpj, email, telefone, endereco, observacoes, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $usuario_id = $_SESSION['id'];
        $stmt->bind_param("isssssss", $usuario_id, $nome, $cnpj, $email, $telefone, $endereco, $observacoes, $imagem);
        $mensagem = "Fornecedor cadastrado com sucesso!";
    }

    if (isset($stmt) && $stmt->execute()) {
        $class = "success";
    } else {
        $mensagem = "Erro: " . $mysqli->error;
        $class = "error";
    }
}

// --- LÓGICA DE EXCLUSÃO ---
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $usuario_id = $_SESSION['id'];

    // Verifica se existem produtos (pacotes) vinculados a este fornecedor
    // Adaptado do seu código: "check_pacotes" virou "check_produtos"
    $check_produtos = $mysqli->query("SELECT COUNT(*) as count FROM produtos WHERE fornecedor_id = '$delete_id'")->fetch_assoc();
   
    if ($check_produtos['count'] > 0) {
        $mensagem = "Não é possível excluir este fornecedor pois existem produtos cadastrados para ele.";
        $class = "error";
    } else {
        $sql = "DELETE FROM fornecedores WHERE id=? AND usuario_id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ii", $delete_id, $usuario_id);
        
        if ($stmt->execute()) {
            $mensagem = "Fornecedor excluído com sucesso!";
            $class = "success";
        } else {
            $mensagem = "Erro ao excluir: " . $mysqli->error;
            $class = "error";
        }
    }
}

// --- LISTAGEM DE FORNECEDORES ---
$usuario_id = $_SESSION['id'];
$fornecedores = $mysqli->query("SELECT * FROM fornecedores WHERE usuario_id = $usuario_id ORDER BY id DESC");

// --- BUSCAR DADOS PARA EDIÇÃO ---
$fornecedor_edit = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $mysqli->prepare("SELECT * FROM fornecedores WHERE id=? AND usuario_id=?");
    $stmt->bind_param("ii", $edit_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fornecedor_edit = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Distribuidores - ALBOZ</title>
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Reutilizando o CSS do CRUD de Produtos para manter padrão */
        .container-crud {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            border-radius: 8px;
        }

        h2.titulo-crud {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-crud {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        
        .full-width { grid-column: 1 / -1; }

        .form-crud label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-crud input, .form-crud select, .form-crud textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;
        }
        .form-crud button {
            background-color: #28a745; color: white; padding: 12px; border: none; cursor: pointer; border-radius: 4px; font-size: 16px; margin-top: 10px;
        }
        .form-crud button:hover { background-color: #218838; }

        table.tabela-crud { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.tabela-crud th, table.tabela-crud td { border: 1px solid #eee; padding: 12px; text-align: left; }
        table.tabela-crud th { background-color: #007bff; color: white; }
        table.tabela-crud tr:nth-child(even) { background-color: #f8f9fa; }
        
        .img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        
        .acoes a { margin-right: 10px; text-decoration: none; font-weight: bold; }
        .btn-edit { color: #ffc107; }
        .btn-delete { color: #dc3545; }
        
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        
        .update-image { max-width: 100px; margin-top: 10px; border-radius: 4px; }

        @media (max-width: 768px) {
            .form-crud { grid-template-columns: 1fr; }
            table.tabela-crud { display: block; overflow-x: auto; }
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
        <div class="subtitulo">Gerenciamento de Distribuidores</div>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="container-crud">
        
        <?php if ($mensagem): ?>
            <div class="message <?php echo $class; ?>"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <h2 class="titulo-crud"><?php echo $fornecedor_edit ? 'Editar Distribuidor' : 'Cadastrar Novo Distribuidor'; ?></h2>

        <!-- FORMULÁRIO -->
        <form method="post" action="" enctype="multipart/form-data" class="form-crud">
            <input type="hidden" name="id" value="<?php echo $fornecedor_edit['id'] ?? ''; ?>">
            
            <div>
                <label for="nome">Nome da Empresa:</label>
                <input type="text" name="nome" value="<?php echo $fornecedor_edit['nome_fornecedor'] ?? ''; ?>" required>
            </div>
            
            <div>
                <label for="cnpj">CNPJ:</label>
                <input type="text" name="cnpj" value="<?php echo $fornecedor_edit['cnpj'] ?? ''; ?>" required>
            </div>

            <div>
                <label for="email">E-mail de Contato:</label>
                <input type="email" name="email" value="<?php echo $fornecedor_edit['email'] ?? ''; ?>">
            </div>

            <div>
                <label for="telefone">Telefone:</label>
                <input type="text" name="telefone" value="<?php echo $fornecedor_edit['telefone'] ?? ''; ?>">
            </div>

            <div class="full-width">
                <label for="endereco">Endereço Completo:</label>
                <input type="text" name="endereco" value="<?php echo $fornecedor_edit['endereco'] ?? ''; ?>">
            </div>

            <div class="full-width">
                <label for="observacoes">Observações:</label>
                <textarea name="observacoes" rows="3"><?php echo $fornecedor_edit['observacoes'] ?? ''; ?></textarea>
            </div>

            <div class="full-width">
                <label for="imagem">Logotipo / Imagem:</label>
                <input type="file" name="imagem" accept="image/*">
                <?php if (isset($fornecedor_edit['imagem']) && $fornecedor_edit['imagem']): ?>
                    <br>
                    <img src="<?php echo $fornecedor_edit['imagem']; ?>" alt="Imagem atual" class="update-image">
                <?php endif; ?>
            </div>
            
            <div class="full-width">
                <button type="submit"><?php echo $fornecedor_edit ? 'Salvar Alterações' : 'Cadastrar Distribuidor'; ?></button>
                <?php if($fornecedor_edit): ?>
                    <a href="listar_fornecedores.php" style="margin-left:15px; text-decoration:none; color:#666;">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>

        <h2 class="titulo-crud">Lista de Distribuidores</h2>

        <table class="tabela-crud">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Nome</th>
                    <th>Contato</th>
                    <th>CNPJ</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if($fornecedores->num_rows > 0): ?>
                    <?php while ($row = $fornecedores->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if ($row['imagem']): ?>
                                <img src="<?php echo $row['imagem']; ?>" class="img-thumb">
                            <?php else: ?>
                                <span style="font-size:10px; color:#999;">Sem logo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo $row['nome_fornecedor']; ?></strong>
                        </td>
                        <td>
                            <?php echo $row['email']; ?><br>
                            <small><?php echo $row['telefone']; ?></small>
                        </td>
                        <td><?php echo $row['cnpj']; ?></td>
                        <td class="acoes">
                            <a href="?edit_id=<?php echo $row['id']; ?>" class="btn-edit">Editar</a>
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir? Isso só funcionará se ele não tiver produtos.')">Excluir</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">Nenhum distribuidor cadastrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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