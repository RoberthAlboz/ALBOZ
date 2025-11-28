<?php
// Mantendo sua proteção (certifique-se que o arquivo existe)
include('protecao.php');

// ==========================================================
// 1. BANCO DE DADOS SIMULADO (MOCK DATA)
// ==========================================================
// Aqui simulamos os produtos que estariam no seu MySQL.
// Quando você conectar no banco real, vai substituir isso pela Query SQL.
$todos_produtos = [
    [
        'nome' => 'NICO ROSBERG 2015 - BELL',
        'preco' => 'R$ 12.350,00',
        'img' => 'https://via.placeholder.com/400x500/808080/FFFFFF?text=Capacete'
    ],
    [
        'nome' => 'FLORETE - FOLO',
        'preco' => 'R$ 1.600,00',
        'img' => 'https://via.placeholder.com/400x500/909090/FFFFFF?text=Florete'
    ],
    [
        'nome' => 'SELA AMERICANA - EQUITECH',
        'preco' => 'R$ 20.400,00',
        'img' => 'https://via.placeholder.com/400x500/707070/FFFFFF?text=Sela'
    ],
    [
        'nome' => 'PRANCHA WING FOIL - NSP',
        'preco' => 'R$ 16.500,00',
        'img' => 'https://via.placeholder.com/400x500/A0A0A0/FFFFFF?text=Prancha'
    ],
    [
        'nome' => 'EQUIPAMENTO TÁTICO X5',
        'preco' => 'R$ 5.200,00',
        'img' => 'https://via.placeholder.com/400x500/858585/FFFFFF?text=Tatico'
    ],
    [
        'nome' => 'CONJUNTO DE ALTA PERFORMANCE',
        'preco' => 'R$ 8.990,00',
        'img' => 'https://via.placeholder.com/400x500/959595/FFFFFF?text=Performance'
    ]
];

// ==========================================================
// 2. LÓGICA DE BUSCA
// ==========================================================
$resultados = [];
$termo_busca = "";
$buscou = false; // Variável para saber se o usuário clicou em buscar

if (isset($_GET['busca'])) {
    $termo_busca = $_GET['busca'];
    $buscou = true;

    // Filtra o array (Simulando um "SELECT * FROM produtos WHERE nome LIKE...")
    foreach ($todos_produtos as $produto) {
        // stripos verifica se o termo existe no nome (ignorando maiúsculas/minúsculas)
        if (stripos($produto['nome'], $termo_busca) !== false) {
            $resultados[] = $produto;
        }
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