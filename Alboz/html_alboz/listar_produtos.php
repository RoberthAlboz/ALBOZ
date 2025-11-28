<?php
// ATIVA EXIBIÇÃO DE ERROS (Para sabermos se algo der errado)
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
    
    // SQL Seguro: Só deleta se o produto pertencer a um fornecedor DO USUÁRIO LOGADO
    // Isso impede que um usuário apague produtos de outro
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

// --- LISTAGEM DE PRODUTOS ---
// Tenta buscar os produtos fazendo JOIN com fornecedores
$sql_listagem = "SELECT p.*, f.nome_fornecedor 
                 FROM produtos p 
                 JOIN fornecedores f ON p.fornecedor_id = f.id 
                 WHERE f.usuario_id = '$usuario_id'
                 ORDER BY p.id DESC";

$produtos = $mysqli->query($sql_listagem);

// SE O SQL FALHAR (Ex: Tabela não existe ou nome da coluna errado)
if (!$produtos) {
    die("<div style='padding:20px; background:#ffe6e6; border:1px solid red; color:#721c24; font-family:Arial; margin:20px;'>
            <h2>❌ Erro Crítico no Banco de Dados</h2>
            <p>Não foi possível carregar a lista de produtos.</p>
            <p><strong>O MySQL disse:</strong> " . $mysqli->error . "</p>
            <hr>
            <p><strong>Possíveis Causas:</strong></p>
            <ul>
                <li>A tabela 'produtos' ou 'fornecedores' não existe.</li>
                <li>Você não criou a coluna 'imagem' (Rode: <code>ALTER TABLE produtos ADD COLUMN imagem VARCHAR(255);</code>).</li>
                <li>Os nomes das colunas no banco estão diferentes do código (id, nome_produto, preco_unitario).</li>
            </ul>
            <p><a href='painel.php'>Voltar ao Painel</a></p>
         </div>");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Produtos - ALBOZ</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* CSS Específico da Tabela */
        .container { max-width: 1100px; margin: 40px auto; padding: 20px; background: #fff; box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 8px; min-height: 400px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: middle; }
        th { background-color: #007bff; color: white; font-weight: bold; }
        tr:hover { background-color: #f8f9fa; }

        .img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
        .no-img { font-size: 11px; color: #999; font-style: italic; }

        .btn-novo { background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display:inline-block; font-weight: bold; transition: 0.3s; }
        .btn-novo:hover { background-color: #218838; }

        .acoes a { text-decoration: none; font-weight: bold; margin-right: 10px; font-size: 14px; }
        .btn-edit { color: #ffc107; }
        .btn-delete { color: #dc3545; }

        .message { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body class="body">

    <!-- HEADER / MENU SUPERIOR -->
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
        <div class="subtitulo">Gerenciamento de Produtos</div>
    </div>

    <!-- ÁREA DE CONTEÚDO -->
    <div class="container">
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0; color:#333;">Meus Produtos</h2>
            <a href="cadastrar_produtos.php" class="btn-novo">+ Novo Produto</a>
        </div>

        <?php if ($mensagem) echo "<div class='message $class'>$mensagem</div>"; ?>

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
                        <td>
                            <?php 
                            // Verifica se existe imagem cadastrada
                            if (!empty($row['imagem']) && file_exists($row['imagem'])) {
                                echo "<img src='" . $row['imagem'] . "' class='img-thumb'>";
                            } else {
                                echo "<span class='no-img'>Sem foto</span>";
                            }
                            ?>
                        </td>
                        <td>
                            <strong><?php echo $row['nome_produto']; ?></strong>
                            <?php if(!empty($row['descricao'])): ?>
                                <br><small style="color:#666;"><?php echo substr($row['descricao'], 0, 40) . '...'; ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['nome_fornecedor']; ?></td>
                        <td>R$ <?php echo number_format($row['preco_unitario'], 2, ',', '.'); ?></td>
                        <td>
                            <?php 
                                echo $row['quantidade_estoque']; 
                                echo ($row['quantidade_estoque'] <= 5) ? " <span style='color:red; font-size:10px;'>(Baixo)</span>" : "";
                            ?>
                        </td>
                        <td class="acoes">
                            <a href="cadastrar_produtos.php?edit_id=<?php echo $row['id']; ?>" class="btn-edit">Editar</a>
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:#666;">
                            Nenhum produto encontrado.<br><br>
                            <a href="cadastrar_produtos.php" style="color:blue;">Clique aqui para cadastrar o primeiro!</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top:20px;">
            <a href="painel.php" style="text-decoration:none; color:#555;">&larr; Voltar ao Painel Principal</a>
        </div>
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
        // Script do Menu Hamburguer para funcionar o mobile
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