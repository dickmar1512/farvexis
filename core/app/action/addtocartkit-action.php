<?php
/**
 * addtocartkit-action.php
 * Agrega un kit/paquete al carrito de venta vía AJAX.
 * Responde JSON.
 */
header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['idpaquete'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de paquete no proporcionado.']);
    exit;
}

$idpaquete = $_POST['idpaquete'];
$errors    = [];

$prodKit = Det_kit::getById($idpaquete);

if (empty($prodKit)) {
    echo json_encode(['status' => 'error', 'message' => 'El paquete no tiene productos configurados.']);
    exit;
}

// Verificar stock de todos los productos del kit
foreach ($prodKit as $prod) {
    $product = ProductData::getById($prod->idprod);
    if ($product->is_stock == 1 && $prod->cantidad > $product->stock) {
        $errors[] = "Sin stock suficiente para: " . $product->name;
    }
}

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode('; ', $errors)]);
    exit;
}

// Agregar al carrito de sesión
if (!isset($_SESSION['cart'])) {
    $cart = [];
    $i = 0;
    foreach ($prodKit as $prod) {
        $cart[$i] = [
            'product_id'      => $prod->idprod,
            'q'               => $prod->cantidad,
            'precio_unitario' => $prod->precio,
            'descripcion'     => '',
            'descuento'       => $prod->descuento,
            'idpaquete'       => $idpaquete
        ];
        $i++;
    }
    $_SESSION['cart'] = $cart;
} else {
    // El carrito ya existe: agregar los productos del kit
    $cart = $_SESSION['cart'];
    $nc   = count($cart);
    foreach ($prodKit as $prod) {
        $found = false;
        foreach ($cart as &$c) {
            if ($c['product_id'] == $prod->idprod && $c['idpaquete'] == $idpaquete) {
                $c['q'] += $prod->cantidad;
                $found   = true;
                break;
            }
        }
        unset($c);
        if (!$found) {
            $cart[$nc] = [
                'product_id'      => $prod->idprod,
                'q'               => $prod->cantidad,
                'precio_unitario' => $prod->precio,
                'descripcion'     => '',
                'descuento'       => $prod->descuento,
                'idpaquete'       => $idpaquete
            ];
            $nc++;
        }
    }
    $_SESSION['cart'] = $cart;
}

echo json_encode(['status' => 'success', 'message' => 'Paquete agregado al carrito.']);
?>
