<?php
session_start();

// En una implementación completa, aquí también se debería llamar a la API de SAP 
// para invalidar la sesión en el servidor de SAP (POST a /b1s/v2/Logout).
// Por ahora, solo destruiremos la sesión de PHP, que es el paso fundamental.

// Destruir todas las variables de sesión.
$_SESSION = array();

// Si se desea destruir la sesión completamente, borre también la cookie de sesión.
// Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();

// Redirigir al login
header("Location: ../index.php");
exit;