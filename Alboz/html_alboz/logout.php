<?php
// Inicia a sessão para poder destruí-la
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Se quiser matar o cookie de sessão também (opcional, mas recomendado para limpeza total)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

// Redireciona para a página de login
header("Location: login.php");
exit;
