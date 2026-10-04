<?php
/**
 * deleteoperation-action.php
 * Elimina una operación y responde en formato JSON o redirige.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    $opid = $_POST["opid"] ?? ($_GET["opid"] ?? null);
    if (!$opid) {
        throw new Exception("ID de operación no proporcionado.");
    }

    $operation = OperationData::getById($opid);
    if (!$operation) {
        throw new Exception("Operación no encontrada.");
    }

    $operation->del();

    echo json_encode([
        "status"  => "success",
        "message" => "Operación eliminada con éxito.",
        "opid"    => $opid
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
