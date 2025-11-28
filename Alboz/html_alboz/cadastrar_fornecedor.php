<?php
include('conexao.php');
include('protecao.php');

if(isset($_POST['nome_fornecedor'])) {
    $nome = $_POST['nome_fornecedor'];
    $cnpj = $_POST['cnpj'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $obs = $_POST['observacoes'];
    $usuario_id = $_SESSION['id']; // Pega o ID de quem está logado

    $sql = "INSERT INTO fornecedores (usuario_id, nome_fornecedor, cnpj, endereco, telefone, email, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("issssss", $usuario_id, $nome, $cnpj, $endereco, $telefone, $email, $obs);

    if($stmt->execute()) {
        echo "<p style='color:green'>Fornecedor cadastrado com sucesso!</p>";
    } else {
        echo "<p style='color:red'>Erro ao cadastrar.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Cadastrar Fornecedor</title></head>
<body>
    <a href="painel.php">Voltar ao Painel</a>
    <h1>Novo Fornecedor</h1>
    <form method="POST">
        <label>Nome:</label><br><input type="text" name="nome_fornecedor" required><br><br>
        <label>CNPJ:</label><br><input type="text" name="cnpj" required><br><br>
        <label>Endereço:</label><br><input type="text" name="endereco"><br><br>
        <label>Telefone:</label><br><input type="text" name="telefone"><br><br>
        <label>Email:</label><br><input type="email" name="email"><br><br>
        <label>Observações:</label><br><textarea name="observacoes"></textarea><br><br>
        <button type="submit">Salvar</button>
    </form>
</body>
</html>