<?php
/**
 * Action para actualizar la información básica de un kit/paquete vía AJAX
 */
if(count($_POST) > 0)
{
	$kit = KitData::getById($_POST["kit_id"]);
    if($kit){
        $kit->barcode = $_POST["barcode"];
        $kit->nombre = $_POST["name"];
        $kit->descripcion = $_POST["description"];
        $kit->precio = $_POST["price_out"];
        $kit->estado = isset($_POST["is_active"]) ? 1 : 0;

        if(isset($_FILES["image"]) && $_FILES["image"]["name"] != "")
        {
            $image = new Upload($_FILES["image"]);
            if($image->uploaded)
            {
                $image->Process("storage/products/");
                if($image->processed)
                {
                    $kit->imagen = $image->file_dst_name;
                    $kit->update_image();
                }
            }
        }
        
        $kit->update();
        echo json_encode(["status" => "success", "message" => "La información del paquete ha sido actualizada."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Paquete no encontrado."]);
    }
}
else
{
    echo json_encode(["status" => "error", "message" => "Petición no válida."]);
}
?>
