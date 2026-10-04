<?php
header('Content-Type: application/json');

// Recibir y decodificar los datos JSON
$unidad = UnidadMedidaData::getById($_GET["id"]);
echo json_encode($unidad);
exit;
?>