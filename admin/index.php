<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

// Debug Code

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 

$DocFolder = $_SERVER['DOCUMENT_ROOT'];
if (!file_exists($DocFolder . "/databases/mainDbConn.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo de conexão do chat não encontrado.']);
    exit;
}

session_start();
if (isset($_SESSION["adm_id"])) {
    header("Location: /admin/dash");
    exit;
}

$err = "";
if (isset($_GET["err"]) && $_GET["err"] == 1) {
    $err = '<div class="error"><p class="errmsg">E-Mail ou Senha incorretos!</p></div>';
}

// Login code integrated into the same file for simplicity
// and to avoid multiple file inclusions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once $DocFolder . "/databases/mainDbConn.php";

    try {
        $dbConn = new AuthDatabaseConnection();
        $db     = $dbConn->getConnection();
    
        $email      = $_POST["email"];
        $password   = $_POST["senha"];
    
        // Buscar admin pelo e-mail
        $stmt = $db->prepare("SELECT * FROM admin WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $adm = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($adm && password_verify($password, $adm["password"])) {
            $_SESSION["adm_id"]            = $adm["id"];
            $_SESSION["adm_name"]          = $adm["name"];
            $_SESSION["adm_email"]         = $adm["email"];
            $_SESSION["adm_created_at"]    = $adm["created_at"];
            $_SESSION["adm_avatar"]        = $adm["avatarpath"];
            $_SESSION["adm"]               = true;;
            header("Location: /admin/dash"); // Redireciona para o painel de admin após login
            exit();
        } else {
            header("Location: /admin/?err=1");
        }
    } catch (PDOException $e) {
        echo "Erro ao criar o banco: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Instituto Bíblico Huperetes</title>
    <link rel="stylesheet" href="/css/root.css">
    <link rel="stylesheet" href="/auth/login/css/login.css">
</head>
<body>
    <div class="header">
        <center>
            <img src="/assets/logo.png" alt="Logo do Huperetes" class="logo">
            <h1 class="title">Instituto Bíblico Huperetes</h1>
        </center>
    </div>
    <div class="container">
        <center>
            <?= $err ?>
            <form action="/admin/index.php" method="post">
                <h4 class="title">-= AUTENTICAÇÃO ADMINISTRADOR =-</h4>
                <div>
                    <label for="email">E-Mail: </label>
                    <input type="text" name="email" id="">
                </div>
    
                <div>
                    <label for="senha">Senha: </label>
                    <input type="password" name="senha" id="">
                </div>
                
                <div>
                    <input type="submit" value="Login">
                </div>
            </form>
        </center>
    </div>
</body>
</html>