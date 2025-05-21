<?php
session_start();

$DocFolder = $_SERVER['DOCUMENT_ROOT'];
if (!file_exists($DocFolder . "/databases/mainDbConn.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo de conexão não encontrado.']);
    exit;
}
require_once $DocFolder . "/databases/mainDbConn.php";

if (!isset($_SESSION["adm_id"])) {
    header("Location: /admin");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome  = $_POST["nome"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT); // Senha criptografada

    try {
        $dbConn = new AuthDatabaseConnection();
        $db     = $dbConn->getConnection();
        
        $query = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $query->execute([$nome, $email, $senha]);

        header("Location: /admin/dash/alunos?msg=Aluno adicionado com sucesso");
    } catch (Exception $e) {
        die("Erro ao adicionar aluno: " . $e->getMessage());
    }
}
?>
