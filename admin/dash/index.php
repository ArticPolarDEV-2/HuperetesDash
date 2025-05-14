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
    <title>Painel dos Administradores</title>
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
            <h1>Bem-vindo, administrador <?= $_SESSION["adm_name"] ?>!</h1>
        </header>

        <main>
            <section class="dashboard-info">
                <div class="card">
                    <h4>🙌 "Sirvam uns aos outros, cada um conforme o dom que recebeu, como encarregados de administrar bem a multiforme graça de Deus. Se alguém fala, fale de acordo com os oráculos de Deus; se alguém serve, faça-o na força que Deus lhe dá, para que, em todas as coisas, Deus seja glorificado, por meio de Jesus Cristo, a quem pertence a glória e o domínio para todo o sempre. Amém!" 📖 1 Pedro 4:10-11</h4>
                </div>
            </section>
        </main>
    </div>
    <script src="/libs/sidebar.js"></script>
</body>
</html>