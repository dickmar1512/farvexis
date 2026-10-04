<?php
/**
 * processsell-action.php
 * Genera la venta tipo Nota de Venta (70), guarda operaciones
 * y responde en formato JSON para consumo vía AJAX.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION["cart"]) || count($_SESSION["cart"]) == 0) {
        throw new Exception("El carrito de compras está vacío.");
    }

    $cart = $_SESSION["cart"];

    foreach ($cart as $cartItem) {
        $cartProduct = ProductData::getById($cartItem["product_id"]);
        if ($cartProduct && (int)$cartProduct->is_stock === 1 &&
            !LoteData::canAllocate((int)$cartItem["product_id"], (float)$cartItem["q"])) {
            throw new Exception("No hay suficiente stock disponible en lotes para el producto: " . $cartProduct->name);
        }
    }

    $TIPO        = $_POST["TIPO"] ?? "70";
    $SERIE       = $_POST["SERIE"] ?? "0002";
    $COMPROBANTE = $_POST["COMPROBANTE"] ?? "1";
    $estado      = '1';
    $fecEmision  = $_POST["fecEmision"] ?? date("Y-m-d");
    $horEmision  = $_POST["horEmision"] ?? date("H:i:s");

    $num_succ = 0;
    $errors = [];

    foreach ($cart as $c) {
        $q = OperationData::getQYesF($c["product_id"]);

        if ($c["q"] <= $q) {
            if (isset($_POST["is_oficial"])) {
                $qyf = OperationData::getQYesF($c["product_id"]);
                if ($c["q"] <= $qyf) {
                    $num_succ++;
                } else {
                    $errors[] = [
                        "product_id" => $c["product_id"],
                        "message" => "No hay suficiente cantidad de producto para facturar en inventario."
                    ];
                }
            } else {
                $num_succ++;
            }
        } else {
            $errors[] = [
                "product_id" => $c["product_id"],
                "message" => "No hay suficiente cantidad de producto en inventario."
            ];
        }
    }

    if ($num_succ !== count($cart)) {
        $_SESSION["errors"] = $errors;
        $errMsg = "Stock insuficiente para uno o más productos.";
        if (count($errors) > 0) {
            $errMsg .= " (" . $errors[0]["message"] . ")";
        }
        throw new Exception($errMsg);
    }

    // Calcular total si no viene en POST
    $total = isset($_POST["total"]) ? floatval($_POST["total"]) : 0;
    if ($total <= 0) {
        foreach ($cart as $c) {
            $total += floatval($c["q"]) * floatval($c["precio_unitario"]);
        }
    }

    $discount = floatval($_POST["discount3"] ?? 0);
    $cash     = floatval($_POST["money3"] ?? $total);

    $sell = new SellData();
    $sell->user_id          = $_SESSION["user_id"];
    $sell->tipo_comprobante = 70;
    $sell->serie            = $SERIE;
    $sell->comprobante      = $COMPROBANTE;
    $sell->total            = round($total - $discount, 2);
    $sell->discount         = $discount;
    $sell->cash             = $cash;
    $sell->tipo_pago        = 1;
    $sell->person_id        = '7';
    $sell->estado           = $estado;
    $sell->created_at       = $fecEmision . ' ' . $horEmision;

    if (isset($_POST["client_id"]) && $_POST["client_id"] != "") {
        $sell->person_id = $_POST["client_id"];
        $s = $sell->add_with_client();
    } else {
        $s = $sell->add2();
    }

    $sell_id = $s[1];

    foreach ($cart as $c) {
        $op = new OperationData();
        $product = ProductData::getById($c["product_id"]);
        $op->product_id        = $c["product_id"];
        $op->operation_type_id = OperationTypeData::getByName("salida")->id;
        $op->sell_id           = $sell_id;
        $op->descripcion       = $c["descripcion"] ?? '';
        $op->cu                = $product->price_in ?? 0;
        $op->prec_alt          = $c["precio_unitario"];
        $op->descuento         = $c["descuento"] ?? 0;
        $op->idpaquete         = $c["idpaquete"] ?? "X";
        $op->q                 = $c["q"];
        $op->igv_tipo          = $c["igv_tipo"] ?? '20';
        $op->created_at        = date('Y-m-d H:i:s');

        if (isset($_POST["is_oficial"])) {
            $op->is_oficial = 1;
        }

        $operationResult = $op->add();
        if (!$operationResult[0]) {
            throw new Exception("No se pudo registrar un producto de la nota de venta.");
        }
        if ($product && (int)$product->is_stock === 1) {
            LoteData::allocateForOperation(
                (int)$operationResult[1],
                (int)$c["product_id"],
                (float)$op->q,
                (int)$_SESSION["user_id"]
            );
        }
    }

    unset($_SESSION["cart"]);
    setcookie("selled", "selled");

    echo json_encode([
        'status'   => 'success',
        'sell_id'  => $sell_id,
        'tipodoc'  => 70,
        'serie'    => $SERIE,
        'numero'   => $COMPROBANTE,
        'message'  => 'Nota de Venta registrada correctamente.',
        'redirect' => "./?view=ordenventa&id={$sell_id}"
    ]);
    exit();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
}
