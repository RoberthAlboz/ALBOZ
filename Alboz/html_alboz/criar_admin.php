<?php
// Exibe erros na tela para facilitar o diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexao.php');

// Padroniza a variável de conexão (garante compatibilidade)
if (isset($conn)) { $mysqli = $conn; }

$mensagem = "";
$class = "";

// PROCESSAR O FORMULÁRIO QUANDO ENVIADO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha']; // Senha em texto puro (conforme seu pedido)
    $cnpj = $_POST['cnpj'];

    // 1. Verifica se o usuário já existe pelo e-mail
    $check_sql = "SELECT id FROM usuarios WHERE email = '$email'";
    $check = $mysqli->query($check_sql);
    
    if ($check && $check->num_rows > 0) {
        $mensagem = "Erro: Este e-mail já está cadastrado!";
        $class = "erro";
    } else {
        // 2. Insere o novo usuário
        $sql = "INSERT INTO usuarios (nome, email, senha, cnpj) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssss", $nome, $email, $senha, $cnpj);
            if ($stmt->execute()) {
                $mensagem = "Usuário cadastrado com sucesso!";
                $class = "sucesso";
            } else {
                $mensagem = "Erro ao cadastrar: " . $stmt->error;
                $class = "erro";
            }
        } else {
            $mensagem = "Erro no SQL: " . $mysqli->error;
            $class = "erro";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário - ALBOZ</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f4f4f4; font-family: Arial, sans-serif; }
        .container { max-width: 500px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; color: #333; margin-top: 0; text-align: center; }
        
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; margin-top: 15px; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        button { background-color: #28a745; color: white; padding: 12px; border: none; cursor: pointer; width: 100%; font-size: 16px; border-radius: 4px; transition: 0.3s; margin-top: 20px; font-weight: bold; }
        button:hover { background-color: #218838; }
        
        .msg { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; font-weight: bold; }
        .sucesso { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .erro { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .link-login { display: block; text-align: center; margin-top: 20px; color: #007bff; text-decoration: none; font-size: 14px; }
        .link-login:hover { text-decoration: underline; }

        /* Pequeno ajuste para o header ficar bonito */
        .header-simples { background-color: #000; color: white; padding: 15px; text-align: center; font-weight: bold; letter-spacing: 2px; }
    </style>
</head>
<body class="body">

    <div class="header-simples">ALBOZ - SISTEMA</div>

    <div class="container">
        <h2>Criar Nova Conta</h2>
        
        <?php if ($mensagem): ?>
            <div class="msg <?php echo $class; ?>">
                <?php echo $mensagem; ?>
                <?php if($class == 'sucesso'): ?>
                    <br><br><a href="login.php" style="color: #155724; font-weight:bold; text-decoration: underline;">CLIQUE AQUI PARA ENTRAR</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>Nome Completo:</label>
            <input type="text" name="nome" required placeholder="Ex: João da Silva">

            <label>CNPJ:</label>
            <input type="text" name="cnpj" required placeholder="00.000.000/0001-00">

            <label>E-mail (Será seu login):</label>
            <input type="email" name="email" required placeholder="seu@email.com">

            <label>Senha:</label>
            <input type="password" name="senha" required placeholder="Crie uma senha">

            <button type="submit">CADASTRAR</button>
        </form>

        <a href="login.php" class="link-login">Já tem uma conta? Voltar para Login</a>
    </div>

</body>
</html>