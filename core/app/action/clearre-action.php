<?php
/**
 * clearre-action.php
 * Elimina un producto del carrito de reabastecimiento (session reabastecer) vía AJAX.
 * Responde JSON.
 */
header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['product_id'])) {
    if (isset($_SESSION['reabastecer'])) {
        $cart = $_SESSION['reabastecer'];
        if (count($cart) == 1) {
            unset($_SESSION['reabastecer']);
        } else {
            $ncart = [];
            foreach ($cart as $c) {
                if ($c['product_id'] != $_GET['product_id']) {
                    $ncart[] = $c;
                }
            }
            $_SESSION['reabastecer'] = $ncart;
        }
    }
    echo json_encode(['status' => 'success', 'message' => 'Producto eliminado del carrito.']);
} else {
    // Limpiar todo el carrito
    unset($_SESSION['reabastecer']);
    echo json_encode(['status' => 'success', 'message' => 'Carrito vaciado.']);
}
?>
