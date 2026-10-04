<?php
/**
 * obtener_nombre_producto_ajax-action.php
 * Busca un producto por código de barras y devuelve su nombre como HTML partial.
 * Reemplaza obtener_nombre_producto_ajax.php de la raíz.
 * Usa Database::getCon() en lugar de conexion.php/funciones.php.
 */
if (!isset($_POST['cod'])) exit;

$cod   = Database::getCon()->real_escape_string(trim($_POST['cod']));
$input = $_POST['cod_input_nuevo'] ?? '';

$result = Database::getCon()->query("SELECT id, name, price_out FROM product WHERE barcode = '{$cod}' LIMIT 1");
$dato   = null;
if ($result && $result->num_rows > 0) {
    $dato = $result->fetch_assoc();
}

include __DIR__ . '/partials/datos_producto_nuevo.php';
?>
