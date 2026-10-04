<?php
/**
 * delproduct-action.php
 * Desactiva un producto y responde en formato JSON.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    $id = $_POST["id"] ?? ($_GET["id"] ?? null);
    if (!$id) {
        throw new Exception("ID no especificado.");
    }

    $product = ProductData::getById($id);
    if (!$product) {
        throw new Exception("Producto no encontrado.");
    }

    $product->del();

    echo json_encode([
        "status"  => "success",
        "message" => "Producto desactivado exitosamente."
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}
