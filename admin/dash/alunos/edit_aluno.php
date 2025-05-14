<?php
session_start();

$DocFolder = $_SERVER['DOCUMENT_ROOT'];
require_once $DocFolder . "/databases/mainDbConn.php";

if (!isset($_SESSION["adm_id"])) {
    header("Location: /admin");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id    = $_POST["id"];
    $nome  = $_POST["nome"];
    $email = $_POST["email"];

    try {
        $dbConn = new AuthDatabaseConnection();
        $db     = $dbConn->getConnection();

        $query = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $query->execute([$nome, $email, $id]);

        header("Location: /admin/dash/alunos?msg=Aluno atualizado com sucesso");
    } catch (Exception $e) {
        die("Erro ao editar aluno: " . $e->getMessage());
    }
}

// Obtém os dados do aluno para exibição no formulário
if (isset($_GET["id"])) {
    $id = $_GET["id"];

    try {
        $dbConn = new AuthDatabaseConnection();
        $db     = $dbConn->getConnection();

        $query = $db->prepare("SELECT * FROM users WHERE id = ?");
        $query->execute([$id]);
        $aluno = $query->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        die("Erro ao buscar aluno: " . $e->getMessage());
    }
} else {
    die("ID do aluno não informado.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aluno</title>
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/css/fontawesome.all.css">
    <link rel="stylesheet" href="/admin/dash/alunos/css/alunos.css">
    <link rel="stylesheet" href="/admin/dash/alunos/css/edit.css">
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
            <li><a href="/admin/dash">🏠 Dashboard</a></li>
            <li><a href="/admin/dash/alunos">🧑‍🎓 Gerenciador de Alunos</a></li>
            <li><a href="/admin/dash/conteudo">📚 Gerenciador de Conteúdo</a></li>
            <li><a href="/admin/dash/chat">💬 Chat Admin</a></li>
            <li><a href="/admin/dash/loginaluno">🔑 Acessar painel dos alunos</a></li>
            <li><a href="/admin/dash/perfil">👤 Perfil</a></li>
            <li><a href="/admin/logout">🚪 Sair</a></li>
        </ul>
    </div>

    <div class="content">
        <header>
            <h1>🧑‍🎓 Editor de Alunos</h1>
        </header>

        <main>
            <form action="edit_aluno.php" method="POST">
                <a href="/admin/dash/alunos" class="return">&times;</a>
                <input type="hidden" name="id" value="<?= $aluno['id'] ?>">
                <label>Nome:</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($aluno['name']) ?>" required>
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($aluno['email']) ?>" required>
                <button type="submit">Atualizar</button>
            </form>
            
        </main>
    </div>    
    
    <script src="/libs/sidebar.js"></script>
</body>
</html>
