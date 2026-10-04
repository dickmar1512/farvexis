<?php
header('Content-Type: application/json');

try {
    $numDocUsuario = $_POST['numDocUsuario'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    // Validar fechas
    if ($numDocUsuario == '' || $tipo == '') {
        throw new Exception('Debe ingresar el número de documento y el tipo');
    }

    $dato = PersonData::datosbyDocumento($numDocUsuario, $tipo);
    
    echo json_encode([
        'success' => true,
        'data' => $dato
    ]);
    exit;

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
