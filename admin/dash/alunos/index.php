<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

// Debug Code
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$DocFolder = $_SERVER['DOCUMENT_ROOT'];
require_once $DocFolder . "/databases/mainDbConn.php";

if (!isset($_SESSION["adm_id"])) {
    header("Location: /admin"); 
    exit();
}

// Obtém todos os alunos
try {
    $dbConn = new AuthDatabaseConnection();
    $db     = $dbConn->getConnection();
    $search = $_GET['search'] ?? '';
    if (!empty($search)) {
        $stmt = $db->prepare("SELECT * FROM users WHERE name LIKE :search ORDER BY created_at DESC");
        $stmt->execute([':search' => "%$search%"]);
    } else {
        $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
    }
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Erro de conexão com o Database.";
}

$messagebox = null;
if (isset($_GET['msg'])) {
    $messagebox = "
        <div class=\"processbox\">
            <button class=\"close-btn\" onclick=\"removeMsgParam()\">&times;</button>
            <h4>" . htmlspecialchars($_GET["msg"]) . "</h4>
        </div>
        <script>
            function removeMsgParam() {
                const url = new URL(window.location.href);
                url.searchParams.delete('msg');
                window.location.href = url.toString();
            }
        </script>
    ";
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Alunos | Painel dos Administradores</title>
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/css/fontawesome.all.css">
    <link rel="stylesheet" href="/admin/dash/alunos/css/alunos.css">
</head>
<body>
    <center>
        <button id="menu-toggle" class="menu-button">
            <i class="fa-regular fa-dash"></i>
        </button>
    </center>

    <div class="sidebar">
        <center>
            <a href="/admin/dash/" class="dashreturn">
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
            <h1>🧑‍🎓 Gerenciador de Alunos</h1>
        </header>

        <main>
            <!-- Adicionar a caixa de mensagem/output do backend quando o parametro da url msg (GET) estiver presente e definida  -->
            <?= $messagebox ?>

            <!-- Formulário para adicionar aluno -->
            <form action="add_aluno.php" method="POST">
                <input type="text" name="nome" placeholder="Nome" required>
                <input type="email" name="email" placeholder="E-mail" required>
                <input type="password" name="senha" placeholder="Senha Padrão" required>
                <button type="submit">Adicionar</button>
            </form>

            <!-- Formulário para importar alunos XLSX -->
            <form action="import_xlsx.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="arquivo" accept=".xlsx" required>
                <button type="submit">Importar XLSX</button>
            </form>

            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Buscar por nome..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit">🔍 Buscar</button>
            </form>

            <!-- Lista de alunos -->
            <div class="table-responsive">
                <table border="1">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                    <?php foreach ($alunos as $aluno): ?>
                    <tr>
                        <td><?= $aluno['id'] ?></td>
                        <td><?= htmlspecialchars($aluno['name']) ?></td>
                        <td><?= htmlspecialchars($aluno['email']) ?></td>
                        <td><?= $aluno['created_at'] ?></td>
                        <td>
                            <a href="edit_aluno.php?id=<?= $aluno['id'] ?>">✏️ Editar</a>
                            <a href="delete_aluno.php?id=<?= $aluno['id'] ?>" onclick="return confirm('Tem certeza?')">🗑 Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </main>
    </div>
    <script src="/libs/sidebar.js"></script>
</body>
</html>
