<?php
// ===== SECCIÓN PHP - CONTROL DE SESIÓN =====
session_start(); // Inicia la sesión para poder usar variables de sesión

// Verificación de seguridad: Si el usuario no está logueado, redirigir al login
if (!isset($_SESSION['sap_session_id'])) {
    header('Location: index.php'); // Redirige a la página de login
    exit; // Detiene la ejecución del script
}

// Obtiene datos del usuario desde la sesión y los sanitiza para evitar XSS
$user = htmlspecialchars($_SESSION['user']); // Nombre del usuario logueado
$sessionId = htmlspecialchars($_SESSION['sap_session_id']); // ID de sesión SAP
?>
<!DOCTYPE html>
<html lang="es"> <!-- Documento HTML en español -->

<head>
    <meta charset="UTF-8"> <!-- Codificación de caracteres UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive design -->
    <title>INICIO - ALMACENES CRISTO VIENE</title> <!-- Título de la pestaña -->

    <!-- ===== ENLACES A LIBRERÍAS EXTERNAS ===== -->
    <!-- Bootstrap CSS para estilos y componentes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons para iconografía -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style-pages.css">
</head>

<body>
    <!-- ===== BARRA DE NAVEGACIÓN ===== -->
    <!-- Navbar fija en la parte superior con colores corporativos -->
    <nav class="navbar navbar-corporate fixed-top">
        <div class="container-fluid">
            <!-- Logo/nombre de la empresa -->
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-building me-2"></i>ALMACENES CRISTO VIENE
            </a>

            <!-- Botón hamburguesa para dispositivos móviles -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- ===== MENÚ LATERAL DESLIZANTE (OFFCANVAS) ===== -->
            <!-- Se abre desde la derecha cuando se hace clic en el botón hamburguesa -->
            <div class="offcanvas offcanvas-end offcanvas-corporate" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">

                <!-- Cabecera del menú lateral -->
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title fw-bold" id="offcanvasNavbarLabel">
                        <i class="bi bi-building me-2"></i>ALMACENES CRISTO VIENE
                    </h5>
                    <!-- Botón para cerrar el menú lateral -->
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>

                <!-- Cuerpo del menú lateral con opciones de navegación -->
                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <!-- Opción INICIO (activa actualmente) -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5 active" aria-current="page" href="#">
                                <i class="bi bi-house me-2 fs-5"></i>INICIO
                            </a>
                        </li>
                        <!-- Opción TRASLADO DE STOCK (enlaza a otra página) -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="transferencia-de-stock.php">
                                <i class="bi bi-truck me-2 fs-5"></i>TRASLADO DE STOCK
                            </a>
                        </li>
                        <!-- Opción INFORME DE RECEPCIÓN (sin enlace aún) -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="informe-de-recepcion.php">
                                <i class="bi bi-file-earmark-text me-2 fs-5"></i>INFORME DE RECEPCIÓN
                            </a>
                        </li>
                        <!-- Opción INVENTARIO (sin enlace aún) -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="inventario.php">
                                <i class="bi bi-box-seam me-2 fs-5"></i>INVENTARIO
                            </a>
                        </li>
                        <!-- Opción ORDEN DE COMPRA (sin enlace aún) -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="inventario.php">
                                <i class="bi bi-receipt me-2 fs-5"></i>ORDEN DE COMPRA
                            </a>
                        </li>
                        <!-- Opción AVERÍAS (sin enlace aún) -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="averias.php">
                                <i class="bi bi-x-circle me-2 fs-5"></i>AVERÍAS
                            </a>
                        </li>
                        <!-- Opción PRECIOS (sin enlace aún) -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="precios.php">
                                <i class="bi bi-tag me-2 fs-5"></i>PRECIOS
                            </a>
                        </li>
                        <!-- Botón CERRAR SESIÓN (posicionado al final del menú) -->
                        <li class="nav-item mt-auto">
                            <a class="btn btn-danger fw-bold fs-5" href="#">
                                <i class="bi bi-box-arrow-right me-2 fs-5"></i>CERRAR SESIÓN
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <div class="container mt-4"> <!-- Contenedor con margen superior para evitar solapamiento con navbar fija -->

        <!-- Mensaje de bienvenida personalizado con el nombre del usuario -->
        <h3 class="welcome-message">Bienvenido, <strong><?php echo $user; ?></strong></h3>

        <!-- ===== GRID DE TARJETAS DE ACCESO RÁPIDO ===== -->
        <!-- Sistema responsive: 2 columnas en móvil, 3 en tablet, 4 en desktop -->
        <div class="row g-3">

            <!-- Tarjeta 1: Transferencia de Stock -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="transferencia-de-stock.php" class="card-custom">
                    <div class="card-body">
                        <i class="bi bi-truck card-icon"></i> <!-- Icono de camión -->
                        <div class="card-text-label">Transferencia de Stock</div>
                    </div>
                </a>
            </div>

            <!-- Tarjeta 2: Reporte de Recepción -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="informe-de-recepcion.php" class="card-custom"> <!-- Sin enlace funcional aún -->
                    <div class="card-body">
                        <i class="bi bi-file-earmark-text card-icon"></i> <!-- Icono de documento -->
                        <div class="card-text-label">Informe de Recepción</div>
                    </div>
                </a>
            </div>

            <!-- Tarjeta 3: Inventario -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="inventario.php" class="card-custom"> <!-- Sin enlace funcional aún -->
                    <div class="card-body">
                        <i class="bi bi-box-seam card-icon"></i> <!-- Icono de caja -->
                        <div class="card-text-label">Inventario</div>
                    </div>
                </a>
            </div>

            <!-- Tarjeta 4: Orden de compra -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="orden-de-compra.php" class="card-custom"> <!-- Sin enlace funcional aún -->
                    <div class="card-body">
                        <i class="bi bi-receipt card-icon"></i> <!-- Icono de caja -->
                        <div class="card-text-label">Orden De Compra</div>
                    </div>
                </a>
            </div>

            <!-- Tarjeta 5: Daños -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="averias.php" class="card-custom"> <!-- Sin enlace funcional aún -->
                    <div class="card-body">
                        <i class="bi bi-x-circle card-icon"></i> <!-- Icono de X en círculo -->
                        <div class="card-text-label">Averías</div>
                    </div>
                </a>
            </div>

            <!-- Tarjeta 6: Precios -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="precios.php" class="card-custom"> <!-- Sin enlace funcional aún -->
                    <div class="card-body">
                        <i class="bi bi-tag card-icon"></i> <!-- Icono de etiqueta -->
                        <div class="card-text-label">Precios</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- ===== SCRIPT DE BOOTSTRAP ===== -->
    <!-- JavaScript necesario para que funcionen los componentes interactivos de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>