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

// Variaveis q sera guardado os dados do documento PDF, setado como NULL, pois os dados serão definidos posteriormente no IF ...
$nomedoconteudo = null;
$urldodoc = null;

$err = null;
if (isset($_GET["filename"]) && isset($_GET["name"])) {
    // Logica para puxar o pdf (PROVAVELMENTE DO CLOUDFLARE R2), e carregar no leitor de pdf nativo no BROWSER
    $nomedoconteudo = $_GET["name"];
    $urldodoc = $_GET["filename"];
} else {
    $nomedoconteudo = "ERR";
    $urldodoc = "ERR";
    $err = "O arquivo de PDF ou o nome não foi espeficado nos parâmentros da URL!";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leitor de Apostilas | Portal do Aluno</title>
    <link rel="stylesheet" href="/css/fontawesome.all.css">
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/dashboard/css/pdfviewer.css">
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
            <h1>📚 Apostila - <?= htmlspecialchars($nomedoconteudo) ?></h1>
            <a href="/dashboard/apostilas/" class="returnBtn"><i class="fa-solid fa-turn-left"></i></a>
        </header>

        <main>
            <section class="dashboard-info">
                <div class="card">
                    <object
                        data="<?= htmlspecialchars($urldodoc == "ERR" ? " " : $urldodoc) ?>"
                        type="application/pdf"
                        width="100%"
                        height="760px">
                        <p>Seu navegador não suporta a exibição de PDFs. <a href="<?= htmlspecialchars($urldodoc == "ERR" ? "#" : $urldodoc) ?>">Clique aqui para baixar o PDF.</a></p>
                    </object>
                </div>
            </section>
        </main>
    </div>
    <script src="/libs/sidebar.js"></script>
</body>
</html>
