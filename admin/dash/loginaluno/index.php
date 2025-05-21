<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

session_start();
if (!isset($_SESSION["adm_id"])) {
    header("Location: /admin");
    exit;
}

// $_SESSION["adm"] = true;
// $_SESSION["user_id"]            = $_SESSION["adm_id"];
// $_SESSION["user_name"]          = $_SESSION["adm_name"];
// $_SESSION["user_email"]         = $_SESSION["adm_email"];
// $_SESSION["user_created_at"]    = $_SESSION["adm_created_at"];
// $_SESSION["user_avatar"]        = $_SESSION["adm_avatar"];
header("Location: /dashboard"); // Redireciona para o painel de admin após login
exit();
?>