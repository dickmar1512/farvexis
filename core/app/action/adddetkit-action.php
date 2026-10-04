<?php
/**
 * Action para agregar un producto al detalle de un kit vía AJAX
 */
if(count($_POST) > 0)
{
	$detkit = new Det_kit();
	$detkit->idpaquete = $_POST["idpaquete"];
	$detkit->idprod    = $_POST["product_id"];
	$detkit->precio    = $_POST["precio_unitario"];
	$detkit->descuento = $_POST["descuento"];
	$detkit->cantidad  = $_POST["q"];

	$detkit->add();
    
    echo json_encode(["status" => "success", "message" => "Producto agregado al paquete."]);
}
else
{
    echo json_encode(["status" => "error", "message" => "Petición no válida."]);
}
?>
