<?php
// Mantém a proteção
include('protecao.php');

// ==========================================================
// 1. CONFIGURAÇÃO DO BANCO DE DADOS
// ==========================================================
$host = 'localhost';      // Geralmente é localhost
$db   = 'NOME_DO_SEU_BANCO'; // <--- COLOQUE O NOME DO SEU BANCO AQUI
$user = 'root';           // Seu usuário do MySQL (padrão do XAMPP é 'root')
$pass = '';               // Sua senha do MySQL (padrão do XAMPP é vazio)

try {
    // Cria a conexão usando PDO (mais seguro)
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

// ==========================================================
// 2. LÓGICA DE BUSCA REAL
// ==========================================================
$resultados = [];
$termo_busca = "";
$buscou = false;

if (isset($_GET['busca'])) {
    $termo_busca = $_GET['busca'];
    $buscou = true;

    try {
        // Prepara o SQL para buscar onde o nome se parece com o termo digitado
        // ATENÇÃO: Troque 'produtos' pelo nome da sua tabela no banco
        $sql = "SELECT * FROM produtos WHERE nome LIKE :termo";
        
        $stmt = $pdo->prepare($sql);
        
        // Adiciona as porcentagens (%) para buscar em qualquer parte do texto
        $stmt->bindValue(':termo', '%' . $termo_busca . '%');
        $stmt->execute();

        // Pega todos os resultados e joga na variável $resultados
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Erro na busca: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Produtos | ALBOZ</title>
    <link rel="stylesheet" href="style.css">
    <script src="java.js" defer></script>
</head>
<body class="body">

    <div class="background-carousel">
        <img class="slide" src="../img.alboz/eugene-lim-J9yPHHc0Fe4-unsplash.jpg" alt="Fundo 1">
        <img class="slide" src="../img.alboz/maarten-duineveld-pmfJcN7RGiw-unsplash (2).jpg" alt="Fundo 2">
        <img class="slide" src="../img.alboz/spencer-davis-vdg14E8KsVM-unsplash.jpg" alt="Fundo 3">
        <div class="overlay-escuro"></div>
    </div>

    <div class="header-reduzido">
        <div class="card-nav-container">
            <div class="card-nav" id="cardNav">
                <div class="card-nav-top">
                    <div class="hamburger-menu" id="hamburgerBtn">
                        <div class="hamburger-line"></div>
                        <div class="hamburger-line"></div>
                    </div>
                    <div class="logo-container">
                        <a href="index.html" class="logo">ALBOZ</a>
                    </div>
                </div>

                <div class="card-nav-content">
                    <div class="nav-card">
                        <div class="nav-card-label">Início</div>
                        <div class="nav-card-links">
                            <a class="nav-card-link" href="index.html">Página Inicial</a>
                        </div>
                    </div>
                    <div class="nav-card">
                        <div class="nav-card-label">Serviços</div>
                        <div class="nav-card-links">
                            <a class="nav-card-link" href="cadastro_produtos.html">Cadastro de Produtos</a>
                            <a class="nav-card-link" href="cadastro_distribuidoras.html">Cadastro de Distribuidor</a>
                            <a class="nav-card-link active-link" href="buscarproduto.php">Buscar meus produtos</a>
                            <a class="nav-card-link" href="buscar_distribuidor.html">Buscar meus distribuidores</a>
                        </div>
                    </div>
                    <a href="login.html" class="botao-login" aria-label="Ir para login">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <h1 class="titulo-pagina">BUSCAR PRODUTOS</h1>
    </div>

    <div class="search-container limitador">
        <form method="GET" action="buscarproduto.php" class="form-busca">
            <input type="text" name="busca" class="input-busca" placeholder="Digite o nome do produto (ex: Capacete, Sela)..." value="<?php echo htmlspecialchars($termo_busca); ?>" required>
            <button type="submit" class="btn-busca">Buscar</button>
        </form>
    </div>

    <section class="secao-produtos limitador">
        
        <?php if (!$buscou): ?>
            <div class="mensagem-aviso">
                <p>Utilize a barra acima para localizar seus produtos.</p>
            </div>

        <?php elseif (empty($resultados)): ?>
            <div class="mensagem-aviso">
                <p>Nenhum produto encontrado para "<strong><?php echo htmlspecialchars($termo_busca); ?></strong>".</p>
            </div>

        <?php else: ?>
            <div class="produtos-grid">
                <?php foreach ($resultados as $prod): ?>
                    <div class="produto-item">
                        <div class="prod-img-box">
                            <img src="<?php echo $prod['img']; ?>" alt="<?php echo $prod['nome']; ?>">
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-nome"><?php echo $prod['nome']; ?></h3>
                            <span class="prod-preco"><?php echo $prod['preco']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </section>

    <div class="rodape">
        <div class="limitador rodape-content">
            <ul class="sobre_nos">
                <strong>Sobre nós</strong>
                <a href="index.html">ALBOZ</a><br>
                <a href="informacoes_corporativas.html">Informações Corporativas</a><br>
                <a href="acessibilidade.html">Acessibilidade</a><br>
                <a href="suporte.html">Suporte</a><br>
            </ul>
            <ul class="paginas">
                <strong>Páginas</strong>
                <a href="login.html">Login/Cadastro</a><br>
                <a href="cadastro_produtos.html">Cadastro de produtos</a><br>
                <a href="buscarproduto.php">Meus Produtos</a><br>
                <a href="meus_distribuidores.html">Meus distribuidores</a><br>
            </ul>
            <ul class="contato">
                <strong>Contato</strong>
                <li>(11) 9999-9999</li>
                <li>contato@alboz.com</li>
            </ul>
        </div>
    </div>
</body>
</html>