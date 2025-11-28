<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('conexao.php');

echo "<h1>Diagnóstico do Sistema</h1>";

// 1. Verifica Conexão
if ($mysqli->connect_error) {
    die("<p style='color:red'>ERRO DE CONEXÃO: " . $mysqli->connect_error . "</p>");
}
echo "<p style='color:green'>✅ Conexão com Banco OK!</p>";

// 2. Verifica Sessão
if (isset($_SESSION['id'])) {
    echo "<p style='color:green'>✅ Usuário Logado: ID " . $_SESSION['id'] . " (" . $_SESSION['nome'] . ")</p>";
} else {
    echo "<p style='color:red'>❌ ERRO: Nenhuma sessão de usuário encontrada. Faça login novamente.</p>";
}

// 3. Verifica Tabela Produtos
$result = $mysqli->query("SHOW COLUMNS FROM produtos");
if ($result) {
    echo "<p style='color:green'>✅ Tabela 'produtos' encontrada. Colunas:</p><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['Field'] . "</li>";
    }
    echo "</ul>";

    // Verifica se tem a coluna imagem
    $tem_imagem = false;
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'imagem') $tem_imagem = true;
    }

    if (!$tem_imagem) {
        echo "<p style='color:red'>⚠️ ALERTA CRÍTICO: A coluna 'imagem' não existe na tabela produtos! O código vai quebrar.</p>";
        echo "<p>Rode este SQL no banco: <code>ALTER TABLE produtos ADD COLUMN imagem VARCHAR(255);</code></p>";
    }
} else {
    echo "<p style='color:red'>❌ ERRO CRÍTICO: Tabela 'produtos' não existe! (" . $mysqli->error . ")</p>";
}

// 4. Teste de Leitura
$sql = "SELECT * FROM produtos LIMIT 1";
$query = $mysqli->query($sql);
if ($query) {
    echo "<p style='color:green'>✅ SELECT em produtos funcionou.</p>";
} else {
    echo "<p style='color:red'>❌ ERRO NO SQL: " . $mysqli->error . "</p>";
}
