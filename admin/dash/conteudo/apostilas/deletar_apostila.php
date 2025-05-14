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

// Document root
$DocFolder = $_SERVER['DOCUMENT_ROOT'];

// DB connection
require_once $DocFolder . "/databases/mainDbConn.php";

// AWS SDK
require $DocFolder . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Verifica ID
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("ID da apostila é obrigatório."));
    exit();
}

$id = trim($_GET['id']);

// Iniciar conexão com o banco
$dbconn = new DataDatabaseConnection();
$db     = $dbconn->getConnection();

// Buscar configurações AWS
$sql    = "SELECT * FROM configs LIMIT 1";
$stmt   = $db->query($sql);
if (!$stmt || !$config = $stmt->fetch(PDO::FETCH_ASSOC)) {
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro ao obter configuração do storage."));
    exit();
}

$bucketName     = $config['docsBucket'];
$region         = $config['region'];
$acessKey       = $config['acessKey'];
$secretKey      = $config['secretKey'];
$endpoint       = $config['endpoint'];

// Criar client S3
$s3Client = new S3Client([
    'version'                   => 'latest',
    'region'                    => $region,
    'credentials'               => [
        'key'                   => $acessKey,
        'secret'                => $secretKey,
    ],
    'endpoint'                  => $endpoint,
    'use_path_style_endpoint'   => true,
]);

try {
    // Buscar a apostila pelo ID (agora com as chaves reais dos objetos)
    $stmt = $db->prepare("SELECT objKey, thumbKey FROM apostilas WHERE id = ?");
    $stmt->execute([$id]);
    $apostila = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$apostila) {
        header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Apostila não encontrada."));
        exit();
    }

    // Deletar do bucket
    $s3Client->deleteObject([
        'Bucket' => $bucketName,
        'Key'    => $apostila['objKey'],
    ]);

    $s3Client->deleteObject([
        'Bucket' => $bucketName,
        'Key'    => $apostila['thumbKey'],
    ]);

    // Deletar do banco
    $stmt = $db->prepare("DELETE FROM apostilas WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Apostila deletada com sucesso."));
    exit();
} catch (AwsException $e) {
    error_log("Erro no AWS S3: " . $e->getAwsErrorMessage()); // Log para desenvolvedor
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro ao enviar o arquivo ao storage."));
    exit();
} catch (PDOException $e) {
    error_log("Erro no Banco de Dados: " . $e->getMessage()); // Log para desenvolvedor
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro ao salvar no banco de dados."));
    exit();
} catch (Exception $e) {
    error_log("Exceção não tratada (Erro inesperado): " . $e->getMessage()); // Log para desenvolvedor
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro inesperado."));
    exit();
}