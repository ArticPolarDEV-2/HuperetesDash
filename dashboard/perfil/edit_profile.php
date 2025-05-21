<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

session_start();

// Conectar ao banco de dados MySQL
$DocFolder = $_SERVER["DOCUMENT_ROOT"];

if (!file_exists($DocFolder . "/databases/mainDbConn.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo de conexão não encontrado.']);
    exit;
}

require_once $DocFolder . "/databases/mainDbConn.php";

// Verificar se o usuário está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: /auth/login");
}

$user_id = $_SESSION["user_id"];

try {
    $dbConn = new AuthDatabaseConnection();
    $db = $dbConn->getConnection();

    // Processar o formulário se enviado
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Buscar os dados atuais do usuário
        $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = trim($_POST["email"]);

        // Atualizar o nome e email
        $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $user_id]);
        $_SESSION["user_email"] = $email;

        // Verificar se um novo avatar foi enviado
        if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == 0) {
            $fileTmpPath = $_FILES["avatar"]["tmp_name"];
            $fileName = $_FILES["avatar"]["name"];
            $fileSize = $_FILES["avatar"]["size"];
            $fileType = $_FILES["avatar"]["type"];

            // Definir o tamanho máximo permitido (10MB = 10 * 1024 * 1024 bytes)
            $maxFileSize = 10 * 1024 * 1024;

            if ($fileSize > $maxFileSize) {
                header("Refresh: 2; URL=/dashboard/perfil?err=1");
            }

            $fileExtension = strtolower(
                pathinfo($fileName, PATHINFO_EXTENSION)
            );

            if (in_array($fileExtension, ["png", "jpg", "jpeg"])) {
                $newFileName = uniqid("", true) . "." . $fileExtension;
                $uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/avatars/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $uploadFilePath = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                    // Atualizar o banco de dados com o novo nome do arquivo
                    $stmt = $db->prepare(
                        "UPDATE users SET avatarpath = ? WHERE id = ?"
                    );
                    $stmt->execute([$newFileName, $user_id]);
                    $_SESSION["user_avatar"] = $newFileName;
                }
            }
        }

        header("Refresh: 2; URL=/dashboard/perfil");
        exit();
    }
} catch (PDOException $e) {
    echo "Erro ao criar o banco: " . $e->getMessage();
}
