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

require_once $DocFolder . "/dashboard/dbconn/database_connection_data.php";

try {
    $dbconn = new DataDatabaseConnection();
    $db = $dbconn->getConnection();

    // Consulta para obter as apostilas
    $stmt = $db->prepare("SELECT titulo, path, thumbnail FROM apostilas");
    $stmt->execute();
    $apostilas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apostilas | Portal do Aluno</title>
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
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
            <li><a href="/dashboard/cronogramas">📅 Cronogramas</a></li>
            <li><a href="/dashboard/chat">💬 Chat</a></li>
            <li><a href="/dashboard/areapix">❖ Área Pix</a></li>
            <?= $addbtn ?>
        </ul>
    </div>

    <div class="content">
        <header>
            <h1>📚 Apostilas</h1>
        </header>

        <main>
            <section class="dashboard-info">
                <?php if (!empty($apostilas)): ?>
                    <?php foreach ($apostilas as $apostila): ?>
                        <div class="card">
                            <a href="<?= "/dashboard/apostilas/viewer?filename=" . urlencode($apostila["path"]) . "&name=" . urlencode($apostila["titulo"]) ?>">
                                <h3><?= htmlspecialchars($apostila["titulo"]) ?></h3>
                                <img src="<?= htmlspecialchars($apostila["thumbnail"]) ?>" alt="<?= htmlspecialchars($apostila["titulo"]) ?>" width="300px">
                            </a>
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
