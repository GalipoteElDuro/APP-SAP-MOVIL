<?php
session_start();

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

            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- ===== OFFCANVAS MENU ===== -->
            <div class="offcanvas offcanvas-end offcanvas-corporate" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title fw-bold" id="offcanvasNavbarLabel">
                        <i class="bi bi-building me-2"></i>ALMACENES CRISTO VIENE
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                
                <div class="offcanvas-body"> 
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="dashboard.php">
                                <i class="bi bi-house me-2 fs-5"></i>INICIO
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold active fs-5" aria-current="page" href="#">
                                <i class="bi bi-truck me-2 fs-5"></i>TRASLADO DE STOCK
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="#">
                                <i class="bi bi-file-earmark-text me-2 fs-5"></i>INFORME DE RECEPCIÓN
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="#">
                                <i class="bi bi-box-seam me-2 fs-5"></i>INVENTARIO
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="#">
                                <i class="bi bi-x-circle me-2 fs-5"></i>AVERÍAS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold fs-5" href="#">
                                <i class="bi bi-tag me-2 fs-5"></i>PRECIOS
                            </a>
                        </li>
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
    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="dashboard.php" class="text-decoration-none">
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
                                    <option value="SUC001">Sucursal Centro</option>
                                    <option value="SUC002">Sucursal Norte</option>
                                    <option value="SUC003">Sucursal Sur</option>
                                    <option value="SUC004">Sucursal Este</option>
                                    <option value="SUC005">Sucursal Oeste</option>
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
                                       placeholder="Ej: ART001" required>
                            </div>

                            <!-- Código de Barras -->
                            <div class="mb-3">
                                <label for="codigoBarras" class="form-label fw-semibold">
                                    <i class="bi bi-upc-scan me-1"></i>Código de Barras
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-custom" id="codigoBarras" 
                                           placeholder="Escanear o escribir código">
                                    <button class="btn btn-scan" type="button">
                                        <i class="bi bi-upc-scan"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-3">
                                <label for="descripcion" class="form-label fw-semibold">
                                    <i class="bi bi-text-left me-1"></i>Descripción
                                </label>
                                <textarea class="form-control form-control-custom" id="descripcion" rows="2" 
                                          placeholder="Descripción del producto" required></textarea>
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
                                    <option value="A01">Almacén A - Estante 01</option>
                                    <option value="A02">Almacén A - Estante 02</option>
                                    <option value="A03">Almacén A - Estante 03</option>
                                    <option value="B01">Almacén B - Estante 01</option>
                                    <option value="B02">Almacén B - Estante 02</option>
                                    <option value="C01">Almacén C - Estante 01</option>
                                </select>
                            </div>

                            <!-- A Ubicación -->
                            <div class="mb-4">
                                <label for="aUbicacion" class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt-fill me-1"></i>A Ubicación
                                </label>
                                <select class="form-select form-control-custom" id="aUbicacion" required>
                                    <option value="">Seleccionar ubicación destino</option>
                                    <option value="A01">Almacén A - Estante 01</option>
                                    <option value="A02">Almacén A - Estante 02</option>
                                    <option value="A03">Almacén A - Estante 03</option>
                                    <option value="B01">Almacén B - Estante 01</option>
                                    <option value="B02">Almacén B - Estante 02</option>
                                    <option value="C01">Almacén C - Estante 01</option>
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
    
    <!-- Script personalizado -->
    <script>
        let transfersList = [];
        let itemCounter = 1;

        // Función para agregar transferencia
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                id: itemCounter++,
                numeroArticulo: document.getElementById('numeroArticulo').value,
                codigoBarras: document.getElementById('codigoBarras').value,
                descripcion: document.getElementById('descripcion').value,
                cantidad: parseInt(document.getElementById('cantidad').value),
                deUbicacion: document.getElementById('deUbicacion').value,
                aUbicacion: document.getElementById('aUbicacion').value
            };

            // Validar que las ubicaciones sean diferentes
            if (formData.deUbicacion === formData.aUbicacion) {
                alert('La ubicación de origen y destino no pueden ser iguales');
                return;
            }

            transfersList.push(formData);
            updateTable();
            clearForm();
            
            // Mostrar mensaje de éxito
            showSuccessMessage('Artículo agregado correctamente');
        });

        // Función para actualizar la tabla
        function updateTable() {
            const tbody = document.getElementById('transfersTableBody');
            
            if (transfersList.length === 0) {
                tbody.innerHTML = `
                    <tr class="empty-state">
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">No hay transferencias pendientes</p>
                            <small class="text-muted">Agregue artículos usando el formulario</small>
                        </td>
                    </tr>
                `;
                document.getElementById('btnProcess').disabled = true;
            } else {
                tbody.innerHTML = transfersList.map((item, index) => `
                    <tr>
                        <td><span class="badge bg-secondary">${index + 1}</span></td>
                        <td><strong>${item.numeroArticulo}</strong></td>
                        <td><code>${item.codigoBarras}</code></td>
                        <td>${item.descripcion}</td>
                        <td><span class="badge bg-info">${item.cantidad}</span></td>
                        <td><small class="text-muted">${item.deUbicacion}</small></td>
                        <td><small class="text-success">${item.aUbicacion}</small></td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${item.id})" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
                document.getElementById('btnProcess').disabled = false;
            }
            
            updateCounters();
        }

        // Función para actualizar contadores
        function updateCounters() {
            document.getElementById('totalItems').textContent = transfersList.length;
            document.getElementById('totalQuantity').textContent = 
                transfersList.reduce((sum, item) => sum + item.cantidad, 0);
        }

        // Función para eliminar item
        function removeItem(id) {
            transfersList = transfersList.filter(item => item.id !== id);
            updateTable();
            showSuccessMessage('Artículo eliminado de la lista');
        }

        // Función para limpiar formulario
        function clearForm() {
            document.getElementById('transferForm').reset();
        }

        // Función para limpiar tabla
        function clearTable() {
            if (transfersList.length > 0) {
                if (confirm('¿Está seguro de que desea limpiar toda la lista?')) {
                    transfersList = [];
                    updateTable();
                    showSuccessMessage('Lista limpiada correctamente');
                }
            }
        }

        // Función para procesar transferencias
        function processTransfers() {
            if (transfersList.length === 0) return;
            
            if (confirm(`¿Procesar ${transfersList.length} transferencias?`)) {
                // Aquí iría la lógica para enviar al servidor
                console.log('Procesando transferencias:', transfersList);
                
                // Simular procesamiento
                showSuccessMessage(`${transfersList.length} transferencias procesadas correctamente`);
                transfersList = [];
                updateTable();
            }
        }

        // Función para mostrar mensajes
        function showSuccessMessage(message) {
            // Crear toast o alerta temporal
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alertDiv.style.cssText = 'top: 90px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Auto eliminar después de 3 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
        }

        // Función para cambiar sucursal
        function changeSucursal() {
            const sucursalSelect = document.getElementById('sucursalSelect');
            const selectedSucursal = sucursalSelect.value;
            
            if (selectedSucursal) {
                // Actualizar las ubicaciones basadas en la sucursal seleccionada
                updateUbicaciones(selectedSucursal);
                
                // Mostrar mensaje de cambio
                const sucursalText = sucursalSelect.options[sucursalSelect.selectedIndex].text;
                showSuccessMessage(`Sucursal cambiada a: ${sucursalText}`);
                
                // Limpiar formulario si hay datos
                if (document.getElementById('numeroArticulo').value) {
                    if (confirm('¿Desea limpiar el formulario actual al cambiar de sucursal?')) {
                        clearForm();
                    }
                }
            }
        }

        // Función para actualizar ubicaciones según la sucursal
        function updateUbicaciones(sucursal) {
            const deUbicacion = document.getElementById('deUbicacion');
            const aUbicacion = document.getElementById('aUbicacion');
            
            // Limpiar opciones actuales
            deUbicacion.innerHTML = '<option value="">Seleccionar ubicación origen</option>';
            aUbicacion.innerHTML = '<option value="">Seleccionar ubicación destino</option>';
            
            let ubicaciones = [];
            
            // Definir ubicaciones por sucursal
            switch(sucursal) {
                case 'SUC001': // Centro
                    ubicaciones = [
                        {value: 'C-A01', text: 'Centro - Almacén A - Estante 01'},
                        {value: 'C-A02', text: 'Centro - Almacén A - Estante 02'},
                        {value: 'C-A03', text: 'Centro - Almacén A - Estante 03'},
                        {value: 'C-B01', text: 'Centro - Almacén B - Estante 01'},
                        {value: 'C-B02', text: 'Centro - Almacén B - Estante 02'}
                    ];
                    break;
                case 'SUC002': // Norte
                    ubicaciones = [
                        {value: 'N-A01', text: 'Norte - Almacén A - Estante 01'},
                        {value: 'N-A02', text: 'Norte - Almacén A - Estante 02'},
                        {value: 'N-B01', text: 'Norte - Almacén B - Estante 01'},
                        {value: 'N-C01', text: 'Norte - Almacén C - Estante 01'}
                    ];
                    break;
                case 'SUC003': // Sur
                    ubicaciones = [
                        {value: 'S-A01', text: 'Sur - Almacén A - Estante 01'},
                        {value: 'S-A02', text: 'Sur - Almacén A - Estante 02'},
                        {value: 'S-B01', text: 'Sur - Almacén B - Estante 01'}
                    ];
                    break;
                case 'SUC004': // Este
                    ubicaciones = [
                        {value: 'E-A01', text: 'Este - Almacén A - Estante 01'},
                        {value: 'E-A02', text: 'Este - Almacén A - Estante 02'},
                        {value: 'E-B01', text: 'Este - Almacén B - Estante 01'},
                        {value: 'E-B02', text: 'Este - Almacén B - Estante 02'}
                    ];
                    break;
                case 'SUC005': // Oeste
                    ubicaciones = [
                        {value: 'O-A01', text: 'Oeste - Almacén A - Estante 01'},
                        {value: 'O-A02', text: 'Oeste - Almacén A - Estante 02'},
                        {value: 'O-B01', text: 'Oeste - Almacén B - Estante 01'}
                    ];
                    break;
            }
            
            // Agregar las opciones a ambos selects
            ubicaciones.forEach(ubicacion => {
                const optionDe = new Option(ubicacion.text, ubicacion.value);
                const optionA = new Option(ubicacion.text, ubicacion.value);
                deUbicacion.add(optionDe);
                aUbicacion.add(optionA);
            });
        }

        // Validar sucursal al enviar formulario
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            const sucursalSelect = document.getElementById('sucursalSelect');
            if (!sucursalSelect.value) {
                e.preventDefault();
                alert('Por favor seleccione una sucursal antes de agregar transferencias');
                sucursalSelect.focus();
                return;
            }
        });
    </script>
</body>

</html>