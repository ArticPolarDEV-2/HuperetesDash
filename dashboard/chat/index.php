<?php
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | Portal do Aluno</title>
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/dashboard/css/chat.css">
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
            <h1>💬 Chat</h1>
        </header>

        <main>
            <section class="dashboard-info">
                <div class="card">
                    <h5>🙌 "Tratem todos com honra, amem os irmãos na fé, temam a Deus e honrem o rei. Servos, sejam obedientes ao senhor de vocês, com todo o temor. E não somente se ele for bom e cordial, mas também se for mau. Porque isto é agradável a Deus, que alguém suporte tristezas, sofrendo injustamente, por motivo de sua consciência para com Deus." 📖 1Pedro 2:17-19</h5>
                </div>
                <div id="chat"></div>
                <!-- <input type="text" id="mensagem" placeholder="Digite sua mensagem..."> -->
                <textarea id="mensagem" placeholder="Digite sua mensagem..." maxlength="2000"></textarea>
                <button onclick="enviarMensagem()" class="send">Enviar</button>
            </section>
        </main>
    </div>

    <script src="/libs/jquery-3.6.0.min.js"></script>
    <script src="/libs/chat.js"></script>
    <script src="/libs/sidebar.js"></script>
</body>
</html>