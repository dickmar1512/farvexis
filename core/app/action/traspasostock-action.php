<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$data = json_decode(file_get_contents("php://input"), true);

if(empty($data['product_id']) || empty($data['target_sucursal_id']) || empty($data['qty'])) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$product_id = intval($data['product_id']);
$target_sucursal_id = intval($data['target_sucursal_id']);
$qty = intval($data['qty']);

if ($qty <= 0) {
    echo json_encode(["success" => false, "message" => "Cantidad inválida"]);
    exit;
}

// 1. Get original product
$product = ProductData::getById($product_id);
if (!$product) {
    echo json_encode(["success" => false, "message" => "Producto no encontrado"]);
    exit;
}

// Ensure stock is sufficient
if ($product->stock < $qty) {
    echo json_encode(["success" => false, "message" => "Stock insuficiente en la sucursal de origen"]);
    exit;
}

if ($product->sucursal_id == $target_sucursal_id) {
    echo json_encode(["success" => false, "message" => "La sucursal de origen y destino deben ser diferentes"]);
    exit;
}

// Check if product exists in target sucursal
$targetProduct = ProductData::getByDuplicate($product->cod_digemid, $product->barcode, $product->name, $target_sucursal_id);

$target_product_id = 0;

if ($targetProduct) {
    $target_product_id = $targetProduct->id;
} else {
    // Clone product for the new sucursal
    $newProduct = new ProductData();
    $newProduct->sucursal_id = $target_sucursal_id;
    $newProduct->barcode = $product->barcode;
    $newProduct->cod_digemid = $product->cod_digemid;
    $newProduct->image = $product->image;
    $newProduct->name = $product->name;
    $newProduct->principio_activo = $product->principio_activo;
    $newProduct->description = $product->description;
    $newProduct->price_in = $product->price_in;
    $newProduct->price_may = $product->price_may;
    $newProduct->price_out = $product->price_out;
    $newProduct->stock = 0; // Se incrementará con la operación de entrada
    $newProduct->user_id = $_SESSION['user_id'] ?? 1;
    $newProduct->presentation = $product->presentation;
    $newProduct->unit = $product->unit;
    $newProduct->category_id = $product->category_id ? $product->category_id : "NULL";
    $newProduct->inventary_min = $product->inventary_min;
    $newProduct->is_stock = $product->is_stock;
    $newProduct->anaquel = $product->anaquel;
    $newProduct->fecha_venc = $product->fecha_venc;
    $newProduct->laboratorio = $product->laboratorio;
    
    $res = $newProduct->add();
    if($res[0]) {
        $target_product_id = $res[1];
    } else {
        echo json_encode(["success" => false, "message" => "Error al clonar el producto en la sucursal destino"]);
        exit;
    }
}

// Output from original sucursal
$opOut = new OperationData();
$opOut->product_id = $product_id;
$opOut->q = $qty;
$opOut->operation_type_id = OperationTypeData::getByName("salida")->id;
$opOut->sell_id = "NULL";
$opOut->descripcion = "Traspaso a sucursal ID: " . $target_sucursal_id;
$opOut->idpaquete = "";
$resOut = $opOut->add();

// Input to target sucursal
$opIn = new OperationData();
$opIn->product_id = $target_product_id;
$opIn->q = $qty;
$opIn->operation_type_id = OperationTypeData::getByName("entrada")->id;
$opIn->sell_id = "NULL";
$opIn->descripcion = "Traspaso desde sucursal ID: " . $product->sucursal_id;
$opIn->idpaquete = "";
$resIn = $opIn->add();

if ($resOut[0] && $resIn[0]) {
    echo json_encode(["success" => true, "message" => "Traspaso exitoso"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al registrar los movimientos de stock"]);
}
?>
