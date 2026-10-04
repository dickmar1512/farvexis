<?php
header('Content-Type: application/json');

try {
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $required = ['descripcion', 'comprobante', 'importe'];
    foreach($required as $field) {
        if(empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    $gasto = new GastoData();
    $gasto->descripcion = $_POST['descripcion'];
    $gasto->comprobante = $_POST['comprobante'];
    $gasto->importe = (float)$_POST['importe'];
    $gasto->fecha = date('Y-m-d H:i:s');
    $gasto->usuario_id = $_SESSION['user_id'];
    
    if($gasto->add()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al guardar en base de datos');
    }

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;