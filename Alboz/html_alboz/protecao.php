<?php
// Inicia a sessão se ela ainda não existir
if(!isset($_SESSION)) {
    session_start();
}

// Verifica se o usuário está logado (se tem ID na sessão)
// Se não tiver, manda de volta para o login
if(!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit; // Mata o script para não carregar o resto da página
}
?>