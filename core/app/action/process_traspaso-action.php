<?php
header('Content-Type: application/json');

if(!isset($_SESSION["traspaso"]) || count($_SESSION["traspaso"]) == 0){
    echo json_encode(["success" => false, "message" => "El carrito está vacío"]);
    exit;
}

$sucursal_destino_id = $_POST["sucursal_destino_id"];
if(empty($sucursal_destino_id)){
    echo json_encode(["success" => false, "message" => "Debe seleccionar una sucursal destino"]);
    exit;
}

$sucursal_origen_id = $_SESSION['sucursal_id'] ?? 1;

if($sucursal_origen_id == $sucursal_destino_id) {
    echo json_encode(["success" => false, "message" => "La sucursal de origen y destino deben ser diferentes"]);
    exit;
}

$cart = $_SESSION["traspaso"];

// Verify stock and lotes
foreach($cart as $item){
    $p = ProductData::getById($item["product_id"]);
    if($p->stock < $item["q"] || !LoteData::canAllocate($item["product_id"], $item["q"])){
        echo json_encode(["success" => false, "message" => "Stock o lotes insuficientes para el producto: " . $p->name]);
        exit;
    }
}

// Generar Correlativo de Traspaso (tipo 75)
$db = Database::getCon();
$sucursalObj = SucursalData::getById($sucursal_origen_id);
$codLocalEmisor = $sucursalObj ? $sucursalObj->codigo : '0000';
$serie = ComprobanteCorrelativoData::getSerieDefecto('75', $codLocalEmisor);
$comprobante = ComprobanteCorrelativoData::siguiente($db, '75', $serie, $codLocalEmisor, $sucursal_origen_id);

// 1. Crear Traspaso
$traspaso = new TraspasoData();
$traspaso->sucursal_origen_id = $sucursal_origen_id;
$traspaso->sucursal_destino_id = $sucursal_destino_id;
$traspaso->user_id = $_SESSION["user_id"];
$traspaso->serie = $serie;
$traspaso->comprobante = $comprobante;
$res = $traspaso->add();

if($res[0]){
    $traspaso_id = $res[1];
    
    // 2. Crear detalles y descontar stock
    foreach($cart as $item){
        $detalle = new TraspasoDetalleData();
        $detalle->traspaso_id = $traspaso_id;
        $detalle->product_id_origen = $item["product_id"];
        $detalle->q = $item["q"];
        
        // Output operation (Descontar stock origen)
        $opOut = new OperationData();
        $opOut->product_id = $item["product_id"];
        $opOut->q = $item["q"];
        $opOut->operation_type_id = OperationTypeData::getByName("salida")->id;
        $opOut->sell_id = "NULL";
        $opOut->descripcion = "Envío por Traspaso N° " . $traspaso_id;
        $opOut->idpaquete = "";
        $opOutRes = $opOut->add();

        if($opOutRes[0]){
            $detalle->operation_id_origen = $opOutRes[1];
            LoteData::allocateForOperation($opOutRes[1], $item["product_id"], $item["q"], $_SESSION["user_id"]);
        }
        
        $detalle->add();
    }
    
    
    unset($_SESSION["traspaso"]);
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error al registrar el traspaso"]);
}
?>
