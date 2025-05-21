<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION["adm_id"])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged.']);
    exit;
}

$DocFolder = $_SERVER['DOCUMENT_ROOT'];
if (!file_exists($DocFolder . "/databases/mainDbConn.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo de conexão não encontrado.']);
    exit;
}
require_once $DocFolder . "/databases/mainDbConn.php";

try {
    // Conecta ao banco de dados de mensagens (chat)
    $dbConn = new ChatDatabaseConnection();
    $db     = $dbConn->getConnection();

    // Conecta ao banco de dados de login (auth)
    $loginDbConn = new AuthDatabaseConnection();
    $loginDb     = $loginDbConn->getConnection();

    // Busca as mensagens, junto com os nomes dos usuários
    $query = $db->query("
        SELECT cm.*, u.name 
                         FROM chat.chat_messages_adm cm 
                         JOIN auth.admin u ON cm.user_id = u.id 
                         ORDER BY cm.sent_in ASC
    ");

    if ($query === false) {
        throw new Exception("Erro ao executar a query: " . implode(", ", $db->errorInfo()));
    }

    $mensagens = $query->fetchAll(PDO::FETCH_ASSOC);

    // Retorna as mensagens em formato JSON
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'messages' => $mensagens]);
} catch (Exception $e) {
    // Retorna uma resposta de erro em JSON
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>