<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

// Debug Code
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
*/

session_start();
if (!isset($_SESSION["adm_id"])) {
    header("Location: /admin");
    exit();
}

// Document root of server
$DocFolder = $_SERVER['DOCUMENT_ROOT'];

// Include principal database connection
require_once $DocFolder . "/databases/mainDbConn.php";

// AWS SDK Autoloader
require 'aws-sdk-php/aws-autoloader.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Verifica se título está presente
if (!isset($_POST['titulo']) || empty(trim($_POST['titulo']))) {
    header('Location: /admin/dash/conteudo/podcasts/?msg=' . urlencode("Título é obrigatório."));
    exit();
}

$titulo = trim($_POST['titulo']);

// Verifica AUDIO
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: /admin/dash/conteudo/podcasts/?msg=' . urlencode("Erro no upload do Audio."));
    exit();
}

// AWS S3 Configuração
$bucketName = 'huperetes-podcasts';
$region = 'us-east-1';
$acessKey = 'SUA_ACCESS_KEY';
$secretKey = 'SUA_SECRET_KEY';
$endpoint = 'https://<account_id>.r2.cloudflarestorage.com';

$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => $region,
    'credentials' => [
        'key' => $acessKey,
        'secret' => $secretKey,
    ],
    'endpoint' => $endpoint,
    'use_path_style_endpoint' => true,
]);

try {
    // === Upload Audio ===
    $tmpFile = $_FILES['file']['tmp_name'];
    $originalName = basename($_FILES['file']['name']);
    $fileMime = mime_content_type($tmpFile);
    $fileName = uniqid() . '-' . $originalName;
    $objectKey = "uploads/" . $fileName;

    $resultAudio = $s3Client->putObject([
        'Bucket' => $bucketName,
        'Key' => $objectKey,
        'SourceFile' => $tmpFile,
        'ACL' => 'public-read',
        'ContentType' => $fileMime,
        'Metadata' => [
            'original_name' => $originalName,
            'uploaded_by' => $_SESSION['adm_id'],
        ],
    ]);
    $audioUrl = $resultAudio['ObjectURL'];

    // === Inserir no banco ===
    $dbconn = new DataDatabaseConnection();
    $db = $dbconn->getConnection();

    $stmt = $db->prepare("INSERT INTO podcasts (titulo, url) VALUES (?, ?)");
    $stmt->execute([$titulo, $audioUrl]);

    // Redireciona com sucesso
    header('Location: /admin/dash/conteudo/podcasts/?msg=' . urlencode("Arquivo enviado com sucesso!"));
} catch (AwsException $e) {
    header('Location: /admin/dash/conteudo/podcasts/?msg=' . urlencode("Erro ao enviar o arquivo: " . htmlspecialchars($e->getMessage())));
} catch (PDOException $e) {
    header('Location: /admin/dash/conteudo/podcasts/?msg=' . urlencode("Erro ao salvar no banco: " . htmlspecialchars($e->getMessage())));
} catch (Exception $e) {
    header('Location: /admin/dash/conteudo/podcasts/?msg=' . urlencode("Erro inesperado: " . htmlspecialchars($e->getMessage())));
}

// Limpa arquivos temporários
unlink($tmpFile);
exit();