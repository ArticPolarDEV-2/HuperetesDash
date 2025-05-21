<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

session_start();
if (!isset($_SESSION["user_id"]) && !isset($_SESSION["adm_id"])) {
    header("Location: /auth/login"); // Redireciona se nenhum dos dois estiver definido
    exit();
}

$addbtn = null;
if ($_SESSION["adm"]) {
    $addbtn = "
        <li><a href=\"/admin/dash\">🚪 Voltar para o Admin</a></li>
    ";
} else {
    $addbtn = "
        <li><a href=\"/dashboard/perfil\">👤 Perfil</a></li>
        <li><a href=\"/auth/logout\">🚪 Sair</a></li>
    ";
}

$DocFolder = $_SERVER['DOCUMENT_ROOT'];

if (!file_exists($DocFolder . "/databases/mainDbConn.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo de conexão não encontrado.']);
    exit;
}

require_once $DocFolder . "/databases/mainDbConn.php";

try {
    $dbconn = new DataDatabaseConnection();
    $db = $dbconn->getConnection();

    // Consulta para obter as apostilas
    $stmt = $db->prepare("SELECT titulo, url FROM podcasts");
    $stmt->execute();
    $podcasts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podcasts | Portal do Aluno</title>
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/dashboard/css/podcasts.css">
    <link rel="stylesheet" href="/css/fontawesome.all.css">
</head>
<body>
    <center>
        <button id="menu-toggle" class="menu-button">
            <i class="fa-regular fa-dash"></i>
        </button>
    </center>

    <div class="sidebar">
        <center>
            <a href="/dashboard" class="dashreturn">
                <img src="/assets/logo.png" alt="Huperetes Logo" class="logo">
                <h2>Portal do Aluno</h2>
            </a>
        </center>
        <ul>
            <li><a href="/dashboard/">🏠 Dashboard</a></li>
            <li><a href="/dashboard/apostilas">📚 Apostilas</a></li>
            <li><a href="/dashboard/podcasts">🎤 Podcasts</a></li>
            <li><a href="/dashboard/conogramas">📅 Conogramas</a></li>
            <li><a href="/dashboard/chat">💬 Chat</a></li>
            <li><a href="/dashboard/areapix">❖ Área Pix</a></li>
            <?= $addbtn ?>
        </ul>
    </div>

    <div class="content">
        <header>
            <h1>🎧 Podcasts Disponíveis</h1>
        </header>

        <main>
            <section class="podcast-list">
                <?php if (!empty($podcasts)): ?>
                    <?php foreach ($podcasts as $podcast): ?>
                        <div class="podcast-card">
                            <h3><?= htmlspecialchars($podcast["titulo"]) ?></h3>
                            <audio controls>
                                <source src="<?= htmlspecialchars($podcast["url"]) ?>" type="audio/mpeg">
                                Seu navegador não suporta o elemento de áudio.
                            </audio>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhuma apostila disponível no momento.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="/libs/sidebar.js"></script>
</body>
</html>