<?php
header('Content-Type: application/json');

try {
    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
    $usuario_id = (int)($_GET['usuario_id'] ?? 0);

    // Validar fechas
    if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
        throw new Exception('La fecha fin no puede ser menor a la fecha inicio');
    }

    $gastos = GastoData::getByFilters($fecha_inicio, $fecha_fin, $usuario_id);
    
    echo json_encode([
        'success' => true,
        'data' => array_map(function($gasto) {
            return [
                'id' => $gasto->id,
                'descripcion' => $gasto->descripcion,
                'comprobante' => $gasto->comprobante,
                'importe' => (float)$gasto->importe,
                'fecha' => $gasto->fecha,
                'usuario' => $gasto->getUser()->username
            ];
        }, $gastos)
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;