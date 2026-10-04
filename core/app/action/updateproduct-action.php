<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)) 
{	
	$product = ProductData::getById($data["product_id"]);

	$product->barcode = $data["barcode"];
	$product->cod_digemid = $data["cod_digemid"];
	$product->name = $data["name"];
	$product->principio_activo = $data["prin_act"];
	$product->price_in = $data["price_in"];
	$product->price_may = $data["price_may"];
	$product->price_out = $data["price_out"] ?? 0;
	$product->anaquel = $data["anaquel"] ?? "";
	$product->unit = $data["selUnidadMedida"] ?? "";

  	$product->description = $data["description"] ?? "";
  	$product->presentation = $data["presentacion"] ?? "";
    $product->laboratorio = $data["laboratorio"] ?? "";
  	$product->inventary_min = $data["inventary_min"] ?? 10;

  	$category_id="NULL";
  	if($data["category_id"]!=""){ $category_id=$data["category_id"];}

  	$is_active = 0;
  	if(isset($data["is_active"])){ $is_active=1;}

  	$product->is_active = $is_active;

  	$is_may = 0;
    if(isset($data["is_may"])){$is_may=1;}
    $product->is_may = $is_may;

  	$product->category_id = $category_id;
  	$product->stock = $data["q"];

	$product->user_id = $_SESSION["user_id"];
	$product->update();

	if(isset($_FILES["image"])){
		$image = new Upload($_FILES["image"]);
		if($image->uploaded)
		{
			$image->Process("storage/products/");
			if($image->processed){
				$product->image = $image->file_dst_name;
				$product->update_image();
			}
		}
	}
	echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["success" => false, "error" => "No se recibieron datos"]);
exit;

?>