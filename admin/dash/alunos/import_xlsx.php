<?php
session_start();

$DocFolder = $_SERVER['DOCUMENT_ROOT'];
require_once $DocFolder . "/databases/mainDbConn.php";

if (!isset($_SESSION["adm_id"])) {
    header("Location: /admin");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["arquivo"])) {
    $fileTmpPath = $_FILES["arquivo"]["tmp_name"];

    if (!file_exists($fileTmpPath)) {
        die("Arquivo não encontrado.");
    }

    try {
        $rows = extractXlsxData($fileTmpPath);

        $dbConn = new AuthDatabaseConnection();
        $db     = $dbConn->getConnection();

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Pula o cabeçalho

            $nome  = $row[0] ?? null;
            $email = $row[1] ?? null;
            $senha = password_hash($row[2] ?? "123456", PASSWORD_DEFAULT);

            if ($nome && $email) {
                $query = $db->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
                $query->execute([$nome, $email, $senha]);
            }
        }

        header("Location: /admin/dash/alunos?msg=Importação concluída!");
    } catch (Exception $e) {
        die("Erro ao importar XLSX: " . $e->getMessage());
    }
} else {
    die("Arquivo inválido.");
}

/**
 * Lê um arquivo XLSX e retorna os dados em um array.
 */
function extractXlsxData($filePath) {
    $zip = new ZipArchive;
    if ($zip->open($filePath) !== TRUE) {
        throw new Exception("Não foi possível abrir o arquivo.");
    }

    $indexFile = "xl/sharedStrings.xml";
    $dataFile  = "xl/worksheets/sheet1.xml";
    $strings   = [];

    if ($zip->locateName($indexFile) !== false) {
        $xml = simplexml_load_string($zip->getFromName($indexFile));
        foreach ($xml->si as $s) {
            $strings[] = (string) $s->t;
        }
    }

    if ($zip->locateName($dataFile) === false) {
        throw new Exception("Planilha não encontrada.");
    }

    $xml     = simplexml_load_string($zip->getFromName($dataFile));
    $rows    = [];
    $currentRow = [];

    foreach ($xml->sheetData->row as $row) {
        foreach ($row->c as $cell) {
            $value = (string) $cell->v;
            if (isset($cell["t"]) && $cell["t"] == "s") {
                $value = $strings[(int) $value] ?? "";
            }
            $currentRow[] = $value;
        }
        $rows[] = $currentRow;
        $currentRow = [];
    }

    $zip->close();
    return $rows;
}
?>
