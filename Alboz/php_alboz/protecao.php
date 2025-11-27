<?php
if(!isset($_SESSION)) {
    session_start();
}

// Se não existir a informação do ID na sessão, significa que não está logado
if(!isset($_SESSION['id'])) {
    // Mata o script e exibe uma mensagem ou redireciona
    die("Você não pode acessar esta página porque não está logado.<p><a href=\"login.php\">Entrar</a></p>");
}
?>