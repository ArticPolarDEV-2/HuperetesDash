<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION["adm_id"])) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Not logged.']);
    exit;
}

$DocFolder = $_SERVER['DOCUMENT_ROOT'];

if (!file_exists($DocFolder . "/databases/mainDbConn.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo de conexão principal não encontrado.']);
    exit;
}

require_once $DocFolder . "/databases/mainDbConn.php";

// Verifica se a mensagem foi enviada via POST
if (isset($_POST['mensagem']) && !empty($_POST['mensagem'])) {
    $mensagem   = $_POST['mensagem'];
    $user_id    = $_SESSION['user_id'];

    try {
        // Conecta ao banco de dados
        $dbConn = new ChatDatabaseConnection();
        $db     = $dbConn->getConnection();

        // Prepara e executa a query para inserir a mensagem
        $userId   = $_SESSION["adm_id"];

        $stmt = $db->prepare("INSERT INTO chat_messages_adm (user_id, message, sent_in) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $mensagem]);


        // Retorna uma resposta de sucesso (opcional)
        ob_clean();
        echo json_encode(['status' => 'success', 'message' => 'Mensagem enviada com sucesso!']);
    } catch (Exception $e) {
        // Retorna uma resposta de erro
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    ob_clean();
    // Retorna uma resposta de erro se a mensagem estiver vazia
    echo json_encode(['status' => 'error', 'message' => 'A mensagem não pode estar vazia.']);
}
?>