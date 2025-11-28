<?php
// ATENÇÃO: Habilita exibição de erros na tela para descobrirmos o problema
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tenta incluir a conexão
if (!file_exists('conexao.php')) {
    die("ERRO FATAL: O arquivo 'conexao.php' não foi encontrado na mesma pasta!");
}
include('conexao.php');

// Padroniza a variável de conexão (aceita tanto $mysqli quanto $conn)
if (isset($conn)) { $mysqli = $conn; }
if (!isset($mysqli)) {
    die("ERRO NO BANCO: A variável de conexão não existe. Verifique seu arquivo conexao.php.");
}

// Inicia sessão
if(!isset($_SESSION)) {
    session_start();
}

$erro = "";

// LÓGICA DE LOGIN
if(isset($_POST['email']) && isset($_POST['senha'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // 1. Verifica se a tabela existe e busca o usuário
    $sql_code = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql_code);
    
    if ($stmt === false) {
        die("ERRO NO SQL: " . $mysqli->error . " (Verifique se a tabela 'usuarios' e a coluna 'email' existem)");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    // 2. Verifica o resultado
    if ($usuario) {
        // Usuário encontrado! Comparação direta (sem hash) conforme solicitado.
        if ($usuario['senha'] == $senha) {
            // SUCESSO!
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            header("Location: listar_produtos.php");
            exit;
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "E-mail não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ALBOZ</title>
    
    <!-- CSS INTERNO COMPLETO (Tema Dark/Gold) -->
    <style>
        /* ==================================================================
           1. IMPORTAÇÃO E VARIÁVEIS
           ================================================================== */
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

        /* ==================================================================
           2. GERAL
           ================================================================== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: 'Monda', sans-serif;
            background: var(--bg);
            color: var(--accent);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a { text-decoration: none; color: inherit; transition: 0.3s; cursor: pointer; }
        ul { list-style: none; }

        .limitador { max-width: 1100px; width: 90%; margin: 0 auto; }

        /* ==================================================================
           3. NAVBAR (Simplificada para Login)
           ================================================================== */
        .card-nav-container {
            position: absolute; top: 2em; left: 50%; transform: translateX(-50%);
            width: 90%; max-width: 600px; z-index: 1001;
        }
        .card-nav {
            position: relative; background-color: #ffffff; border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden;
            color: var(--card);
        }
        .card-nav-top {
            height: 60px; display: flex; align-items: center; justify-content: center;
            padding: 0 1.5rem; position: relative;
        }
        .logo {
            font-family: 'Abhaya Libre', serif; font-weight: 800; font-size: 1.5rem;
            letter-spacing: 1px; text-transform: uppercase;
        }
        .card-nav-content { padding: 1rem; display: flex; flex-direction: column; gap: 10px; margin-top: 10px; }
        
        .nav-card { background: #f4f6f8; border: 1px solid #e0e0e0; padding: 10px; border-radius: 8px; text-align: center; }
        .nav-card-links a { display: inline-block; font-size: 0.9rem; color: #555; font-weight: 500; }
        .nav-card-links a:hover { text-decoration: underline; color: var(--card-hover); }

        /* ==================================================================
           4. ÁREA DE LOGIN (CENTRALIZADA)
           ================================================================== */
        .secao_login {
            flex: 1; /* Ocupa o espaço restante para empurrar o rodapé */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 60px 20px; /* Espaço para o menu flutuante */
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            text-align: center;
            position: relative;
            border-top: 5px solid var(--card);
        }

        .login-container h2 {
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            margin: 0 0 20px 0;
            color: var(--card);
            font-weight: 400;
        }
        
        .login-subtitle {
            font-family: 'Abhaya Libre', serif;
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Formulário */
        .form-group { text-align: left; }
        
        .field { margin-bottom: 20px; }
        
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
            transition: 0.3s;
            color: #333;
        }

        input:focus {
            outline: none;
            border-color: var(--card);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0, 53, 84, 0.1);
        }

        .btn-submit {
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
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            background: var(--card-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Mensagens de Erro */
        .msg-erro {
            background-color: #ffe6e6;
            color: #b71c1c;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #ffcdd2;
            font-size: 0.95rem;
        }

        /* Rodapé do Form */
        hr { border: 0; border-top: 1px solid #eee; margin: 30px 0; }
        
        .links-extra p { font-size: 0.9rem; color: #777; margin-bottom: 10px; }
        .links-extra a { color: var(--card); font-weight: bold; }
        .links-extra a:hover { text-decoration: underline; }

        /* ==================================================================
           5. RODAPÉ
           ================================================================== */
        .rodape {
            background: #000c13;
            padding: 2rem 0;
            border-top: 1px solid rgba(255,255,255,0.05);
            text-align: center;
            font-size: 0.9rem;
            color: var(--muted);
        }
        
        /* Menu Hamburguer (Funcionalidade Visual) */
        .hamburger-menu {
            position: absolute; left: 20px; cursor: pointer; display: flex; flex-direction: column; gap: 5px;
        }
        .hamburger-line { width: 25px; height: 2px; background-color: var(--card); transition: 0.3s; }
        .hamburger-menu.open .hamburger-line:first-child { transform: translateY(7px) rotate(45deg); }
        .hamburger-menu.open .hamburger-line:last-child { transform: translateY(0px) rotate(-45deg); }

    </style>
</head>
<body>
    
    <!-- NAVBAR FLUTUANTE -->
    <div class="card-nav-container">
        <div class="card-nav" id="cardNav">
            <div class="card-nav-top">
                <!-- Botão hamburger apenas visual neste caso, pois o menu é simples -->
                <div class="hamburger-menu" id="hamburgerBtn">
                    <div class="hamburger-line"></div>
                    <div class="hamburger-line"></div>
                </div>
                <div class="logo">ALBOZ</div>
            </div>
            
            <div class="card-nav-content">
                <div class="nav-card">
                    <div class="nav-card-links">
                        <a href="index.html">Voltar para a Página Inicial</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ÁREA DE LOGIN -->
    <div class="secao_login">
        <div class="login-container">
            <h2>Bem-vindo</h2>
            <div class="login-subtitle">Acesse sua conta</div>
            
            <?php if($erro): ?>
                <div class="msg-erro">
                    <strong>Atenção:</strong> <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <div class="field">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required placeholder="admin@teste.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="field">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" required placeholder="Digite sua senha">
                    </div>
                    
                    <button type="submit" class="btn-submit">Entrar</button>
                </div>
            </form>

            <hr>
            
            <div class="links-extra">
                <p>Não possui cadastro?</p>
                <a href="criar_admin.php">Criar nova conta de usuário</a>
            </div>
        </div>
    </div>

    <!-- RODAPÉ -->
    <div class="rodape">
        <div class="limitador">
            <p>© 2025 Alboz. Todos os direitos reservados.</p>
        </div>
    </div>
    
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