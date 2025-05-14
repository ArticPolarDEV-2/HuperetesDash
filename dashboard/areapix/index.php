<?php
// Notas de Desenvolvimento:
/*
    - Testar se o pix seja individual, ou seja, somente qm fez o pagamento e os adms veja, sem os outros alunos verem.
    - Fazer o painel de validamento de Pix, onde o adm pode ver os pagamentos pendentes e aprovar ou rejeitar.
    - Fazer o painel de histórico de pagamentos, onde o aluno pode ver os pagamentos que ele fez e o status deles.
    - Fazer o painel de upload de comprovante, onde o aluno pode fazer upload do comprovante de pagamento e o adm pode ver.
    - IMPORTANTE: ADICIONAR QR CODE E CHAVE PIX NA PAGINA DE PAGAMENTO, PARA O ALUNO ESCANEAR E PAGAR. (QR CODE E CHAVE NO ZAP DA PASTORA KIKA)
*/

/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /auth/login"); // Redireciona se nenhum dos dois estiver definido
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Pix | Portal do Aluno</title>
    <link rel="stylesheet" href="/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/css/fontawesome.all.css">
    <link rel="stylesheet" href="/dashboard/css/areapix.css">
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
            <li><a href="/dashboard/perfil">👤 Perfil</a></li>
            <li><a href="/auth/logout">🚪 Sair</a></li>
        </ul>
    </div>

    <div class="content">
        <header>
            <h1>❖ Área Pix</h1>
        </header>

        <main>
            <section class="dashboard-info">
                <div class="card">
                    <h4>🙌 "Porque nós não pregamos a nós mesmos, mas a Cristo Jesus, o Senhor, e a nós mesmos como vossos servos por amor de Jesus." 📖 2 Coríntios 4:5</h4>
                </div>
            </section>
            
            <section class="pix-container">
                <div class="pix-form">
                    <h2>Registrar Novo Pagamento Pix</h2>
                    <form action="/dashboard/areapix/process.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="amount">Valor (R$):</label>
                            <input type="number" id="amount" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Descrição/Referência:</label>
                            <input type="text" id="description" name="description" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="button" id="generate-pix" class="btn-submit">Gerar Chave Pix</button>
                        </div>

                        <!-- Resultado da Geração da Chave PIX -->
                        <div id="pix-result" style="display: none; margin-top: 20px;">
                            <p style="position: relative; padding-right: 40px;">
                                <strong>Chave Pix Copia e Cola:</strong> 
                                <span id="codepix"></span>
                                <button  type="button" id="copy-btn" onclick="copiarPix()" title="Copiar Pix">
                                    <i class="fa-light fa-copy"></i>
                                </button>
                            </p>
                            <div class="qrcode-container">
                                <div class="qrcode-subcontainer">
                                    <img id="qrcode" src="" alt="QR Code do Pix" style="max-width: 200px;">
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="receipt">Comprovante (Imagem da tela ou recibo):</label>
                                <input type="file" id="receipt" name="receipt" class="form-control" accept="image/*" required>
                                <small>Faça upload de uma imagem do comprovante de pagamento ou print da tela.</small>
                            </div>
                            <br>
                            <div class="form-group">
                                <button type="submit" class="btn-submit">Enviar para Validação</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="pix-history">
                    <h2>Histórico de Pagamentos</h2>
                    <?php
                    // Incluir arquivo de conexão com o banco de dados
                    include_once $_SERVER["DOCUMENT_ROOT"] . "/databases/mainDbConn.php";

                    $dbConn = new AreaPixDatabaseConnection();
                    $db = $dbConn->getConnection();

                    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_SESSION["adm_id"];

                    // Buscar transações do usuário
                    $query = "SELECT * FROM pix_transactions WHERE user_id = ? ORDER BY transaction_date DESC";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$user_id]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($result) > 0) {
                        echo '<table class="pix-table">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Valor</th>
                                        <th>Descrição</th>
                                        <th>Status</th>
                                        <th>Comprovante</th>
                                    </tr>
                                </thead>
                                <tbody>';

                        foreach ($result as $row) {
                            $status_class = "";
                            $status_text = "";

                            switch ($row["status"]) {
                                case "pending":
                                    $status_class = "status-pending";
                                    $status_text = "Pendente";
                                    break;
                                case "approved":
                                    $status_class = "status-approved";
                                    $status_text = "Aprovado";
                                    break;
                                case "rejected":
                                    $status_class = "status-rejected";
                                    $status_text = "Rejeitado";
                                    break;
                            }

                            echo '<tr>
                                    <td>' . date("d/m/Y", strtotime($row["transaction_date"])) . '</td>
                                    <td>R$ ' . number_format($row["amount"], 2, ",", ".") . '</td>
                                    <td>' . htmlspecialchars($row["description"]) . '</td>
                                    <td class="' . $status_class . '">' . $status_text . '</td>
                                    <td><a href="/uploads/receipts/' . $row["receipt_image"] . '" target="_blank">Ver Comprovante</a></td>
                                  </tr>';
                        }

                        echo '</tbody></table>';
                    } else {
                        echo '<p>Nenhuma transação Pix registrada ainda.</p>';
                    }

                    // Fechar a conexão
                    $stmt = null;
                    $db = null;
                    ?>
                </div>
            </section>
        </main>
    </div>
    <script src="/libs/pix.js"></script>
    <script src="/libs/sidebar.js"></script>
</body>
</html>
