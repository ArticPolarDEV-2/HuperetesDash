<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: /auth/login"); // Redireciona se não estiver logado
    exit();
}

$addbtn = null;
if (isset($_SESSION["adm"]) && $_SESSION["adm"]) {
    $addbtn = "
        <li><a href=\"/admin/dash\">🚪 Voltar para o Admin</a></li>
    ";
} else {
    $addbtn = "
        <li><a href=\"/dashboard/perfil\">👤 Perfil</a></li>
        <li><a href=\"/auth/logout\">🚪 Sair</a></li>
    ";
}

$avatarabsolutepath = "/assets/default-avatar.jpeg";
if (isset($_SESSION["user_avatar"])) {
    $avatarabsolutepath = "/uploads/avatars/" . $_SESSION["user_avatar"];
}

$err = "";
if (isset($_GET["err"]) && $_GET["err"] == 1) {
    $err = '<div class="error"><p class="errmsg">Não é permitido avatares maiores que 10Mb!</p></div>';
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | Portal do Aluno</title>
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/dashboard/css/profile.css">
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
            <h1>👤 Perfil</h1>
        </header>

        <main>
            <section class="profile-info">
                <div class="card">
                    <!-- Informações do Perfil -->
                    <h3>Informações do Usuário</h3>
                    <!-- <img src="/assets/default-avatar.jpeg" alt="Avatar do Usuário" class="profile-avatar"> -->
                    <img src="<?= $avatarabsolutepath ?>" alt="Avatar do Usuário" class="profile-avatar">
                    <div class="profile-details">
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($_SESSION["user_name"]); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION["user_email"]); ?></p>
                        <p><strong>Data de Cadastro:</strong> <?php echo date('d/m/Y H:i:s', strtotime($_SESSION["user_created_at"])); ?></p>
                    </div>
                </div>

                <!-- Editar Perfil -->
                <div class="card">
                    <h3>Editar Perfil</h3>
                    <form action="/dashboard/perfil/edit_profile.php" method="POST" enctype="multipart/form-data">
                        <?= $err ?>
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION["user_email"]); ?>" required>
                        
                        <label for="avatar">Alterar Avatar:</label>
                        <input type="file" id="avatar" name="avatar" accept=".png, .jpg, .jpeg">

                        <button type="submit">Salvar Alterações</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
    <script src="/libs/sidebar.js"></script>
</body>
</html>
