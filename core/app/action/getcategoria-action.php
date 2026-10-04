<?php
header('Content-Type: application/json');

// Recibir y decodificar los datos JSON
$categoria = CategoryData::getById($_GET["id"]);
echo json_encode($categoria);
exit;
?>