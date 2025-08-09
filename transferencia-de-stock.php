<?php
session_start();

// =========== BACKEND API PARA PROCESAR TRANSFERENCIAS ===========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['sap_session_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Error: No ha iniciado sesión.']);
        exit;
    }

    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Error: Datos JSON inválidos.']);
        exit;
    }

    $warehouseCode = $data['WarehouseCode'];
    $lines = $data['Lines'];
    $stockTransferLines = [];
    $lineNum = 0;

    foreach ($lines as $line) {
        $stockTransferLines[] = [
            'LineNum' => $lineNum,
            'ItemCode' => $line['ItemCode'],
            'ItemDescription' => $line['ItemDescription'],
            'Quantity' => $line['Quantity'],
            'FromWarehouseCode' => $warehouseCode,
            'WarehouseCode' => $warehouseCode, // En transferencias, el ToWarehouse es el WarehouseCode en las líneas
            'StockTransferLinesBinAllocations' => [
                [
                    'BinAbsEntry' => $line['FromBinAbsEntry'],
                    'Quantity' => $line['Quantity'],
                    'AllowNegativeQuantity' => 'tNO',
                    'BinActionType' => 'batFromWarehouse',
                    'BaseLineNumber' => $lineNum
                ],
                [
                    'BinAbsEntry' => $line['ToBinAbsEntry'],
                    'Quantity' => $line['Quantity'],
                    'AllowNegativeQuantity' => 'tNO',
                    'BinActionType' => 'batToWarehouse',
                    'BaseLineNumber' => $lineNum
                ]
            ]
        ];
        $lineNum++;
    }

    $sap_data = [
        'FromWarehouse' => $warehouseCode,
        'ToWarehouse' => $warehouseCode,
        'Comments' => 'Transferencia creada desde APP-SAP-MOVIL',
        'JournalMemo' => 'Inventory Transfers - SAP App',
        'StockTransferLines' => $stockTransferLines
    ];

    $apiUrl = 'https://acv.b1.do:50000/b1s/v2/StockTransfers';
    $sessionId = $_SESSION['sap_session_id'];
    $ch = curl_init($apiUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Cookie: B1SESSION=' . $sessionId
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sap_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error de cURL: ' . $curl_error]);
        exit;
    }

    if ($http_code === 201) {
        echo json_encode(['success' => true, 'message' => 'Transferencia creada con éxito en SAP.']);
    } else {
        http_response_code($http_code);
        $error_response = json_decode($response, true);
        $error_message = $error_response['error']['message']['value'] ?? 'Error desconocido al procesar en SAP.';
        echo json_encode(['success' => false, 'message' => "Error SAP ($http_code): " . $error_message]);
    }

    exit;
}

// El resto del archivo HTML/PHP sigue aquí abajo sin cambios
// ============================================================

// Si el usuario no está logueado, redirigir al login
if (!isset($_SESSION['sap_session_id'])) {
    header('Location: index.php');
    exit;
}

