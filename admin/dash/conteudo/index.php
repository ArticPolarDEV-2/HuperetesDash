<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

session_start();
if (!isset($_SESSION["adm_id"])) {
    header("Location: /admin"); // Redireciona pra pagina de login dos administradores, se não estiver logado
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Conteúdos | Painel dos Administradores</title>
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
            <a href="/admin/dash" class="dashreturn">
                <img src="/assets/logo.png" alt="Huperetes Logo" class="logo">
                <h2>Painel dos Administradores</h2>
            </a>
        </center>
        <ul>
            <li><a href="/admin/dash/">🏠 Dashboard</a></li>
            <li><a href="/admin/dash/alunos">🧑‍🎓 Gerenciador de Alunos</a></li>
            <li><a href="/admin/dash/conteudo">📚 Gerenciador de Conteúdo</a></li>
            <li><a href="/admin/dash/chat">💬 Chat Admin</a></li>
            <li><a href="/admin/dash/loginaluno">🔑 Acessar painel dos alunos</a></li>
            <li><a href="/admin/dash/financeiro">💰 Financeiro</a></li>
            <li><a href="/admin/dash/perfil">👤 Perfil</a></li>
            <li><a href="/admin/logout">🚪 Sair</a></li>
        </ul>
    </div>

    <div class="content">
        <header>
            <h1>Gerenciador de Conteúdos</h1>
        </header>

        <main>
            <style>
                .btn-content {
                    background-color: #404040;
                    padding: 10px 20px;
                    color: var(--main-text-color-golden);
                    text-decoration: none;
                    border-radius: 8px;
                    transition: background-color 0.3s ease;
                }
                
                .btn-content:hover {
                    background-color: var(--main-text-color);
                    color: black;
                }
            </style>
            <section class="dashboard-info">
                <div class="card">
                    <h4>🙌 "Sirvam uns aos outros, cada um conforme o dom que recebeu... 📖 1 Pedro 4:10-11"</h4>
                </div>
                <div style="margin-top: 20px; display: flex; gap: 15px; justify-content: center;" class="card">
                    <a href="./apostilas" class="btn-content">📖 Apostilas</a>
                    <a href="./podcasts" class="btn-content">🎧 Podcasts</a>
                </div>
            </section>
        </main>
    </div>
    <script src="/libs/sidebar.js"></script>
</body>
</html>