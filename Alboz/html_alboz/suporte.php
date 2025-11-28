<?php
// Exibe erros para facilitar testes
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexao.php');
include('protecao.php');

$mensagem_aviso = "";
$class_aviso = "";
$usuario_id = $_SESSION['id'];

// --- SALVAR NOVO CHAMADO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assunto = $_POST['assunto'];
    $texto_mensagem = $_POST['mensagem'];

    // Prepara o SQL (Segurança contra invasão)
    $sql = "INSERT INTO suporte (usuario_id, assunto, mensagem) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("iss", $usuario_id, $assunto, $texto_mensagem);
        
        if ($stmt->execute()) {
            $mensagem_aviso = "Chamado de suporte aberto com sucesso! Aguarde nosso retorno.";
            $class_aviso = "success";
        } else {
            $mensagem_aviso = "Erro ao abrir chamado: " . $stmt->error;
            $class_aviso = "error";
        }
    } else {
        $mensagem_aviso = "Erro no banco: " . $mysqli->error;
        $class_aviso = "error";
    }
}

// --- LISTAR MEUS CHAMADOS (Para ver o histórico) ---
$sql_historico = "SELECT * FROM suporte WHERE usuario_id = '$usuario_id' ORDER BY id DESC";
$historico = $mysqli->query($sql_historico);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte Técnico - ALBOZ</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container { max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 8px; }
        
        /* Estilos do Formulário */
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, textarea, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        button { background-color: #007bff; color: white; padding: 12px; border: none; cursor: pointer; width: 100%; font-size: 16px; border-radius: 4px; font-weight: bold; transition: 0.3s; }
        button:hover { background-color: #0056b3; }

        /* Mensagens de Sucesso/Erro */
        .message { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Tabela de Histórico */
        .historico-box { margin-top: 40px; border-top: 2px solid #eee; padding-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background-color: #f8f9fa; color: #333; }
        
        .status-aberto { color: green; font-weight: bold; background: #e8f5e9; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
        .status-fechado { color: #666; background: #eee; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
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
        <div class="subtitulo">Central de Suporte</div>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="container">
        
        <h2>Abrir Novo Chamado</h2>
        <p style="color:#666; margin-bottom:20px;">Descreva seu problema abaixo. Nossa equipe responderá em breve.</p>

        <?php if ($mensagem_aviso): ?>
            <div class="message <?php echo $class_aviso; ?>">
                <?php echo $mensagem_aviso; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label for="assunto">Assunto:</label>
            <select name="assunto" required>
                <option value="">Selecione...</option>
                <option value="Dúvida sobre Produto">Dúvida sobre Produto</option>
                <option value="Problema no Cadastro">Problema no Cadastro</option>
                <option value="Erro no Sistema">Erro no Sistema</option>
                <option value="Sugestão">Sugestão</option>
                <option value="Outros">Outros</option>
            </select>

            <label for="mensagem">Mensagem Detalhada:</label>
            <textarea name="mensagem" rows="6" required placeholder="Descreva o que aconteceu..."></textarea>

            <button type="submit">Enviar Solicitação</button>
        </form>

        <!-- Histórico para conferência -->
        <div class="historico-box">
            <h3>Seus Chamados Recentes</h3>
            <?php if ($historico && $historico->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Assunto</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $historico->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['data_criacao'])); ?></td>
                            <td><?php echo htmlspecialchars($row['assunto']); ?></td>
                            <td>
                                <span class="<?php echo ($row['status'] == 'Aberto') ? 'status-aberto' : 'status-fechado'; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#999; font-style: italic;">Nenhum chamado registrado.</p>
            <?php endif; ?>
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