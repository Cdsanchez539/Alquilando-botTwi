<?php
// --- SCRIPT DE DIAGNÓSTICO ---
// Este script no procesa lógica, solo captura y muestra toda la información
// que Vercel recibe de Twilio para que podamos depurar el problema de validación.

// Habilita el reporte de todos los errores para máxima información
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Imprimimos un encabezado para saber cuándo empieza nuestro log
echo "--- INICIO DEL LOG DE DIAGNÓSTICO DE VERCEL ---\n\n";

// 1. Capturamos y mostramos todos los HEADERS de la solicitud
echo "--- HEADERS RECIBIDOS ---\n";
// getallheaders() no siempre funciona en entornos serverless, usamos una alternativa más robusta.
foreach ($_SERVER as $name => $value) {
    if (substr($name, 0, 5) == 'HTTP_') {
        echo str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))) . ': ' . $value . "\n";
    }
}
echo "\n\n";

// 2. Capturamos y mostramos los datos POST
echo "--- DATOS POST (\$_POST) ---\n";
print_r($_POST);
echo "\n\n";

// 3. Capturamos y mostramos los datos GET
echo "--- DATOS GET (\$_GET) ---\n";
print_r($_GET);
echo "\n\n";

// 4. Capturamos y mostramos el CUERPO CRUDO de la solicitud
// A menudo, aquí es donde están los datos que $_POST no puede ver.
echo "--- CUERPO CRUDO (RAW BODY) ---\n";
$raw_body = file_get_contents('php://input');
print_r($raw_body);
echo "\n\n";

echo "--- FIN DEL LOG DE DIAGNÓSTICO ---\n";

// Finalmente, respondemos a Twilio con un TwiML vacío para que la solicitud
// en su debugger no quede marcada como un error de "respuesta vacía".
header("Content-Type: text/xml");
echo "<Response></Response>";

?>
