<?php
/**
 * Action para agregar un nuevo kit/paquete vía AJAX
 * @author Antigravity
 */
if(count($_POST) > 0)
{
	$kit = new KitData();

	$kit->barcode = $_POST["barcode"];
	$kit->nombre = $_POST["name"];
	$kit->descripcion = $_POST["description"];
	$kit->precio = $_POST["price_out"];
	$kit->user_id = $_SESSION["user_id"];
    
    $success = false;

	if(isset($_FILES["image"]) && $_FILES["image"]["name"] != "")
	{
		$image = new Upload($_FILES["image"]);
		if($image->uploaded)
		{
			$image->Process("storage/products/");
			if($image->processed)
			{
				$kit->imagen = $image->file_dst_name;
				$kit->add_with_image();
                $success = true;
			}
		}
		else
		{
			$kit->add();
            $success = true;
		}
	}
	else
	{
		$kit->add();
        $success = true;
	}
    
    if($success){
        echo json_encode(["status" => "success", "message" => "¡Excelente! El paquete ha sido registrado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Hubo un problema al procesar la imagen o los datos."]);
    }
}
else
{
    echo json_encode(["status" => "error", "message" => "Petición no válida."]);
}
?>
