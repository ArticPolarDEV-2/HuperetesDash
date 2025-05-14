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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Aluno</title>
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
            <li><a href="/dashboard/conogramas">📅 Cronogramas</a></li>
            <li><a href="/dashboard/chat">💬 Chat</a></li>
            <li><a href="/dashboard/areapix">❖ Área Pix</a></li>
            <?= $addbtn ?>
            <!-- <li><a href="/dashboard/perfil">👤 Perfil</a></li>
            <li><a href="/auth/logout">🚪 Sair</a></li> -->
        </ul>
    </div>

    <div class="content">
        <header>
            
            <h1>Bem-vindo, <?= $_SESSION["adm_name"] ?? $_SESSION["user_name"] ?>!</h1>
        </header>

        <main>
            <section class="dashboard-info">
                <!-- <div class="card">
                    <h3>📖 Módulos</h3>
                    <p>6 Módulos restantes</p>
                </div>
                <div class="card">
                    <h3>📈 Progresso</h3>
                    <p>Conclusão: 10%</p>
                </div>
                <div class="card">
                    <h3>📅 Próximas Aulas</h3>
                    <p>Quinta-feira às 19:30h: Sabedoria Financeira</p>
                </div> -->
                <div class="card">
                    <h4>🙌 "Porque nós não pregamos a nós mesmos, mas a Cristo Jesus, o Senhor, e a nós mesmos como vossos servos por amor de Jesus." 📖 2 Coríntios 4:5</h4>
                </div>
            </section>
        </main>
    </div>
    <script src="/libs/sidebar.js"></script>
</body>
</html>
