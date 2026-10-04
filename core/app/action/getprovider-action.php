<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$provider = PersonData::getById($_GET["id"]);
echo json_encode($provider);
exit;
?>