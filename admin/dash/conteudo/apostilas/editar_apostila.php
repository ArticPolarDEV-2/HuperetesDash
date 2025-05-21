<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    LICENÇA MIT - RESPEITE A LICENÇA!
    Script para DELETAR apostila (PDF + thumbnail) do banco e do Cloudflare R2.
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

$DocFolder = $_SERVER['DOCUMENT_ROOT'];
if (!file_exists($DocFolder . "/databases/mainDbConn.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo de conexão não encontrado.']);
    exit;
}
require_once $DocFolder . "/databases/mainDbConn.php";

if (!file_exists($DocFolder . "/vendor/autoload.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo Autoload do Composer não encontrado.']);
    exit;
}
require $DocFolder . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /admin/dash/conteudo/apostilas/");
    exit();
}

// Dados do formulário
$id         = $_POST['id'] ?? '';
$newTitle   = $_POST['titulo'] ?? '';
$newObjKey  = $_POST['objKey'] ?? '';
$newThumbKey= $_POST['thumbKey'] ?? '';

if (empty($id) || empty($newTitle) || empty($newObjKey) || empty($newThumbKey)) {
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Todos os campos são obrigatórios."));
    exit();
}

// Conexão
$dbconn = new DataDatabaseConnection();
$db = $dbconn->getConnection();

// Configurações do bucket
$config = $db->query("SELECT * FROM configs LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro ao obter configuração do storage."));
    exit();
}

$bucketName    = $config['docsBucket'];
$region        = $config['region'];
$acessKey      = $config['acessKey'];
$secretKey     = $config['secretKey'];
$endpoint      = $config['endpoint'];
$publicUrlBase = $config['publicUrlBase'];

// Cliente S3
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => $region,
    'credentials' => [
        'key'    => $acessKey,
        'secret' => $secretKey,
    ],
    'endpoint' => $endpoint,
    'use_path_style_endpoint' => true,
]);

try {
    // Buscar info da apostila atual
    $stmt = $db->prepare("SELECT objKey, thumbKey FROM apostilas WHERE id = ?");
    $stmt->execute([$id]);
    $apostila = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$apostila) {
        header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Apostila não encontrada."));
        exit();
    }

    $oldObjKey   = $apostila['objKey'];
    $oldThumbKey = $apostila['thumbKey'];

    // Renomear no R2: copiar novo, apagar antigo
    if ($newObjKey !== $oldObjKey) {
        $s3Client->copyObject([
            'Bucket'     => $bucketName,
            'Key'        => $newObjKey,
            'CopySource' => "{$bucketName}/{$oldObjKey}",
        ]);
        $s3Client->deleteObject(['Bucket' => $bucketName, 'Key' => $oldObjKey]);
    }

    if ($newThumbKey !== $oldThumbKey) {
        $s3Client->copyObject([
            'Bucket'     => $bucketName,
            'Key'        => $newThumbKey,
            'CopySource' => "{$bucketName}/{$oldThumbKey}",
        ]);
        $s3Client->deleteObject(['Bucket' => $bucketName, 'Key' => $oldThumbKey]);
    }

    // URLs atualizadas
    $newPath = $publicUrlBase . '/' . $newObjKey;
    $newThumb = $publicUrlBase . '/' . $newThumbKey;

    // Atualizar no banco
    $stmt = $db->prepare("UPDATE apostilas SET titulo = ?, path = ?, thumbnail = ?, objKey = ?, thumbKey = ? WHERE id = ?");
    $stmt->execute([$newTitle, $newPath, $newThumb, $newObjKey, $newThumbKey, $id]);

    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Apostila atualizada com sucesso."));
    exit();

} catch (AwsException $e) {
    error_log("Erro AWS: " . $e->getAwsErrorMessage());
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro ao renomear objetos no R2."));
    exit();
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro inesperado."));
    exit();
} catch (PDOException $e) {
    error_log("Erro no banco de dados: " . $e->getMessage());
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro ao atualizar apostila."));
    exit();
}