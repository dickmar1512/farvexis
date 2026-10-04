<?php
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new RuntimeException('Sesión no válida.');
    }
    $admin = UserData::getById($_SESSION['user_id']);
    if (!$admin || (int)$admin->is_admin !== 1) {
        throw new RuntimeException('Solo un administrador puede gestionar sucursales.');
    }

    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $nombre = trim((string)($data['nombre'] ?? ''));
    $codigo = trim((string)($data['codigo'] ?? ''));
    if ($nombre === '' || !preg_match('/^\d{1,4}$/', $codigo)) {
        throw new InvalidArgumentException('Indica un nombre y un código de local de 1 a 4 dígitos.');
    }

    $sucursal = new SucursalData();
    $sucursal->id = (int)($data['id'] ?? 0);
    $sucursal->codigo = $codigo;
    $sucursal->nombre = $nombre;
    $sucursal->direccion = $data['direccion'] ?? '';
    $sucursal->activo = isset($data['activo']) ? 1 : 0;
    $sucursal->save();

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
