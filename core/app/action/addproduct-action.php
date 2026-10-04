<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)) {
    $product = new ProductData();
    $product->barcode          = $data["barcode"];
    $product->cod_digemid      = $data["cod_digemid"];
    $product->name             = addslashes($data["name"]);
    $product->principio_activo = $data["prin_act"];
    $product->price_in         = $data["price_in"];
    $product->price_may        = $data["price_may"];
    $product->price_out        = $data["price_out"];
    $product->anaquel          = $data["anaquel"];
    $product->stock            = $data["q"];
    $product->unit             = $data["selUnidadMedida"];
    $product->description      = $data["description"] ?? "";
    $product->presentation     = $data["presentacion"] ?? "";
    $product->fecha_venc       = $data["fecha_venc"] ?? "";
    $product->laboratorio      = $data["laboratorio"] ?? "";
    
    $category_id="NULL";
    if($data["category_id"]!=""){ $category_id = $data["category_id"];}
    $inventary_min="10";
    if($data["inventary_min"]!=""){ $inventary_min = $data["inventary_min"];}
  
    $product->category_id   = $category_id;
    $product->inventary_min = $inventary_min;
    $product->user_id       = $_SESSION["user_id"];
  
    $product->user_id       = $_SESSION["user_id"];
  
    // Verificar si ya existe para actualizar o insertar
    $existing = ProductData::getByDuplicate($product->cod_digemid, $product->barcode, $product->name);

    if($existing) {
        $product->id = $existing->id;
        $product->is_active = 1;
        // Si viene stock nuevo, se suma al existente o se reemplaza? 
        // El usuario dijo "actualizar". En importación sumamos. Aquí reemplazamos por lo que puso en el modal?
        // Usaremos el valor del modal.
        $product->update();
        $prod = [$existing->id, true]; // Simular retorno de Executor
    } else {
        if(isset($data['is_stock'])) { $product->is_stock = 1; } else { $product->is_stock = 0; }
        
        if(isset($_FILES["image"]))
        {
          $image = new Upload($_FILES["image"]);
          if($image->uploaded){
            $image->Process("storage/products/");
            if($image->processed)
            {
              $product->image = $image->file_dst_name;
              $prod = $product->add_with_image();
            }
            }else
            {
             $prod= $product->add();
            }
        }
        else
        {
         $prod= $product->add();
        }
    }
    
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["success" => false, "error" => "No se recibieron datos"]);
exit;
?>