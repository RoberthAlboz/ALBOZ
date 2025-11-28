<?php
// Exibe erros na tela para facilitar o diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('conexao.php');

// Padroniza a variável de conexão (garante compatibilidade)
if (isset($conn)) { $mysqli = $conn; }

$mensagem = "";
$class = "";

// PROCESSAR O FORMULÁRIO QUANDO ENVIADO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha']; // Senha em texto puro (conforme seu pedido)
    $cnpj = $_POST['cnpj'];

    // 1. Verifica se o usuário já existe pelo e-mail
    $check_sql = "SELECT id FROM usuarios WHERE email = '$email'";
    $check = $mysqli->query($check_sql);
    
    if ($check && $check->num_rows > 0) {
        $mensagem = "Erro: Este e-mail já está cadastrado!";
        $class = "erro";
    } else {
        // 2. Insere o novo usuário
        $sql = "INSERT INTO usuarios (nome, email, senha, cnpj) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssss", $nome, $email, $senha, $cnpj);
            if ($stmt->execute()) {
                $mensagem = "Usuário cadastrado com sucesso!";
                $class = "sucesso";
            } else {
                $mensagem = "Erro ao cadastrar: " . $stmt->error;
                $class = "erro";
            }
        } else {
            $mensagem = "Erro no SQL: " . $mysqli->error;
            $class = "erro";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário - ALBOZ</title>
    
    <!-- CSS INTERNO (Tema Dark/Gold) -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Abhaya+Libre:wght@800&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Monda:wght@400;700&display=swap');

        :root {
            --bg: #001826;        
            --card: #003554;      
            --card-hover: #014e7b;
            --muted: #bfc9ce;
            --accent: #dfe7e9;
            --gold: #ffffff;      
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Monda', sans-serif;
            background: var(--bg);
            color: var(--accent);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a { text-decoration: none; color: inherit; transition: 0.3s; cursor: pointer; }
        
        /* --- NAVBAR --- */
        .card-nav-container {
            position: absolute; top: 2em; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 600px; z-index: 1001;
        }
        .card-nav {
            background-color: #ffffff; border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            color: var(--card); overflow: hidden;
            transition: height 0.4s ease; /* Animação suave da altura */
        }
        .card-nav-top { height: 60px; display: flex; align-items: center; justify-content: center; position: relative; }
        .logo {
            font-family: 'Abhaya Libre', serif; font-weight: 800; font-size: 1.5rem;
            letter-spacing: 1px; text-transform: uppercase;
        }
        .card-nav-content { padding: 10px; text-align: center; }
        .nav-card-links a { color: #555; font-weight: 500; font-size: 0.9rem; }
        .nav-card-links a:hover { color: var(--card-hover); text-decoration: underline; }

        /* --- CONTEÚDO CENTRALIZADO --- */
        .secao_login {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 60px 20px;
        }

        .login-container {
            width: 100%;
            max-width: 500px;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            border-top: 5px solid var(--card);
        }

        h2 {
            font-family: 'Abhaya Libre', serif;
            color: var(--card);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--card);
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #f9f9f9;
            font-size: 1rem;
            color: #333;
            margin-bottom: 20px;
        }
        input:focus {
            outline: none;
            border-color: var(--card);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0, 53, 84, 0.1);
        }

        button {
            width: 100%;
            padding: 14px;
            background: var(--card);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        button:hover {
            background: var(--card-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .msg {
            padding: 15px; margin-bottom: 20px; border-radius: 6px;
            text-align: center; font-weight: bold; font-size: 0.95rem;
        }
        .sucesso { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .erro { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .link-login {
            display: block; text-align: center; margin-top: 20px;
            color: var(--card); font-weight: bold; text-decoration: none;
        }
        .link-login:hover { text-decoration: underline; }

        /* --- RODAPÉ --- */
        .rodape {
            background: #000c13;
            padding: 2rem 0;
            border-top: 1px solid rgba(255,255,255,0.05);
            text-align: center;
            font-size: 0.9rem;
            color: var(--muted);
        }
        
        /* Menu Hambúrguer CSS */
        .hamburger-menu {
            position: absolute; left: 20px; cursor: pointer; display: flex; flex-direction: column; gap: 5px;
        }
        .hamburger-line { width: 25px; height: 2px; background-color: var(--card); transition: 0.3s; }
        .hamburger-menu.open .hamburger-line:first-child { transform: translateY(7px) rotate(45deg); }
        .hamburger-menu.open .hamburger-line:last-child { transform: translateY(0px) rotate(-45deg); }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <div class="card-nav-container">
        <div class="card-nav" id="cardNav">
            <div class="card-nav-top">
                <div class="hamburger-menu" id="hamburgerBtn">
                    <div class="hamburger-line"></div>
                    <div class="hamburger-line"></div>
                </div>
                <div class="logo">ALBOZ</div>
            </div>
            <div class="card-nav-content">
                <div class="nav-card-links">
                    <a href="index.html">Voltar para Início</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ÁREA DE CADASTRO -->
    <div class="secao_login">
        <div class="login-container">
            <h2>Criar Nova Conta</h2>
            
            <?php if ($mensagem): ?>
                <div class="msg <?php echo $class; ?>">
                    <?php echo $mensagem; ?>
                    <?php if($class == 'sucesso'): ?>
                        <br><br><a href="login.php" style="color: #155724; text-decoration: underline;">CLIQUE AQUI PARA ENTRAR</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <label>Nome Completo:</label>
                <input type="text" name="nome" required placeholder="Ex: João da Silva">

                <label>CNPJ:</label>
                <input type="text" name="cnpj" required placeholder="00.000.000/0001-00">

                <label>E-mail (Será seu login):</label>
                <input type="email" name="email" required placeholder="seu@email.com">

                <label>Senha:</label>
                <input type="password" name="senha" required placeholder="Crie uma senha">

                <button type="submit">CADASTRAR</button>
            </form>

            <a href="login.php" class="link-login">Já tem uma conta? Voltar para Login</a>
        </div>
    </div>

    <!-- RODAPÉ -->
    <div class="rodape">
        <div class="limitador">
            <p>© 2025 Alboz. Todos os direitos reservados.</p>
        </div>
    </div>

    <script src="java.js" defer></script>
    <script>
        const hamburger = document.getElementById('hamburgerBtn');
        const nav = document.getElementById('cardNav');
        if(hamburger && nav){
            hamburger.addEventListener('click', () => {
                nav.classList.toggle('open');
                hamburger.classList.toggle('open');
                const content = nav.querySelector('.card-nav-content');
                nav.style.height = nav.classList.contains('open') ? (content.scrollHeight + 60) + 'px' : '60px';
            });
            nav.style.height = '60px';
        }
    </script>
</body>
</html>