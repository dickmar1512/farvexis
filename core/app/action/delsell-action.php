<?php
/**
 * delsell-action.php
 * Cancela una venta u orden y devuelve respuesta en formato JSON.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    $id = $_POST["id"] ?? ($_GET["id"] ?? null);
    if (!$id) {
        throw new Exception("ID de venta no especificado.");
    }

    $sell = SellData::getById($id);
    if (!$sell) {
        throw new Exception("Venta no encontrada.");
    }

    $operations = OperationData::getAllProductsBySellId($id);
    if (!empty($operations)) {
        foreach ($operations as $op) {
            $op->cancel();
        }
    }

    $sell->cancel();

    echo json_encode([
        "status"  => "success",
        "message" => "Venta cancelada exitosamente.",
        "id"      => $id
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
