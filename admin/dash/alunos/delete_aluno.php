<?php
session_start();

$DocFolder = $_SERVER['DOCUMENT_ROOT'];
require_once $DocFolder . "/databases/mainDbConn.php";

if (!isset($_SESSION["adm_id"])) {
    header("Location: /admin");
    exit();
}

if (isset($_GET["id"])) {
    $id = $_GET["id"];

    try {
        $dbConn = new AuthDatabaseConnection();
        $db     = $dbConn->getConnection();

        $query = $db->prepare("DELETE FROM users WHERE id = ?");
        $query->execute([$id]);

        header("Location: /admin/dash/alunos?msg=Aluno excluído com sucesso");
    } catch (Exception $e) {
        die("Erro ao excluir aluno: " . $e->getMessage());
    }
} else {
    die("ID do aluno não informado.");
}
?>
