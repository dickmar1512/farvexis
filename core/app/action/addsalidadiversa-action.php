<?php
/**
 * Procesa la Salida Diversa, registra el documento y resta stock.
 */
if(count($_POST) > 0 && isset($_SESSION["cart_sd"]) && count($_SESSION["cart_sd"]) > 0) {
    
    try {
        $cart = $_SESSION["cart_sd"];
        $user_id = $_SESSION["user_id"];
        $serie = $_POST["serie"];
        $comprobante = $_POST["comprobante"];
        $observacion = $_POST["observacion"];
        $fecha_actual = date("Y-m-d H:i:s");

        // 1. Crear Registro en SELL (como documento interno)
        $sell = new SellData();
        $sell->person_id = $user_id;
        $sell->operacion_type_id = 2;
        $sell->user_id = $user_id;
        $sell->tipo_comprobante = 65; // Salida Diversa
        $sell->serie = $serie;
        $sell->comprobante = $comprobante;
        $sell->total = 0; // No tiene valor monetario de venta
        $sell->cash = 0;
        $sell->discount = 0;
        $sell->person_id = "NULL"; // Sin cliente específico
        $sell->created_at = $fecha_actual;
        $sell->estado = 1;
        $sell->tipo_pago = 1;
        $sell->observacion = $observacion;

        // Necesitamos un método add_sd para insertar con observación y sin person_id
        $s = $sell->add_sd();

        if($s[0]) {
            $sell_id = $s[1];

            // 2. Procesar cada producto
            foreach($cart as $item) {
                $product_id = $item["product_id"];
                $q = $item["q"];
                
                $product = ProductData::getById($product_id);
                
                // Registro de Operación (Inventory Movement)
                $op = new OperationData();
                $op->product_id = $product_id;
                $op->operation_type_id = 2; // Salida
                $op->sell_id = $sell_id;
                $op->q = $q;
                $op->prec_alt = 0;
                $op->cu = $product->price_in; // Costo unitario para reportes
                $op->descuento = 0;
                $op->created_at = $fecha_actual;
                $op->idpaquete = "X";
                $op->descripcion = "SALIDA DIVERSA: " . $observacion;
                $op->add();
            }

            unset($_SESSION["cart_sd"]);
            echo json_encode(array("status" => "success", "message" => "Salida diversa registrada correctamente. Documento: $serie-$comprobante"));
        } else {
            echo json_encode(array("status" => "error", "message" => "Error al registrar en la base de datos."));
        }

    } catch (Exception $e) {
        echo json_encode(array("status" => "error", "message" => $e->getMessage()));
    }

} else {
    echo json_encode(array("status" => "error", "message" => "No hay productos en la lista o datos inválidos."));
}
?>
