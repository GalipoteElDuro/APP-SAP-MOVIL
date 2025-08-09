<?php
session_start();
header('Content-Type: application/json');

// Objeto de respuesta por defecto
$response = ['success' => false, 'message' => 'Petición inválida.'];

// 1. VALIDACIÓN INICIAL
// ==================================================
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    $response['message'] = 'Método no permitido.';
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION['sap_session_id'])) {
    http_response_code(401); // Unauthorized
    $response['message'] = 'Error: No ha iniciado sesión.';
    echo json_encode($response);
    exit;
}

// Limpieza de parámetros
$barcode = trim($_GET['barcode'] ?? '');
$warehouse = trim($_GET['warehouse'] ?? '');

if (empty($barcode) || empty($warehouse)) {
    http_response_code(400); // Bad Request
    $response['message'] = 'El código de barras y el almacén son obligatorios.';
    echo json_encode($response);
    exit;
}

// Validar formato del warehouse (debe ser 2 dígitos)
if (!preg_match('/^[0-9]{2}$/', $warehouse)) {
    http_response_code(400); // Bad Request
    $response['message'] = 'Formato de almacén inválido. Debe ser 2 dígitos.';
    echo json_encode($response);
    exit;
}

// 2. PREPARAR Y EJECUTAR LA PETICIÓN A SAP
// ==================================================
$sessionId = $_SESSION['sap_session_id'];

// CORRECCIÓN: Usar el nombre correcto del campo en SAP B1
// En SAP B1, los códigos de barras pueden estar en diferentes campos dependiendo de la configuración
// Campos comunes: BarCode, CodeBars, ItemCode, o campos personalizados U_*

// Preparamos los valores para los parámetros OData
$select_fields = "ItemCode,ItemName,ItemWarehouseInfoCollection";

// CORRECCIÓN: Intentar múltiples campos de código de barras
// Primero intentamos con BarCode, luego con CodeBars si falla
$possible_barcode_fields = ['BarCode', 'CodeBars'];
$item_found = null;
$http_code = 0;
$last_error = '';

foreach ($possible_barcode_fields as $barcode_field) {
    // Escapar correctamente el valor del código de barras
    $escaped_barcode = str_replace("'", "''", $barcode);
    $filter_string = "{$barcode_field} eq '{$escaped_barcode}'";
    
    // CORRECCIÓN: Usar rawurlencode para los parámetros OData
    $queryParams = '$select=' . rawurlencode($select_fields) . 
                   '&$filter=' . rawurlencode($filter_string);
    
    $apiUrl = "https://acv.b1.do:50000/b1s/v2/Items?" . $queryParams;
    
    // Log para debug (comentar en producción)
    error_log("Intentando con campo: {$barcode_field}");
    error_log("URL generada: " . $apiUrl);
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Cookie: B1SESSION=' . $sessionId
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $sap_response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // 3. PROCESAR LA RESPUESTA DE SAP
    // ==================================================
    if ($curl_error) {
        $last_error = 'Error de cURL: ' . $curl_error;
        continue;
    }
    
    if ($http_code === 200) {
        $sap_data = json_decode($sap_response_body, true);
        $items = $sap_data['value'] ?? [];
        
        if (!empty($items)) {
            $item_found = $items[0];
            break; // Encontramos el artículo, salir del loop
        }
    } else {
        // Log del error para debug
        error_log("Error SAP ({$http_code}) con campo {$barcode_field}: " . $sap_response_body);
        
        $error_response = json_decode($sap_response_body, true);
        $last_error = $error_response['error']['message']['value'] ?? "Error SAP ($http_code): Error desconocido";
    }
}

// Si no encontramos el artículo con ningún campo, intentamos buscarlo por ItemCode
if (!$item_found) {
    error_log("No encontrado con códigos de barras, intentando con ItemCode");
    
    $escaped_barcode = str_replace("'", "''", $barcode);
    $filter_string = "ItemCode eq '{$escaped_barcode}'";
    
    $queryParams = '$select=' . rawurlencode($select_fields) . 
                   '&$filter=' . rawurlencode($filter_string);
    
    $apiUrl = "https://acv.b1.do:50000/b1s/v2/Items?" . $queryParams;
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Cookie: B1SESSION=' . $sessionId
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $sap_response_body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if (!$curl_error && $http_code === 200) {
        $sap_data = json_decode($sap_response_body, true);
        $items = $sap_data['value'] ?? [];
        
        if (!empty($items)) {
            $item_found = $items[0];
        }
    }
}

// 4. PROCESAR RESULTADO FINAL
// ==================================================
if (!$item_found) {
    http_response_code(404); // Not Found
    $response['message'] = "No se encontró ningún artículo con el código: $barcode";
    echo json_encode($response);
    exit;
}

// Procesar el artículo encontrado
$itemCode = $item_found['ItemCode'];
$itemName = $item_found['ItemName'];
$stock = 0;

// Buscar el stock en el almacén correcto dentro de la colección
if (!empty($item_found['ItemWarehouseInfoCollection'])) {
    foreach ($item_found['ItemWarehouseInfoCollection'] as $wh_info) {
        if ($wh_info['WarehouseCode'] === $warehouse) {
            $stock = $wh_info['InStock'];
            break; // Encontramos el almacén, no es necesario seguir
        }
    }
}

// Devolver la respuesta exitosa
$response = [
    'success' => true,
    'itemCode' => $itemCode,
    'description' => $itemName,
    'stock' => $stock
];
echo json_encode($response);
?>