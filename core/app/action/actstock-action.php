<?php
/**
 * actstock-action.php
 * Sincronización Maestra de Inventario
 * Recalcula el stock de todos los productos basándose en el historial de operaciones y responde en JSON.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    $con = Database::getCon();

    $sql_sync = "UPDATE product p
                 SET p.stock = (
                    SELECT COALESCE(SUM(CASE 
                        WHEN op.operation_type_id = 1 THEN op.q 
                        WHEN op.operation_type_id = 2 THEN -op.q 
                        ELSE 0 
                    END), 0)
                    FROM operation op
                    WHERE op.product_id = p.id AND op.estado = 1
                 )
                 WHERE p.is_active = 1";

    $res = $con->query($sql_sync);

    echo json_encode([
        "status"  => "success",
        "message" => "Inventario recalculado y sincronizado con éxito."
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Error al sincronizar inventario: " . $e->getMessage()
    ]);
    exit;
}
