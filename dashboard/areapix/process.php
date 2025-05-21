<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: /auth/login");
    exit();
}

if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/databases/mainDbConn.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo de conexão não encontrado.']);
    exit;
}

// Incluir arquivo de conexão com o banco de dados
include_once($_SERVER['DOCUMENT_ROOT'] . '/databases/mainDbConn.php');

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter dados do formulário
    $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    // Replace deprecated FILTER_SANITIZE_STRING with modern alternatives
    $description = filter_input(INPUT_POST, 'description', FILTER_UNSAFE_RAW);
    // Additional sanitization
    $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    $user_id = $_SESSION["user_id"];
    // Usar a data atual do momento do envio
    //$transaction_date = date('Y-m-d H:i:s');
    
    // Converter valor para formato numérico (substituir vírgula por ponto)
    $amount = str_replace(',', '.', $amount);
    
    $dbConn = new AreaPixDatabaseConnection();
    $db = $dbConn->getConnection();

    // Validar upload de arquivo
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['receipt']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Criar diretório de uploads se não existir
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/receipts/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Gerar nome único para o arquivo
            $file_extension = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('pix_') . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            // Mover o arquivo para o diretório de uploads
            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $file_path)) {
                try {
                    // Inserir registro no banco de dados - PDO version
                    $query = "INSERT INTO pix_transactions (user_id, amount, description, receipt_image, status, transaction_date) 
                                                                VALUES (:user_id, :amount, :description, :receipt_image, 'pending', NOW())";
                    $stmt = $db->prepare($query);
                    
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
                    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                    $stmt->bindParam(':receipt_image', $file_name, PDO::PARAM_STR);
                    
                    if ($stmt->execute()) {
                        // Redirecionar com mensagem de sucesso
                        $_SESSION['pix_message'] = "Pagamento registrado com sucesso! Aguarde a validação.";
                        $_SESSION['pix_status'] = "success";
                    } else {
                        $_SESSION['pix_message'] = "Erro ao registrar pagamento.";
                        $_SESSION['pix_status'] = "error";
                    }
                } catch (PDOException $e) {
                    $_SESSION['pix_message'] = "Erro ao registrar pagamento: " . $e->getMessage();
                    $_SESSION['pix_status'] = "error";
                }
            } else {
                $_SESSION['pix_message'] = "Erro ao fazer upload do arquivo.";
                $_SESSION['pix_status'] = "error";
            }
        } else {
            $_SESSION['pix_message'] = "Tipo de arquivo não permitido. Use apenas imagens (JPEG, PNG, GIF).";
            $_SESSION['pix_status'] = "error";
        }
    } else {
        $_SESSION['pix_message'] = "Erro no upload do comprovante.";
        $_SESSION['pix_status'] = "error";
    }
    
    // No need to close the connection with PDO
    
    // Redirecionar de volta para a página principal
    header("Location: /dashboard/areapix");
    exit();
}