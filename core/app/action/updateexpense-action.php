<?php
header('Content-Type: application/json');

try {
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $required = ['id', 'descripcion', 'comprobante', 'importe'];
    foreach($required as $field) {
        if(empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    $gasto = GastoData::getById($_POST['id']);
    if(!$gasto) {
        throw new Exception('Gasto no encontrado');
    }

    $gasto->descripcion = $_POST['descripcion'];
    $gasto->comprobante = $_POST['comprobante'];
    $gasto->usuario_id = $_SESSION['user_id'];
    $gasto->importe = (float)$_POST['importe'];
    
    if($gasto->update()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al actualizar en base de datos');
    }

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;