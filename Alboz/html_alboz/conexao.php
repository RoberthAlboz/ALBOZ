<?php
// Configurações do Banco de Dados
$host = "localhost";
$usuario = "root";      // Usuário padrão do XAMPP
$senha = "Senai@118";            // Senha padrão do XAMPP (geralmente vazia)
$banco = "alboz";       // <--- VERIFIQUE SE O NOME DO SEU BANCO ESTÁ CERTO AQUI

// Criar a conexão
$mysqli = new mysqli($host, $usuario, $senha, $banco);

// Verificar se houve erro na conexão
if ($mysqli->connect_error) {
    die("Falha na conexão: " . $mysqli->connect_error);
}

// Configurar caracteres para UTF-8 (evita problemas com acentos)
$mysqli->set_charset("utf8mb4");

// Variável extra para compatibilidade (alguns códigos usam $conn, outros $mysqli)
$conn = $mysqli;
?>