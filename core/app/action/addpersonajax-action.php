<?php
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No session']);
    exit;
}

if (!isset($_POST['role'])) {
    echo json_encode(['success' => false, 'message' => 'Rol no especificado']);
    exit;
}

$role = $_POST['role']; // 'cliente' o 'medico'
$nombres = trim($_POST['nombres'] ?? '');
$apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
$numero_documento = trim($_POST['numero_documento'] ?? '');
$tipo_documento_id = isset($_POST['tipo_documento_id']) ? (int)$_POST['tipo_documento_id'] : 1;
$cmp = trim($_POST['cmp'] ?? '');

$conn = Database::getCon();

if ($nombres === '') {
    echo json_encode(['success' => false, 'message' => 'Nombre es requerido']);
    exit;
}

// Check if person exists by doc
$persona_id = null;
if ($numero_documento !== '') {
    $stmt = $conn->prepare("SELECT id FROM persona WHERE numero_documento = ?");
    $stmt->bind_param("s", $numero_documento);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $persona_id = $row['id'];
    }
}

if (!$persona_id) {
    // Insert persona
    $stmt = $conn->prepare("INSERT INTO persona (tipo_documento_id, numero_documento, nombres, apellido_paterno, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $tipo_documento_id, $numero_documento, $nombres, $apellido_paterno);
    $stmt->execute();
    $persona_id = $conn->insert_id;
}

if ($role === 'cliente') {
    // Check if already client
    $stmt = $conn->prepare("SELECT id FROM cliente WHERE persona_id = ? AND kind = 1");
    $stmt->bind_param("i", $persona_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        $conn->query("INSERT INTO cliente (persona_id, tipo_cliente, kind) VALUES ($persona_id, 1, 1)");
    }
    echo json_encode(['success' => true, 'id' => $persona_id, 'name' => $nombres . ' ' . $apellido_paterno, 'doc' => $numero_documento]);
} elseif ($role === 'medico') {
    $stmt = $conn->prepare("SELECT id FROM medico WHERE persona_id = ?");
    $stmt->bind_param("i", $persona_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        $stmt = $conn->prepare("INSERT INTO medico (persona_id, cmp) VALUES (?, ?)");
        $stmt->bind_param("is", $persona_id, $cmp);
        $stmt->execute();
    }
    echo json_encode(['success' => true, 'id' => $persona_id, 'name' => $nombres . ' ' . $apellido_paterno, 'cmp' => $cmp]);
} else {
    echo json_encode(['success' => false, 'message' => 'Rol inválido']);
}
?>
