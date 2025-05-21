<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

session_start();
if (isset($_SESSION["user_id"])) {
    header("Location: /dashboard");
    exit;
}

$DocFolder = $_SERVER['DOCUMENT_ROOT'];
if (!file_exists($DocFolder . "/databases/mainDbConn.php")) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Arquivo de conexão não encontrado.']);
    exit;
}
require_once $DocFolder . "/databases/mainDbConn.php";

$err = "";
if (isset($_GET["err"]) && $_GET["err"] == 1) {
    $err = '<div class="error"><p class="errmsg">E-Mail ou Senha incorretos!</p></div>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        require_once $DocFolder . "/databases/mainDbConn.php";
    
        $dbConn = new AuthDatabaseConnection();
        $db     = $dbConn->getConnection();
    
        $email      = $_POST["email"];
        $password   = $_POST["senha"];

        // Buscar user pelo e-mail
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"]            = $user["id"];
            $_SESSION["user_name"]          = $user["name"];
            $_SESSION["user_email"]         = $user["email"];
            $_SESSION["user_created_at"]    = $user["created_at"];
            $_SESSION["user_avatar"]        = $user["avatarpath"];
            header("Location: /dashboard"); // Redireciona para o painel de usuario após login
            exit();
        } else {
            header("Location: /auth/login/?err=1");
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
    <title>Instituto Bíblico Huperetes | Login</title>
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
            <form action="/auth/login/" method="post">
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