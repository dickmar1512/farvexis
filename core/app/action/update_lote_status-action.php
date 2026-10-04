<?php
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('No autorizado');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = trim($_POST['estado'] ?? '');
$reason = trim($_POST['motivo'] ?? '') ?: null;

if (!$id || $status === '') {
    $_SESSION['lote_error'] = 'Datos de lote incompletos.';
    header('Location: ./?view=lotes');
    exit;
}

try {
    LoteData::updateStatus($id, $status, $reason);
    $_SESSION['lote_success'] = 'Estado del lote actualizado correctamente.';
} catch (Throwable $e) {
    $_SESSION['lote_error'] = $e->getMessage();
}

header('Location: ./?view=lotes');
exit;
