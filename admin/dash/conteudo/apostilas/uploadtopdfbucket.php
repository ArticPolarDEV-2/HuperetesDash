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

// Include main database connection
require_once $DocFolder . "/databases/mainDbConn.php";

// AWS SDK Autoloader
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Verifica se título está presente
if (!isset($_POST['titulo']) || empty(trim($_POST['titulo']))) {
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Título é obrigatório."));
    exit();
}

$titulo = trim($_POST['titulo']);

// Verifica PDF
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro no upload do PDF."));
    exit();
}

// Verifica Thumbnail
if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro no upload da thumbnail."));
    exit();
}

// Iniciar DB
$dbconn = new DataDatabaseConnection();
$db = $dbconn->getConnection();

// AWS S3 Configuração
// Consulta para obter os dados da tabela configs
$sql    = "SELECT * FROM configs LIMIT 1";
$stmt   = $db->query($sql);

// Verifica se retornou alguma linha
if ($stmt && $config = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $bucketName     = $config['docsBucket'];
    $region         = $config['region'];
    $acessKey       = $config['acessKey'];
    $secretKey      = $config['secretKey'];
    $endpoint       = $config['endpoint'];
    $publicUrlBase  = $config['publicUrlBase'];

    // Exemplo de uso
    // echo "Conectando ao bucket $bucketName na região $region\n";
} else {
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode('Nenhuma configuração encontrada na tabela configs.'));
    exit();
}

$s3Client = new S3Client([
    'version'                       => 'latest',
    'region'                        => $region,
    'credentials'                   => [
        'key'                       => $acessKey,
        'secret'                    => $secretKey,
    ],
    'endpoint'                      => $endpoint,
    'use_path_style_endpoint'       => true,
]);

try {
    $fileID = uniqid();
    // === Upload PDF ===
    $tmpFile = $_FILES['file']['tmp_name'];
    $originalName = basename($_FILES['file']['name']);
    $fileMime = mime_content_type($tmpFile);
    if ($fileMime !== 'application/pdf') {
        header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode('Apenas arquivos PDF são permitidos.'));
        exit();
    }
    $fileName = $fileID . '-' . $originalName;
    $objectKey = "uploads/apostilas/" . $fileName;


    $resultPdf = $s3Client->putObject([
        'Bucket'            => $bucketName,
        'Key'               => $objectKey,
        'SourceFile'        => $tmpFile,
        'ACL'               => 'public-read',
        'ContentType'       => $fileMime,
        'Metadata'          => [
            'original_name' => $originalName,
            'uploaded_by'   => $_SESSION['adm_id'],
        ],
    ]);
    $publicUrl = $publicUrlBase . '/uploads/apostilas/' . rawurlencode($fileName); // cuidado com espaços!

    // === Upload Thumbnail ===
    $tmpThumb   = $_FILES['thumbnail']['tmp_name'];
    $thumbMime  = mime_content_type($tmpThumb);
    if (!str_starts_with($thumbMime, 'image/')) {
        header('Location: admin/dash/conteudo/apostilas?msg=' . urlencode('Apenas imagens são permitidas para a thumbnail.'));
        exit();
    }
    // $thumbName  = uniqid() . '-' . basename($_FILES['thumbnail']['name']);
    $thumbName = $fileName . '_cover.' . pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
    $thumbKey   = "uploads/thumbnails/" . $thumbName;

    $resultThumb = $s3Client->putObject([
        'Bucket'        => $bucketName,
        'Key'           => $thumbKey,
        'SourceFile'    => $tmpThumb,
        'ACL'           => 'public-read',
        'ContentType'   => $thumbMime,
    ]);
    $thumbUrl = $publicUrlBase . '/uploads/thumbnails/' . rawurlencode($thumbName); // cuidado com espaços!

    // === Inserir no banco ===
    $stmt = $db->prepare("INSERT INTO apostilas (titulo, path, thumbnail, objKey, thumbKey) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $publicUrl, $thumbUrl, $objectKey, $thumbKey]);

    // Redireciona com sucesso
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Arquivo enviado com sucesso!"));
} catch (AwsException $e) {
    error_log("Erro no AWS S3: " . $e->getAwsErrorMessage()); // Log para desenvolvedor
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro ao enviar o arquivo ao storage."));
} catch (PDOException $e) {
    error_log("Erro no Banco de Dados: " . $e->getMessage()); // Log para desenvolvedor
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro ao salvar no banco de dados."));
} catch (Exception $e) {
    error_log("Exceção não tratada (Erro inesperado): " . $e->getMessage()); // Log para desenvolvedor
    header('Location: /admin/dash/conteudo/apostilas/?msg=' . urlencode("Erro inesperado."));
}

// Limpa arquivos temporários
unlink($tmpFile);
unlink($tmpThumb);
exit();