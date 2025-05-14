<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/


require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configs de conexão com o banco de dados
$host = $_ENV["DBHOST"];
$user = $_ENV["DBUSERNAME"];
$password = isset($_ENV["DBPASSWORD"]) && !is_null($_ENV["DBPASSWORD"]) ? $_ENV["DBPASSWORD"] : '';

// Database de Pix
$dbname1 = "pixarea";
class PixAreaDatabaseConnection {
    private $connection;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . $GLOBALS['host'] . ";dbname=" . $GLOBALS['dbname1'] . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, $GLOBALS['user']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Database de Auth
$dbname2 = "auth";
class AuthDatabaseConnection {
    private $connection;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . $GLOBALS['host'] . ";dbname=" . $GLOBALS['dbname2'] . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, $GLOBALS['user']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Database de Chat
$dbname3 = "chat";
class ChatDatabaseConnection {
    private $connection;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . $GLOBALS['host'] . ";dbname=" . $GLOBALS['dbname3'] . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, $GLOBALS["user"]);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Database de Dados
$dbname4 = "dados";
class DataDatabaseConnection {
    private $connection;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . $GLOBALS['host'] . ";dbname=" . $GLOBALS['dbname4'] . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, $GLOBALS["user"]);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Database de Pix
$dbname5 = "areapix";
class AreaPixDatabaseConnection {
    private $connection;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . $GLOBALS['host'] . ";dbname=" . $GLOBALS['dbname5'] . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, $GLOBALS["user"]);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}
?>