$user = htmlspecialchars($_SESSION['user']);
$sessionId = htmlspecialchars($_SESSION['sap_session_id']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRANSFERENCIA DE STOCK - ALMACENES CRISTO VIENE</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="css/transferencia-de-stock.css">
</head>

<body>
    <!-- ===== NAVBAR ===== -->
    <nav class="navbar navbar-corporate fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-building me-2"></i>ALMACENES CRISTO VIENE
            </a>

            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- ===== OFFCANVAS MENU ===== -->
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
                            <a class="nav-link fw-bold fs-5" aria-current="page" href="#">
                                <i class="bi bi-house me-2 fs-5"></i>INICIO
                            </a>
                        </li>
                        <!-- Opción TRASLADO DE STOCK (enlaza a otra página) -->
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5 active" href="transferencia-de-stock.php">
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
                            <a class="btn btn-danger fw-bold fs-5" href="Conexiones/logout.php">
                                <i class="bi bi-box-arrow-right me-2 fs-5"></i>CERRAR SESIÓN
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="inicio.php" class="text-decoration-none">
                        <i class="bi bi-house me-1"></i>Inicio
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-truck me-1"></i>Transferencia de Stock
                </li>
            </ol>
        </nav>

        <!-- Título de la página -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="page-title">
                                <i class="bi bi-truck me-3"></i>Transferencia de Stock
                            </h2>
                            <p class="page-subtitle">Usuario: <strong><?php echo $user; ?></strong></p>
                        </div>
                        <div class="col-md-4">
                            <div class="sucursal-selector">
                                <label for="sucursalSelect" class="form-label fw-semibold mb-2">
                                    <i class="bi bi-building me-1"></i>Sucursal Activa
                                </label>
                                <select class="form-select form-control-custom" id="sucursalSelect" onchange="changeSucursal()" required>
                                    <option value="">Seleccionar sucursal</option>
                                    <option value="01">01 - Principal</option>
                                    <option value="02">02 - Almacén Al Por Mayor</option>
                                    <option value="03">03 - Villa Hermosa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- ===== FORMULARIO DE TRANSFERENCIA ===== -->
            <div class="col-lg-5 col-xl-4 mb-4">
                <div class="card form-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-plus-circle me-2"></i>Nueva Transferencia
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="transferForm">
                            <!-- Número de Artículo -->
                            <div class="mb-3">
                                <label for="numeroArticulo" class="form-label fw-semibold">
                                    <i class="bi bi-hash me-1"></i>Número de Artículo
                                </label>
                                <input type="text" class="form-control form-control-custom" id="numeroArticulo"
                                    placeholder="Ej: ART001" required readonly>
                            </div>

                            <!-- Código de Barras -->
                            <div class="mb-3">
                                <label for="codigoBarras" class="form-label fw-semibold">
                                    <i class="bi bi-upc-scan me-1"></i>Código de Barras
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-custom" id="codigoBarras"
                                        placeholder="Escanear o escribir código">
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-3">
                                <label for="descripcion" class="form-label fw-semibold">
                                    <i class="bi bi-text-left me-1"></i>Descripción
                                </label>
                                <textarea class="form-control form-control-custom" id="descripcion" rows="2"
                                    placeholder="Descripción del producto" required readonly></textarea>
                            </div>

                            <!-- Cantidad En Stock -->
                            <div class="mb-3">
                                <label for="en-stock" class="form-label fw-semibold">
                                    <i class="bi bi-123 me-1"></i>En Stock
                                </label>
                                <input type="number" class="form-control form-control-custom" id="en-stock"
                                    min="1" placeholder="0" readonly required>
                            </div>

                            <!-- Cantidad -->
                            <div class="mb-3">
                                <label for="cantidad" class="form-label fw-semibold">
                                    <i class="bi bi-123 me-1"></i>Cantidad
                                </label>
                                <input type="number" class="form-control form-control-custom" id="cantidad"
                                    min="1" placeholder="0" required>
                            </div>

                            <!-- De Ubicación -->
                            <div class="mb-3">
                                <label for="deUbicacion" class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt me-1"></i>De Ubicación
                                </label>
                                <select class="form-select form-control-custom" id="deUbicacion" required>
                                    <option value="">Seleccionar ubicación origen</option>
                                </select>
                            </div>

                            <!-- A Ubicación -->
                            <div class="mb-4">
                                <label for="aUbicacion" class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt-fill me-1"></i>A Ubicación
                                </label>
                                <select class="form-select form-control-custom" id="aUbicacion" required>
                                    <option value="">Seleccionar ubicación destino</option>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-add">
                                    <i class="bi bi-plus-lg me-2"></i>Agregar a Lista
                                </button>
                                <button type="button" class="btn btn-clear" onclick="clearForm()">
                                    <i class="bi bi-eraser me-2"></i>Limpiar Formulario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ===== TABLA DE TRANSFERENCIAS ===== -->
            <div class="col-lg-7 col-xl-8">
                <div class="card table-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul me-2"></i>Lista de Transferencias
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-process" onclick="processTransfers()" disabled id="btnProcess">
                                <i class="bi bi-check-circle me-2"></i>Procesar Todo
                            </button>
                            <button class="btn btn-clear-table" onclick="clearTable()">
                                <i class="bi bi-trash me-2"></i>Limpiar Lista
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th width="10%">#</th>
                                        <th width="12%">Art. #</th>
                                        <th width="15%">Código</th>
                                        <th width="25%">Descripción</th>
                                        <th width="8%">Cant.</th>
                                        <th width="12%">De</th>
                                        <th width="12%">A</th>
                                        <th width="6%">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="transfersTableBody">
                                    <tr class="empty-state">
                                        <td colspan="8" class="text-center py-5">
                                            <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">No hay transferencias pendientes</p>
                                            <small class="text-muted">Agregue artículos usando el formulario</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-sm-6">
                                <small class="text-muted">
                                    Total de items: <span id="totalItems" class="fw-bold">0</span>
                                </small>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <small class="text-muted">
                                    Cantidad total: <span id="totalQuantity" class="fw-bold">0</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/transferencia-de-stock.js"></script>
</body>
</html>