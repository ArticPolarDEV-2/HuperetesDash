<?php
/* 
    CODIGO FEITO POR ARTICPOLARDEV.
    CODIGO LICENÇA MIT - RESPEITE A LICENÇA!
*/

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: /auth/login"); // Redireciona se nenhum dos dois estiver definido
    exit();
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['amount'])) {
    echo json_encode(['error' => 'Missing amount parameter']);
    exit();
}

function formatField($id, $valor) {
    return $id . str_pad(strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
}

function calcCRC16($data) {
    $result = 0xFFFF;
    
    for ($i = 0; $i < strlen($data); $i++) {
        $result ^= (ord($data[$i]) << 8);
        
        for ($j = 0; $j < 8; $j++) {
            if ($result & 0x8000) {
                $result = ($result << 1) ^ 0x1021;
            } else {
                $result <<= 1;
            }
            $result &= 0xFFFF; // Mantém o resultado dentro de 16 bits
        }
    }
    return strtoupper(str_pad(dechex($result), 4, '0', STR_PAD_LEFT));
}

function genPix($key, $idTx = '', $amount = 0.00) {
    $result = "000201";
    $result .= formatField("26", "0014br.gov.bcb.pix" . formatField("01", $key));
    $result .= "52040000"; // Fixed Code
    $result .= "5303986";  // Coin (Real)

    if ($amount > 0) {
        $result .= formatField("54", number_format($amount, 2, '.', ''));
    }

    $result .= "5802BR"; // Country Code
    $result .= "5901N"; // Name
    $result .= "6001C"; // City
    $result .= formatField("62", formatField("05", $idTx ?: '***'));
    $result .= "6304"; // Start of CRC16
    $result .= calcCRC16($result); // Add the CRC16 at finish
    return $result;
}

// Data of the transaction
$key = "+5577998696801"; // Key of the receiver
$amountOfTransaction = $_GET['amount']; // Amount of the transaction
$idTransaction = ""; // Indentifier of the transaction, if exist

// Create the Pix code
$codePix = genPix($key, $idTransaction, $amountOfTransaction);

//echo '<h2>Pix Code with QRCode: <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($codePix) . '" alt="QR Code"></h2>';
//echo "<h1>Pix Code: $codePix</h1>";

echo json_encode([
    'codePix' => $codePix,
    'qrcode' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($codePix)
]);
?>