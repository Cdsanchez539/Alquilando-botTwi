<?php

// --- CONFIGURACIÓN DE CREDENCIALES ---

// Credenciales de Bitrix24 (¡YA INSERTADAS!)
define('BITRIX_CLIENT_ID', 'local.6882d5d292b140.97912310');
define('BITRIX_CLIENT_SECRET', 'JI8u1nuC3yS1oLVTzign7aX4q4lvn6StnAfElk1lVRDXIVL8Ax');
define('BITRIX_PORTAL_URL', 'https://fonnegragerlein.bitrix24.com'); // URL de tu portal

// --- ¡ACCIÓN REQUERIDA! ---
// --- Rellena tus credenciales de Twilio ---
define('TWILIO_ACCOUNT_SID', 'AC2d95635292863259fad6a42bcf1c9acf'); // Encuéntralo en tu dashboard de Twilio
define('TWILIO_AUTH_TOKEN', '976de6e5eeb76df91e0ccaf54419e389'); // Encuéntralo en tu dashboard de Twilio
define('TWILIO_WHATSAPP_NUMBER', 'whatsapp:+576013790023'); // El número de WhatsApp de tu cuenta de Twilio (el que empieza con +1)

// --- ¡ACCIÓN REQUERIDA! ---
// --- SID de tu plantilla de contenido de Twilio ---
define('TWILIO_CONTENT_SID', 'HXa8b76a436e4d0f6b78e0b624954582f7'); // Ve a Messaging > Content Editor y copia el SID de tu plantilla "Bienvenida Alquilando"

// --- FIN DE LA CONFIGURACIÓN ---


// --- LIBRERÍA DE FUNCIONES ---

// Función para registrar logs (muy útil para depurar)
function writeToLog($data, $title = '') {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}

// Función para hacer llamadas a la API REST
function callAPI($url, $params) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// --- LÓGICA PRINCIPAL DEL BOT ---

// Obtiene los datos que envía Bitrix24 o Twilio
$request = $_REQUEST;
writeToLog($request, 'Incoming Request');

// 1. REGISTRO DEL BOT EN BITRIX24
// Se ejecuta solo una vez, cuando instalas la aplicación en Bitrix24.
if ($request['event'] == 'ONAPPINSTALL') {
    $handler_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    $result = callAPI(BITRIX_PORTAL_URL . '/rest/imbot.register', [
        'auth' => $request['auth']['access_token'],
        'CODE' => 'alquilando_bot',
        'TYPE' => 'O', // 'O' para Canal Abierto
        'EVENT_HANDLER' => $handler_url,
        'PROPERTIES' => [
            'NAME' => 'Bot Recepcionista Twilio',
            'COLOR' => 'AQUA',
            'EMAIL' => 'bot@alquilando.com',
            'WORK_POSITION' => 'Asistente Digital',
        ]
    ]);
    writeToLog($result, 'Bot Registration');
    return;
}


// 2. MANEJO DE MENSAJES ENTRANTES DE TWILIO
// Esta parte se ejecuta cada vez que un usuario envía un mensaje de WhatsApp.
$from_number = $request['From'];
$user_message = $request['Body'];
$interactive_reply_id = $request['ButtonPayload']; // Para botones de respuesta rápida
$list_reply_id = $request['ListId']; // Para menús de lista

// Si es un mensaje nuevo (no una respuesta a un menú)
if (empty($list_reply_id) && empty($interactive_reply_id)) {
    // Usamos la API de Twilio para enviar el menú de lista
    $twilio_client = new Twilio\Rest\Client(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
    $twilio_client->messages->create(
        $from_number,
        [
            "contentSid" => TWILIO_CONTENT_SID,
            "from" => TWILIO_WHATSAPP_NUMBER
        ]
    );
} 
// Si el usuario eligió "Soy cliente"
else if ($list_reply_id == 'cliente_existente') {
    // Usamos el comando de la API de Bitrix24 para transferir la sesión a la cola
    $result = callAPI(BITRIX_PORTAL_URL . '/rest/imopenlines.bot.session.transfer', [
        'auth' => $request['auth']['access_token'], // Bitrix24 nos da un token temporal
        'CHAT_ID' => $request['data']['PARAMS']['CHAT_ID'],
        'USER_ID' => 'queue' // Transfiere a la cola de agentes por defecto
    ]);
    writeToLog($result, 'Transfer to Queue');
}
// Si el usuario eligió "Estoy interesado"
else if ($list_reply_id == 'cliente_nuevo') {
    // Lógica para el cliente nuevo (ej. enviar mensaje de redirección)
    // ...
}

// Respondemos a Twilio para que sepa que recibimos el mensaje.
header("Content-Type: text/xml");
echo "<Response></Response>";

?>
