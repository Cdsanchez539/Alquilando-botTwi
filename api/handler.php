<?php

// --- CONFIGURACIÓN DE CREDENCIALES ---

// Credenciales de Bitrix24
define('BITRIX_CLIENT_ID', 'local.6882d5d292b140.97912310');
define('BITRIX_CLIENT_SECRET', 'JI8u1nuC3yS1oLVTzign7aX4q4lvn6StnAfElk1lVRDXIVL8Ax');
define('BITRIX_PORTAL_URL', 'https://fonnegragerlein.bitrix24.com');

// --- ¡ACCIÓN REQUERIDA! ---
// --- Rellena tus credenciales de Twilio ---
define('TWILIO_ACCOUNT_SID', 'AC2d95635292863259fad6a42bcf1c9acf');
define('TWILIO_AUTH_TOKEN', 'dbd2c035bafc4f664e46fd5fd32abc40'); // ¡MUY IMPORTANTE PARA LA VALIDACIÓN!
define('TWILIO_WHATSAPP_NUMBER', 'whatsapp:+576013790023');

// --- ¡ACCIÓN REQUERIDA! ---
// --- SID de tu plantilla de contenido de Twilio ---
define('TWILIO_CONTENT_SID', 'HXa8b76a436e4d0f6b78e0b624954582f7');

// --- FIN DE LA CONFIGURACIÓN ---


// --- FUNCIÓN DE SEGURIDAD ---
// Esta función verifica que la solicitud viene realmente de Twilio
function validateTwilioRequest($authToken, $twilioSignature, $requestUrl, $postVars) {
    ksort($postVars);
    $dataToSign = $requestUrl;
    foreach ($postVars as $key => $value) {
        $dataToSign .= $key . $value;
    }
    $expectedSignature = base64_encode(hash_hmac('sha1', $dataToSign, $authToken, true));
    return hash_equals($expectedSignature, $twilioSignature);
}


// --- LIBRERÍA DE FUNCIONES ---
function writeToLog($data, $title = '') { /* ... (código de log sin cambios) ... */ }
function callAPI($url, $params) { /* ... (código de API sin cambios) ... */ }


// --- LÓGICA PRINCIPAL DEL BOT ---

// 1. VALIDACIÓN DE SEGURIDAD
$requestUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$twilioSignature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'];
$postVars = $_POST;

if (!validateTwilioRequest(TWILIO_AUTH_TOKEN, $twilioSignature, $requestUrl, $postVars)) {
    header("HTTP/1.1 403 Forbidden");
    echo "Error: Invalid signature.";
    exit;
}

// Si la firma es válida, continuamos...

$request = $_REQUEST;
writeToLog($request, 'Incoming Validated Request');

// 2. REGISTRO DEL BOT EN BITRIX24
if (isset($request['event']) && $request['event'] == 'ONAPPINSTALL') {
    // ... (código de registro sin cambios) ...
}

// 3. MANEJO DE MENSAJES ENTRANTES DE TWILIO
$from_number = $request['From'];
$list_reply_id = $request['ListId'];

if (empty($list_reply_id)) {
    // Lógica para enviar el menú (requiere librería Twilio)
    // ...
} 
else if ($list_reply_id == 'cliente_existente') {
    // Lógica para transferir a Bitrix24
    // ...
}
else if ($list_reply_id == 'cliente_nuevo') {
    // Lógica para cliente nuevo
    // ...
}

header("Content-Type: text/xml");
echo "<Response></Response>";

?>

