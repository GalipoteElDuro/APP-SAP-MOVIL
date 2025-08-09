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

$barcode = $_GET['barcode'] ?? null;
$warehouse = $_GET['warehouse'] ?? null;

if (empty($barcode) || empty($warehouse)) {
    http_response_code(400); // Bad Request
    $response['message'] = 'El código de barras y el almacén son obligatorios.';
    echo json_encode($response);
    exit;
}

// 2. PREPARAR Y EJECUTAR LA PETICIÓN A SAP
// ==================================================
$sessionId = $_SESSION['sap_session_id'];

// Preparamos los valores para los parámetros OData
$select_fields = "ItemCode,ItemName,ItemWarehouseInfoCollection";
$filter_string = "BarCode eq '" . $barcode . "'";

// Construimos la URL final, codificando correctamente los parámetros
$baseUrl = "https://acv.b1.do:50000/b1s/v2/Items";
$queryParams = http_build_query([
    '\$select' => $select_fields,
    '\$filter' => $filter_string
]);

$apiUrl = $baseUrl . '?' . $queryParams;

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Cookie: B1SESSION=' . $sessionId
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$sap_response_body = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// 3. PROCESAR LA RESPUESTA DE SAP
// ==================================================
if ($curl_error) {
    http_response_code(500);
    $response['message'] = 'Error de cURL: ' . $curl_error;
    echo json_encode($response);
    exit;
}

if ($http_code === 200) {
    $sap_data = json_decode($sap_response_body, true);
    $items = $sap_data['value'] ?? [];

    if (empty($items)) {
        http_response_code(404); // Not Found
        $response['message'] = "No se encontró ningún artículo con el código de barras: $barcode";
        echo json_encode($response);
        exit;
    }

    // Asumimos que el código de barras es único y tomamos el primer resultado
    $item = $items[0];
    $itemCode = $item['ItemCode'];
    $itemName = $item['ItemName'];
    $stock = 0;

    // Buscar el stock en el almacén correcto dentro de la colección
    if (!empty($item['ItemWarehouseInfoCollection'])) {
        foreach ($item['ItemWarehouseInfoCollection'] as $wh_info) {
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

} else {
    // Error desde SAP
    http_response_code($http_code);
    $error_response = json_decode($sap_response_body, true);
    $error_message = $error_response['error']['message']['value'] ?? 'Error desconocido en SAP.';
    $response['message'] = "Error SAP ($http_code): " . $error_message;
    echo json_encode($response);
}
?>
