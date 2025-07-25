<?php

// Carga la librería oficial de Twilio que Vercel instalará por nosotros
require __DIR__ . '/../../vendor/autoload.php';
use Twilio\Security\RequestValidator;
use Twilio\Rest\Client;

// --- CONFIGURACIÓN DE CREDENCIALES ---

// Credenciales de Bitrix24
define('BITRIX_CLIENT_ID', 'local.6882d5d292b140.97912310');
define('BITRIX_CLIENT_SECRET', 'JI8u1nuC3yS1oLVTzign7aX4q4lvn6StnAfElk1lVRDXIVL8Ax');
define('BITRIX_PORTAL_URL', 'https://fonnegragerlein.bitrix24.com');

// --- ¡ACCIÓN REQUERIDA! ---
// --- Asegúrate de que estas credenciales de Twilio sean las correctas ---
define('TWILIO_ACCOUNT_SID', 'AC2d95635292863259fad6a42bcf1c9acf');
define('TWILIO_AUTH_TOKEN', 'dbd2c035bafc4f664e46fd5fd32abc40'); // ¡LA CREDENCIAL MÁS IMPORTANTE!
define('TWILIO_WHATSAPP_NUMBER', 'whatsapp:+576013790023');
define('TWILIO_CONTENT_SID', 'HXa8b76a436e4d0f6b78e0b624954582f7');

// --- FIN DE LA CONFIGURACIÓN ---


// --- LIBRERÍA DE FUNCIONES ---
function writeToLog($data, $title = '') { /* ... (código de log sin cambios) ... */ }
function callAPI($url, $params) { /* ... (código de API sin cambios) ... */ }


// --- LÓGICA PRINCIPAL DEL BOT ---

// 1. VALIDACIÓN DE SEGURIDAD PROFESIONAL
$validator = new RequestValidator(TWILIO_AUTH_TOKEN);

$requestUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$postVars = $_POST;
$twilioSignature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'];

if (!$validator->validate($twilioSignature, $requestUrl, $postVars)) {
    header("HTTP/1.1 403 Forbidden");
    echo "Error: Invalid signature.";
    writeToLog(['url' => $requestUrl, 'post' => $postVars, 'sig' => $twilioSignature], 'VALIDATION FAILED');
    exit;
}

// Si la firma es válida, continuamos...
$request = $_REQUEST;
writeToLog($request, 'Incoming Validated Request');

// 2. REGISTRO DEL BOT EN BITRIX24
if (isset($request['event']) && $request['event'] == 'ONAPPINSTALL') {
    // ... (código de registro sin cambios, se ejecutará al reinstalar) ...
}


// 3. MANEJO DE MENSAJES ENTRANTES DE TWILIO
$from_number = $request['From'];
$list_reply_id = $request['ListId'];

// Si es un mensaje nuevo (no una respuesta a un menú)
if (empty($list_reply_id)) {
    try {
        $twilio_client = new Client(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
        $twilio_client->messages->create(
            $from_number,
            [
                "contentSid" => TWILIO_CONTENT_SID,
                "from" => TWILIO_WHATSAPP_NUMBER
            ]
        );
    } catch (Exception $e) {
        writeToLog('Error sending Twilio message: ' . $e->getMessage(), 'TWILIO API ERROR');
    }
} 
// Si el usuario eligió "Soy cliente"
else if ($list_reply_id == 'cliente_existente') {
    // ... (lógica para transferir a Bitrix24, sin cambios) ...
}

header("Content-Type: text/xml");
echo "<Response></Response>";

?>
