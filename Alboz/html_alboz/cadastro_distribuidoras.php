<?php
// INÍCIO DO PHP
// 1. Inclui a conexão (certifique-se que o arquivo conexao.php está na mesma pasta)
include('conexao.php');

$mensagem = ""; // Variável para guardar mensagens de sucesso ou erro

// 2. Verifica se o usuário clicou no botão "Cadastrar"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recebe os dados do formulário
    $nome_empresa = $_POST['nome_empresa'];
    $cnpj = $_POST['cnpj'];

    // Validação simples
    if (empty($nome_empresa) || empty($cnpj)) {
        $mensagem = "Preencha todos os campos!";
    } else {
        // 3. Prepara o SQL para inserir na tabela 'distribuidor'
        $sql = "INSERT INTO distribuidor (nome_empresa, cnpj) VALUES (?, ?)";
        
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ss", $nome_empresa, $cnpj);
            
            if ($stmt->execute()) {
                $mensagem = "Distribuidor cadastrado com sucesso!";
            } else {
                $mensagem = "Erro ao cadastrar: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $mensagem = "Erro no SQL: " . $mysqli->error;
        }
    }
}
// FIM DO PHP
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Distribuidor</title>
</head>
<body>

    <!-- Link para voltar -->
    <p><a href="index.php">Voltar</a></p>

    <!-- Exibe a mensagem do PHP aqui, se houver -->
    <?php if(!empty($mensagem)) echo "<p><strong>$mensagem</strong></p>"; ?>

    <form action="" method="post">
        
        <h3>Novo Distribuidor</h3>
        <p>Cadastre os parceiros fornecedores de produtos esportivos.</p>

        <div>
            <label for="nome_empresa">Nome da Empresa:</label><br>
            <input type="text" id="nome_empresa" name="nome_empresa" placeholder="Ex: TechLog Brasil" required>
        </div>
        <br>

        <div>
            <label for="cnpj">CNPJ:</label><br>
            <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0001-00" required>
        </div>
        <br>
        
        <button type="submit">Salvar Distribuidor</button>

    </form>

</body>
</html>