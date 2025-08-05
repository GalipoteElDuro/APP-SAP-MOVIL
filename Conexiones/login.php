<?php
session_start(); // Iniciar la sesión al principio de todo

// Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Si no es POST, redirigir al login
    header("Location: ../index.php");
    exit;
}

// --- Configuración ---
$apiUrl = "https://acv.b1.do:50000/b1s/v2/Login";
// Es más seguro definir la CompanyDB aquí que recibirla del formulario
$companyDB = "ALMACENES_CRISTO_VIENE";

// --- Obtener datos del formulario ---
// Usar el operador de fusión de null (??) es más corto y limpio
$usuario = $_POST['usuario'] ?? '';
$clave = $_POST['password'] ?? '';

// Validar que los campos no estén vacíos
if (empty($usuario) || empty($clave)) {
    $_SESSION['login_error'] = "El usuario y la contraseña son obligatorios.";
    header("Location: ../index.php");
    exit;
}

// --- Preparar la solicitud a la API ---
$data = [
    "UserName"  => $usuario,
    "Password"  => $clave,
    "CompanyDB" => $companyDB
];

// --- Ejecutar cURL ---
$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
// ⚠️ Para pruebas. En producción, esto debería ser `true` y configurar el trust store del servidor.
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Complementario a VERIFYPEER false

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// --- Procesar la respuesta ---
if ($curl_error) {
    // Error a nivel de cURL (ej. no se pudo conectar)
    // Es mejor guardar esto en un log que mostrarlo al usuario
    error_log("cURL Error: " . $curl_error);
    $_SESSION['login_error'] = "Error de conexión con el servidor. Intente más tarde.";
    header("Location: ../index.php");
    exit;
}

if ($http_code === 200) {
    // ✅ Login exitoso
    $sessionData = json_decode($response, true);

    // Guardar datos de la sesión de SAP en la sesión de PHP
    $_SESSION['sap_session_id'] = $sessionData['SessionId'];
    $_SESSION['sap_session_timeout'] = $sessionData['SessionTimeout'];
    $_SESSION['user'] = $usuario;

    // Limpiar cualquier error de login anterior
    unset($_SESSION['login_error']);

    // Redirigir al panel principal de la aplicación
    header("Location: ../inicio.php");
    exit;
} else {
    // ❌ Error de autenticación o del servidor SAP
    $error_response = json_decode($response, true);
    // Intentamos obtener un mensaje de error específico de la respuesta de SAP
    $error_message = $error_response['error']['message']['value'] ?? 'Credenciales incorrectas o error en el servidor.';

    $_SESSION['login_error'] = "Error ($http_code): " . $error_message;
    header("Location: ../index.php");
    exit;
}
?>
