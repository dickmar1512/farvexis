<?php
/**
 * clearcart2-action.php
 * Elimina un producto del carrito de repuestos (session cart2) vía AJAX/GET.
 * Responde JSON.
 */
header('Content-Type: application/json; charset=utf-8');

$orden_id = $_GET['orden_id'] ?? '';

if (isset($_GET['product_id'])) {
    if (isset($_SESSION['cart2'])) {
        $cart = $_SESSION['cart2'];
        if (count($cart) == 1) {
            unset($_SESSION['cart2']);
        } else {
            $ncart = [];
            $nx = 0;
            foreach ($cart as $c) {
                if ($c['product_id'] != $_GET['product_id']) {
                    $ncart[$nx] = $c;
                }
                $nx++;
            }
            $_SESSION['cart2'] = $ncart;
        }
    }
    echo json_encode(['status' => 'success', 'message' => 'Producto eliminado del carrito.', 'orden_id' => $orden_id]);
} else {
    // Limpiar todo el carrito
    unset($_SESSION['cart2']);
    echo json_encode(['status' => 'success', 'message' => 'Carrito vaciado.', 'orden_id' => $orden_id]);
}
?>
