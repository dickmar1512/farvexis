<?php
/**
 * obtener_datos_boleta_ajax-action.php
 * Devuelve el detalle de una boleta para notas de crédito/débito.
 * Reemplaza obtener_datos_boleta_ajax.php de la raíz.
 * Usa Database::getCon() en lugar de conexion.php/funciones.php.
 */
if (!isset($_POST['numDoc'])) exit;

$numDoc = Database::getCon()->real_escape_string($_POST['numDoc']);
$tipo   = $_POST['tipo'] ?? '';

$sql = "SELECT d.* FROM det d
        INNER JOIN boleta f ON (f.id = d.ID_TIPO_DOC)
        WHERE d.TIPO_DOC = 1
        AND CONCAT(f.SERIE,'-',f.COMPROBANTE) = '{$numDoc}'";

$result = Database::getCon()->query($sql);
$dato   = [];
while ($row = $result->fetch_assoc()) {
    $dato[] = $row;
}

if ($tipo === '03') {
    include __DIR__ . '/partials/datos_boleta_descripcion.php';
} elseif ($tipo === '05') {
    include __DIR__ . '/partials/datos_boleta_descuento.php';
} elseif ($tipo === '07') {
    include __DIR__ . '/partials/datos_boleta_devolucion.php';
}
?>
