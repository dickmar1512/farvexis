<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$products = ProductData::getAll();
echo json_encode($products);
exit;
?>