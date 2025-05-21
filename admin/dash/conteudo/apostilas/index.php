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

$messagebox = null;
if (isset($_GET['msg'])) {
    $messagebox = "
        <div class=\"processbox\">
            <button class=\"close-btn\" onclick=\"removeMsgParam()\">&times;</button>
            <h4>" . htmlspecialchars(urldecode($_GET["msg"])) . "</h4>
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
    <title>Gerenciador de Apostilas | Painel dos Administradores</title>
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/css/fontawesome.all.css">
    <link rel="stylesheet" href="/admin/dash/css/apostilasadm.css">
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
            <h1>Gerenciador de Apostilas</h1>
        </header>

        <main>
            <!-- Adicionar a caixa de mensagem/output do backend quando o parametro da url msg (GET) estiver presente e definida  -->
            <?= $messagebox ?>
        <?php
        // Conexão com banco
        require_once $_SERVER["DOCUMENT_ROOT"] .
            "/dashboard/dbconn/database_connection_data.php";

        try {
            $dbconn = new DataDatabaseConnection();
            $db = $dbconn->getConnection();
            $stmt = $db->prepare("SELECT * FROM apostilas");
            $stmt->execute();
            $apostilas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
        ?>

        <div class="tabs">
            <button class="tab-button active" onclick="window.location.href = '#ver'; setTimeout(() => location.reload(), 50);">📚 Apostilas Registradas</button>
            <button class="tab-button" onclick="window.location.href = '#nova'; setTimeout(() => location.reload(), 50);">➕ Nova Apostila</button>
        </div>


        <!-- TAB 1: VER APOSTILAS -->
        <div id="ver" class="tab-content active">
            <h2 class="subHeaderTxt">Apostilas Registradas</h2>
            <?php if (count($apostilas) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>PDF</th>
                            <th>Thumbnail</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apostilas as $apostila): ?>
                            <tr>
                                <td><?= $apostila["id"] ?></td>
                                <td><?= htmlspecialchars($apostila["titulo"]) ?></td>
                                <td><a href="<?= $apostila[
                                    "path"
                                ] ?>" target="_blank">📄 Ver PDF</a></td>
                                <td><img src="<?= $apostila[
                                    "thumbnail"
                                ] ?>" width="80"></td>
                                <td>
                                    <a href="/admin/dash/conteudo/apostilas/deletar_apostila.php?id=<?= $apostila[
                                        "id"
                                    ] ?>" onclick="return confirm('Tem certeza que deseja excluir esta apostila?')">❌ Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: var(--main-text-color-golden);">Nenhuma apostila registrada ainda.</p>
            <?php endif; ?>
        </div>

        <!-- TAB 2: NOVA APOSTILA -->
        <div id="nova" class="tab-content">
            <h2 class="subHeaderTxt">Registrar Nova Apostila</h2>
            <div class="form-container">
                <form action="/admin/dash/conteudo/apostilas/uploadtopdfbucket.php" method="POST" enctype="multipart/form-data">
                    <label for="titulo">Título da Apostila:</label><br>
                    <input type="text" name="titulo" required><br><br>
    
                    <label for="thumbnail">Thumbnail (Imagem):</label><br>
                    <input type="file" name="thumbnail" accept="image/*" required><br><br>
    
                    <label for="file">Arquivo PDF:</label><br>
                    <input type="file" name="file" accept="application/pdf" required><br><br>
    
                    <button type="submit">📤 Enviar e Registrar</button>
                </form>
            </div>
        </div>
        </main>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
        const hash = window.location.hash;

        if (hash === "#ver") {
            showTab("ver");
        } else if (hash === "#nova") {
            showTab("nova");
        }

        // Também trata mudança dinâmica da hash
        window.addEventListener("hashchange", () => {
            const newHash = window.location.hash;
            if (newHash === "#apostilas") {
                showTab("ver");
            } else if (newHash === "#nova") {
                showTab("nova");
            }
        });
    });

    function showTab(tabId, button = null) {
    const buttons = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');

    buttons.forEach(btn => btn.classList.remove('active'));
    contents.forEach(tab => tab.classList.remove('active'));

    document.querySelector(`#${tabId}`).classList.add('active');

    if (button) {
        button.classList.add('active');
    } else {
        // fallback: destaca com base no ID
        if (tabId === 'ver') {
            buttons[0].classList.add('active');
        } else if (tabId === 'nova') {
            buttons[1].classList.add('active');
        }
    }
}
    </script>
    <script src="/libs/sidebar.js"></script>
</body>
</html>