<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $admin = UserData::getById($_SESSION['user_id'] ?? 0);
    if (!$admin || (int)$admin->is_admin !== 1) throw new RuntimeException('Solo un administrador puede gestionar correlativos.');
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $sucursal = SucursalData::getById($data['sucursal_id'] ?? 0);
    if (!$sucursal || !(int)$sucursal->activo) throw new InvalidArgumentException('Selecciona una sucursal activa.');
    ComprobanteCorrelativoData::guardar((int)$sucursal->id, (string)($data['tipo_comprobante'] ?? ''), (string)($data['serie'] ?? ''), (string)$sucursal->codigo, (int)($data['ultimo_numero'] ?? 0));
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
