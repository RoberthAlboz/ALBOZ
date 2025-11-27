<?php
session_start(); // Inicia a sessão para poder destruí-la

// 1. Apaga todas as variáveis de sessão
$_SESSION = array();

// 2. Se quiser matar o cookie de sessão também (opcional, mas recomendado)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destrói a sessão no servidor
session_destroy();

// 4. Redireciona para o login
header("Location: login.html");
exit;
?>