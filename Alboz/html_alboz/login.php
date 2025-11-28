<?php
// ATENÇÃO: Habilita exibição de erros na tela para descobrirmos o problema
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tenta incluir a conexão
if (!file_exists('conexao.php')) {
    die("ERRO FATAL: O arquivo 'conexao.php' não foi encontrado na mesma pasta!");
}
include('conexao.php');

// Padroniza a variável de conexão (aceita tanto $mysqli quanto $conn)
if (isset($conn)) { $mysqli = $conn; }
if (!isset($mysqli)) {
    die("ERRO NO BANCO: A variável de conexão não existe. Verifique seu arquivo conexao.php.");
}

// Inicia sessão
if(!isset($_SESSION)) {
    session_start();
}

$erro = "";

// LÓGICA DE LOGIN
if(isset($_POST['email']) && isset($_POST['senha'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // 1. Verifica se a tabela existe e busca o usuário
    $sql_code = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql_code);
    
    if ($stmt === false) {
        die("ERRO NO SQL: " . $mysqli->error . " (Verifique se a tabela 'usuarios' e a coluna 'email' existem)");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    // 2. Verifica o resultado
    if ($usuario) {
        // Usuário encontrado! Vamos ver a senha.
        // DEBUG: Descomente a linha abaixo se quiser ver a senha que está no banco na tela
        // echo "Senha no Banco: " . $usuario['senha'] . " | Senha Digitada: " . $senha;

        if ($usuario['senha'] == $senha) {
            // SUCESSO!
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            header("Location: listar_produtos.php");
            exit;
        } else {
            $erro = "Senha incorreta! (Você digitou '$senha', mas no banco está diferente)";
        }
    } else {
        $erro = "E-mail não encontrado no banco de dados.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ALBOZ</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .secao_login { min-height: 60vh; display: flex; align-items: center; justify-content: center; }
        .form-cadastro { max-width: 400px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .msg-erro { background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    
    <!-- NAVBAR SIMPLIFICADA -->
    <div class="header">
        <div class="card-nav-container">
            <div class="card-nav" id="cardNav">
                <div class="card-nav-top">
                    <div class="logo-container"><div class="logo">ALBOZ</div></div>
                </div>
                <div class="card-nav-content">
                    <div class="nav-card">
                        <div class="nav-card-label">Navegação</div>
                        <div class="nav-card-links">
                            <a class="nav-card-link" href="index.html">Voltar ao Início</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LOGIN -->
    <div id="login" class="secao_login">
        <div class="limitador">
            <h2 style="text-align: center; margin-bottom: 20px;">Acesso ao Sistema</h2>
            
            <form action="" method="post" class="form-cadastro">
                
                <!-- Exibe erros aqui -->
                <?php if($erro): ?>
                    <div class="msg-erro">
                        <strong>Ops!</strong> <?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <div class="field">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required placeholder="admin@teste.com">
                    </div>
                    <br>
                    <div class="field">
                        <label for="senha">Senha:</label>
                        <input type="password" id="senha" name="senha" required placeholder="12345">
                    </div>
                    <br>
                    <button type="submit" class="btn-submit">Entrar</button>
                    
                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
                    
                    <!-- BOTÃO DE EMERGÊNCIA: Cria o usuário se ele não existir -->
                    <p style="text-align:center; font-size:12px; color:#666;">
                        Não possui cadastro? <a href="criar_admin.php" style="color:blue;">Clique aqui para criar outro usuario/administrador.</a>.
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="java.js" defer></script>
</body>
</html>