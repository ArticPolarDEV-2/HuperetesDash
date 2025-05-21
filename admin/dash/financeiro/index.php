<?php
// Debug Code
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

session_start();
if (!isset($_SESSION["adm_id"])) {
    header("Location: /admin");
    exit();
}

// Incluir arquivo de conexão
$DocFolder = $_SERVER['DOCUMENT_ROOT'];
include_once($DocFolder . '/databases/mainDbConn.php');

$dbConn = new AreaPixDatabaseConnection();
$db = $dbConn->getConnection();

// Atualização de status do PIX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $transaction_id = (int)$_POST['transaction_id'];
    $status = $_POST['status'];
    $comment = $_POST['comment'] ?? null;
    $admin_id = (int)$_SESSION['adm_id'];
    
    try {
        $stmt = $db->prepare("UPDATE areapix.pix_transactions 
                             SET status = ?, 
                                 admin_comment = ?, 
                                 approved_by = ?, 
                                 approved_at = NOW() 
                             WHERE id = ?");
        $stmt->execute([$status, $comment, $admin_id, $transaction_id]);
        
        header("Location: /admin/dash/financeiro?success=1");
        exit();
    } catch (PDOException $e) {
        error_log("Erro ao atualizar PIX: " . $e->getMessage());
        header("Location: /admin/dash/financeiro?error=1");
        exit();
    }
}

// Consulta para listar transações
$query = "SELECT p.*, 
                 u.name as user_name,
                 u.email as user_email,
                 a.name as admin_name
          FROM areapix.pix_transactions p
          JOIN auth.users u ON p.user_id = u.id
          LEFT JOIN auth.admin a ON p.approved_by = a.id
          ORDER BY CASE 
              WHEN p.status = 'pending' THEN 1
              WHEN p.status = 'approved' THEN 2
              ELSE 3
          END, p.transaction_date DESC";

$transactions = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Financeiro | Painel dos Administradores</title>
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/dashboard/css/areapix.css">
    <link rel="stylesheet" href="/admin/dash/css/financeiro.css">
    <style>
        
    </style>
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
            <h1>Área do Financeiro</h1>
        </header>

        <main>
            <section class="dashboard-info">
                <div class="card">
                    <h4>Área de moderação de pagamentos PIX</h4>
                </div>
                
                <div class="pix-history">
                    <h2>Transações para Validação</h2>
                    <?php if (count($transactions) > 0): ?>
                        <table class="pix-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Usuário</th>
                                    <th>Valor</th>
                                    <th>Descrição</th>
                                    <th>Status</th>
                                    <th>Comprovante</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $row): ?>
                                    <?php
                                    $status_class = "status-" . $row["status"];
                                    switch ($row["status"]) {
                                        case "pending": $status_text = "Pendente"; break;
                                        case "approved": $status_text = "Aprovado"; break;
                                        case "rejected": $status_text = "Rejeitado"; break;
                                    }
                                    ?>
                                    <tr>
                                        <td><?= date("d/m/Y H:i", strtotime($row["transaction_date"])) ?></td>
                                        <td><?= htmlspecialchars($row["user_name"]) ?><br><small><?= $row["user_email"] ?></small></td>
                                        <td>R$ <?= number_format($row["amount"], 2, ",", ".") ?></td>
                                        <td><?= htmlspecialchars($row["description"]) ?></td>
                                        <td class="<?= $status_class ?>"><?= $status_text ?></td>
                                        <td><a href="#" onclick="showReceipt('<?= $row["receipt_image"] ?>')">Ver Comprovante</a></td>
                                        <td>
                                            <form method="post" class="status-form">
                                                <input type="hidden" name="transaction_id" value="<?= $row['id'] ?>">
                                                <select name="status" class="status-select" onchange="this.form.submit()">
                                                    <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pendente</option>
                                                    <option value="approved" <?= $row['status'] == 'approved' ? 'selected' : '' ?>>Aprovado</option>
                                                    <option value="rejected" <?= $row['status'] == 'rejected' ? 'selected' : '' ?>>Rejeitado</option>
                                                </select>
                                                <input type="text" name="comment" placeholder="Comentário (opcional)" 
                                                       value="<?= htmlspecialchars($row['admin_comment'] ?? '') ?>">
                                                <button type="submit" class="btn-save">Salvar</button>
                                            </form>
                                            <?php if ($row['approved_by']): ?>
                                                <div class="admin-notes">
                                                    Processado por: <?= $row['admin_name'] ?> em <?= date('d/m/Y H:i', strtotime($row['approved_at'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nenhuma transação PIX encontrada.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal para comprovante -->
    <div id="receiptModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('receiptModal').style.display='none'">&times;</span>
            <h3 class="modalTitle">Comprovante de Pagamento</h3>
            <div id="receiptContainer"></div>
        </div>
    </div>

    <script>
        function showReceipt(imageName) {
            document.getElementById('receiptContainer').innerHTML = 
                `<img src="/uploads/receipts/${imageName}" style="max-width:100%;">`;
            document.getElementById('receiptModal').style.display = 'block';
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            if (event.target == document.getElementById('receiptModal')) {
                document.getElementById('receiptModal').style.display = "none";
            }
        }
    </script>
</body>
</html>