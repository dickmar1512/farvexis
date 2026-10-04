<?php
// Suponiendo que ya tienes una conexión a la base de datos y una sesión iniciada si es necesario

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos enviados por AJAX
    $paymentId = isset($_POST['paymentId']) ? intval($_POST['paymentId']) : 0;
    $paymentType = isset($_POST['paymentType']) ? intval($_POST['paymentType']) : 0;
    $importepp = isset($_POST['importepp']) ? floatval($_POST['importepp']) : 0;

    // Validar que los datos sean correctos (ejemplo básico)
    if ($paymentId <= 0 || $paymentType < 1 || $paymentType > 5) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    // Actualizar en la base de datos (ejemplo con PDO)
    try {
        $sell = SellData::getById($paymentId);
        $sell->tipo_pago = $paymentType;
        $sell->update_tipoPago();
        $sell->importepp = $importepp;
        $sell->id = $paymentId;
        $sell->update_pagoParcial();
        echo json_encode(['success' => true, 'message' => 'El tipo de pago ha sido actualizado correctamente']);
        exit;
        // Verificar si se actualizó correctamente
        // if ($stmt->rowCount() > 0) {
        //     echo json_encode(['success' => true, 'message' => 'Tipo de pago actualizado']);
        // } else {
        //     echo json_encode(['success' => false, 'message' => 'No se encontró la venta o no hubo cambios']);
        // }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}
?>