<?php
// Configurações do Banco de Dados
$host = "localhost";
$usuario = "root";      // Seu usuário do banco (geralmente é 'root' no XAMPP/WAMP)
$senha = "Senai@118";            // Sua senha do banco (geralmente vazio no XAMPP)
$banco = "algoz"; // COLOQUE AQUI O NOME DO SEU BANCO

// Criar a conexão
$mysqli = new mysqli($host, $usuario, $senha, $banco);

// Verificar se houve erro na conexão
if ($mysqli->connect_error) {
    die("Falha na conexão: " . $mysqli->connect_error);
}

// Configurar caracteres para UTF-8 (evita problemas com acentos)
$mysqli->set_charset("utf8mb4");
?>