<?php
/**
 * finishcut-action.php
 * Finaliza el corte de caja actual vía AJAX.
 * Responde JSON.
 */
header('Content-Type: application/json; charset=utf-8');

$current = CutData::getCurrent();

if ($current != null) {
    $current->update();
    echo json_encode(['status' => 'success', 'message' => 'Corte de caja finalizado correctamente.', 'redirect' => './?view=box']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No hay un corte de caja activo para finalizar.']);
}
?>
