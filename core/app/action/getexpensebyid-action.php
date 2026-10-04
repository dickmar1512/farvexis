<?php
header('Content-Type: application/json');

try {
    if(!isset($_GET['id'])) {
        throw new Exception('ID no proporcionado');
    }

    $id = (int)$_GET['id'];
    $gasto = GastoData::getById($id);
    
    if(!$gasto) {
        throw new Exception('Gasto no encontrado');
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $gasto->id,
            'descripcion' => $gasto->descripcion,
            'comprobante' => $gasto->comprobante,
            'importe' => $gasto->importe
        ]
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;