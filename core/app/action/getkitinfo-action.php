<?php
/**
 * Action para obtener la información de un kit en formato JSON para el modal de edición
 */
if(isset($_GET["id"]))
{
    $kit = KitData::getById($_GET["id"]);
    if($kit){
        echo json_encode([
            "status" => "success",
            "data" => [
                "id" => $kit->idpaquete,
                "barcode" => $kit->barcode,
                "name" => $kit->nombre,
                "description" => $kit->descripcion,
                "price" => $kit->precio,
                "image" => $kit->imagen,
                "is_active" => $kit->estado
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Paquete no encontrado."]);
    }
}
?>
