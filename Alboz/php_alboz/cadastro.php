<?php
// 1. Inclui o arquivo de conexão
include('conexao.php');

// 2. Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. Recebe os dados do formulário (o nome dentro de $_POST[''] deve ser igual ao name="" do HTML)
    $nome = $_POST['nome'];
    $ie = $_POST['IE'];
    $cnpj = $_POST['cnpj'];
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $uf = $_POST['uf'];
    $email = $_POST['email'];
    $celular = $_POST['celular'];
    $senha = $_POST['senha'];

    // 4. Validação básica (Opcional, mas recomendada)
    if (empty($nome) || empty($email) || empty($senha) || empty($cnpj)) {
        die("Por favor, preencha todos os campos obrigatórios.");
    }

    // 5. Criptografia da Senha (MUITO IMPORTANTE)
    // Nunca salvamos a senha pura. Criamos um 'hash' seguro.
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // 6. Prepara o comando SQL (Previne SQL Injection)
    $sql = "INSERT INTO usuarios (nome, inscricao_estadual, cnpj, rua, numero, bairro, cidade, uf, email, celular, senha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);

    // O "s" significa "string". Temos 11 campos, então são 11 "s".
    // Se IE fosse número inteiro no banco, seria "i". Mas definimos tudo como VARCHAR no banco, então é tudo "s".
    $stmt->bind_param("sssssssssss", $nome, $ie, $cnpj, $rua, $numero, $bairro, $cidade, $uf, $email, $celular, $senha_hash);

    // 7. Executa e verifica se deu certo
    if ($stmt->execute()) {
        echo "<script>
                alert('Cadastro realizado com sucesso!');
                window.location.href = 'login.html'; // Redireciona para login
              </script>";
    } else {
        // Verifica se o erro é de duplicidade (ex: email ou CNPJ já cadastrado)
        if ($mysqli->errno == 1062) {
            echo "Erro: Este E-mail ou CNPJ já está cadastrado.";
        } else {
            echo "Erro ao cadastrar: " . $mysqli->error;
        }
    }

    // Fecha a conexão
    $stmt->close();
    $mysqli->close();
}
?>