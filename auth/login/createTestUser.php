<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

session_start();
$DocFolder = $_SERVER['DOCUMENT_ROOT'];

require_once $DocFolder . "/databases/mainDbConn.php";

try {
    $dbConn = new AuthDatabaseConnection();
    $db = $dbConn->getConnection();

    // Criar as tabelas se não existirem
    $schemaFile = $DocFolder . "/databases/schemaAuth.sql";
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $db->exec($sql);
    } else {
        throw new Exception("Arquivo schemaAuth.sql não encontrado.");
    }

    // Dados do usuário de teste
    $name1      = "José Lucas Santos Silva";
    $email1     = "articpolardev@icloud.com";
    $password1  = password_hash("Ban17anos", PASSWORD_DEFAULT);

    // Dados do segundo usuário de teste
    $name2      = "ArticPolarDEV 202020";
    $email2     = "vilma4040p@gmail.com";
    $password2  = password_hash("teste123", PASSWORD_DEFAULT);

    // Função para verificar e inserir usuário
    function createUser($db, $name, $email, $password) {
        // Verificar se o e-mail já existe
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // Se o e-mail não existir, inserir o usuário
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            echo "Usuário '$name' criado com sucesso!<br>";
        } else {
            echo "O usuário com o email '$email' já existe no banco de dados.<br>";
        }
    }

    // Inserir o primeiro usuário
    createUser($db, $name1, $email1, $password1);

    // Inserir o segundo usuário
    createUser($db, $name2, $email2, $password2);

} catch (PDOException $e) {
    echo "Erro ao inserir usuário: " . $e->getMessage();
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
