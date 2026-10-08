<?php
/**
 * processre-action.php
 * Procesa el formulario de reabastecimiento (ingreso de mercadería).
 * Responde JSON para consumo AJAX.
 */
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['reabastecer']) || empty($_SESSION['reabastecer'])) {
    echo json_encode(['status' => 'error', 'message' => 'No hay productos en el carrito de reabastecimiento.']);
    exit;
}

if (count($_POST) > 0) {
    $tipo_comprobante = $_POST['optTipoComprobante'] ?? '';
    $serie            = $_POST['serie']            ?? '';
    $comprobante      = $_POST['comprobante']      ?? '';
    $fecemi           = $_POST['fecemi']           ?? '';
    $total            = $_POST['total']            ?? 0;
    $cash             = $_POST['money']            ?? 0;

    $cart = $_SESSION['reabastecer'];
    
    if ($tipo_comprobante == '60') {
        $db = Database::getCon();
        $sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
        $sucursalObj = SucursalData::getById($sucursalId);
        $codLocalEmisor = $sucursalObj ? $sucursalObj->codigo : '0000';
        $serie_defecto = ComprobanteCorrelativoData::getSerieDefecto('60', $codLocalEmisor);
        
        // Si el usuario no mandó una serie distinta, usamos la de defecto y guardamos el correlativo incremental
        if (empty($serie) || $serie === 'I001') {
            $serie = $serie_defecto;
        }
        $comprobante = ComprobanteCorrelativoData::siguiente($db, '60', $serie, $codLocalEmisor, $sucursalId);
    }

    if (count($cart) > 0) {
        $sell = new SellData();
        $sell->user_id          = $_SESSION['user_id'];
        $sell->tipo_comprobante = $tipo_comprobante;
        $sell->serie            = $serie;
        $sell->comprobante      = $comprobante;
        $sell->fecha_emi        = $fecemi;
        $sell->total            = $total;
        $sell->cash             = $cash;

        if (isset($_POST['client_id']) && $_POST['client_id'] != '') {
            $sell->person_id = $_POST['client_id'];
            $s = $sell->add_re_with_client();
        } else {
            $s = $sell->add_re2();
        }

        foreach ($cart as $c) {
            $op  = new OperationData();
            $op2 = new ProductData();
            $op->product_id       = $c['product_id'];

            $product = ProductData::getById($c['product_id']);
            $op->cu               = $c['price_in'];
            $op->prec_alt         = $c['price_in'];
            $op->operation_type_id = 1; // 1 - entrada
            $op->sell_id          = $s[1];
            $op->descuento        = 0;
            $op->q                = $c['q'];

            if ($tipo_comprobante == 60) {
                $op->descripcion = 'INGRESO DIVERSO: Por Inventario de produtos';
            }

            $fecha_actual   = date('Y-m-d H:i:s');
            $op->created_at = $fecha_actual;

            $op2->id            = $c['product_id'];
            $op2->reg_san       = $c['rs'];
            $op2->laboratorio   = $c['labo'];
            $op2->price_in      = $c['price_in'];

            $anaquel = addslashes($c['anaquel'] ?? '');
            $is_controlled = intval($c['is_controlled'] ?? 0);
            Executor::doit("UPDATE product SET is_controlled = $is_controlled, anaquel = '$anaquel' WHERE id = " . $op->product_id);

            if (isset($_POST['is_oficial'])) {
                $op->is_oficial = 1;
            }

            $operationResult = $op->add();
            if (!$operationResult[0]) {
                throw new RuntimeException('No se pudo registrar el movimiento de reabastecimiento.');
            }
            $op2->update_cu();

            if ($product->is_stock == 1) {
                if ($c['fec_venc'] != '') {
                    $sql_venc = "UPDATE product SET fecha_venc = '" . $c['fec_venc'] . "' WHERE id = " . $op->product_id;
                    Executor::doit($sql_venc);
                }

                LoteData::registerEntry(
                    (int)$c['product_id'],
                    trim($c['nl'] ?? '') ?: 'S/L',
                    (float)$c['q'],
                    (int)$_SESSION['user_id'],
                    (int)$operationResult[1],
                    !empty($c['fec_venc']) ? $c['fec_venc'] : null,
                    !empty($c['fec_fab']) ? $c['fec_fab'] : null,
                    (float)$c['price_in'],
                    !empty($c['anaquel']) ? trim($c['anaquel']) : null,
                    (!empty($_POST['client_id'])) ? (int)$_POST['client_id'] : null
                );
            }
        }

        unset($_SESSION['reabastecer']);
        setcookie('selled', 'selled');

        echo json_encode(['status' => 'success', 'message' => 'Reabastecimiento registrado correctamente.', 'redirect' => './?view=onere&id=' . $s[1]]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'El carrito está vacío.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Petición no válida.']);
}
?>
