<?php
/**
 * Action para eliminar un producto del detalle de un kit vía AJAX
 */
if(isset($_POST["iddet"]))
{
	$iddet = $_POST["iddet"];
	Det_kit::delId($iddet);
    
    echo json_encode(["status" => "success", "message" => "Producto eliminado del paquete."]);
}
else
{
    echo json_encode(["status" => "error", "message" => "ID de detalle no proporcionado."]);
}
?>
