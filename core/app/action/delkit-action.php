<?php
/**
 * delkit-action.php
 * Cambia el estado de un kit/paquete y responde en JSON.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    $est       = $_POST["est"] ?? ($_GET["est"] ?? null);
    $idpaq     = $_POST["id"] ?? ($_GET["id"] ?? null);
    $fecha_fin = $_POST["fecha"] ?? ($_GET["fecha"] ?? date("Y-m-d"));

    if (!$idpaq) {
        throw new Exception("ID de paquete no especificado.");
    }

    $op = new KitData();
    $op->estado      = $est;
    $op->fecha_fin   = $fecha_fin;
    $op->idpaquete   = $idpaq;
    $op->updateestado();

    echo json_encode([
        "status"  => "success",
        "message" => "Estado de paquete actualizado exitosamente."
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
