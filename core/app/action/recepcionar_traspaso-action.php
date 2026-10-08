<?php
header('Content-Type: application/json');

if(empty($_POST["id"])){
    echo json_encode(["success" => false, "message" => "ID inválido"]);
    exit;
}

$id = intval($_POST["id"]);
$traspaso = TraspasoData::getById($id);

if(!$traspaso){
    echo json_encode(["success" => false, "message" => "Traspaso no encontrado"]);
    exit;
}

if($traspaso->estado != 1){
    echo json_encode(["success" => false, "message" => "Este traspaso ya fue procesado o anulado"]);
    exit;
}

$sucursal_actual = $_SESSION['sucursal_id'] ?? 1;

// Only the destination branch or a superadmin can receive
$user = UserData::getById($_SESSION["user_id"]);
if($traspaso->sucursal_destino_id != $sucursal_actual && $user->is_admin != 1){
    echo json_encode(["success" => false, "message" => "No tienes permisos para recepcionar este traspaso en esta sucursal"]);
    exit;
}

$detalles = TraspasoDetalleData::getAllByTraspasoId($id);

foreach($detalles as $item){
    $product_origen = ProductData::getById($item->product_id_origen);
    
    // Check if product exists in destination branch
    $targetProduct = ProductData::getByDuplicate($product_origen->cod_digemid, $product_origen->barcode, $product_origen->name, $traspaso->sucursal_destino_id);
    
    $target_product_id = 0;
    
    if ($targetProduct) {
        $target_product_id = $targetProduct->id;
    } else {
        // Clone product for the destination sucursal
        $newProduct = new ProductData();
        $newProduct->sucursal_id = $traspaso->sucursal_destino_id;
        $newProduct->barcode = $product_origen->barcode;
        $newProduct->cod_digemid = $product_origen->cod_digemid;
        $newProduct->image = $product_origen->image;
        $newProduct->name = $product_origen->name;
        $newProduct->principio_activo = $product_origen->principio_activo;
        $newProduct->description = $product_origen->description;
        $newProduct->price_in = $product_origen->price_in;
        $newProduct->price_may = $product_origen->price_may;
        $newProduct->price_out = $product_origen->price_out;
        $newProduct->stock = 0; // Se incrementará con la operación
        $newProduct->user_id = $_SESSION['user_id'] ?? 1;
        $newProduct->presentation = $product_origen->presentation;
        $newProduct->unit = $product_origen->unit;
        $newProduct->category_id = $product_origen->category_id ? $product_origen->category_id : "NULL";
        $newProduct->inventary_min = $product_origen->inventary_min;
        $newProduct->is_stock = $product_origen->is_stock;
        $newProduct->anaquel = $product_origen->anaquel;
        $newProduct->fecha_venc = $product_origen->fecha_venc;
        $newProduct->laboratorio = $product_origen->laboratorio;
        
        $res = $newProduct->add();
        if($res[0]){
            $target_product_id = $res[1];
        } else {
            echo json_encode(["success" => false, "message" => "Error al clonar el producto: " . $product_origen->name]);
            exit;
        }
    }
    
    // Update target product id in traspaso_detalle
    $item->product_id_destino = $target_product_id;
    $item->update_destino();

    // Input operation in target branch
    $opIn = new OperationData();
    $opIn->product_id = $target_product_id;
    $opIn->q = $item->q;
    $opIn->operation_type_id = OperationTypeData::getByName("entrada")->id;
    $opIn->sell_id = "NULL";
    $origen_name = $traspaso->getOrigen()->nombre;
    $opIn->descripcion = "Ingreso por Traslado del Almacén: " . $origen_name . " (Doc. " . $traspaso->serie . "-" . $traspaso->comprobante . ")";
    $opIn->idpaquete = "";
    $opInRes = $opIn->add();

    if ($opInRes[0]) {
        $item->operation_id_destino = $opInRes[1];
        $item->update_destino();

        if ($item->operation_id_origen) {
            // Retrieve lot movements from the origin operation and register them in destination
            $db = Database::getCon();
            $queryLots = "SELECT lm.cantidad, lm.costo_unitario, l.num_lot, l.fecha_vencimiento, l.fecha_fabricacion, l.ubicacion 
                          FROM lote_movimiento lm 
                          INNER JOIN lote l ON lm.lote_id = l.id 
                          WHERE lm.operation_id = " . intval($item->operation_id_origen) . " AND lm.tipo = 'salida'";
            $lotsRes = $db->query($queryLots);
            
            if ($lotsRes) {
                while($lot = $lotsRes->fetch_assoc()) {
                    LoteData::registerEntry(
                        $target_product_id, 
                        $lot['num_lot'], 
                        $lot['cantidad'], 
                        $_SESSION["user_id"], 
                        $opInRes[1], 
                        $lot['fecha_vencimiento'], 
                        $lot['fecha_fabricacion'], 
                        $lot['costo_unitario'], 
                        $lot['ubicacion']
                    );
                }
            }
        }
    }
}

$traspaso->user_recepcion_id = $_SESSION["user_id"];
$traspaso->recepcionar();

echo json_encode(["success" => true]);
?>
