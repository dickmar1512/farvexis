<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$product = ProductData::getById($_GET["id"]);
echo json_encode($product);
exit;
?>