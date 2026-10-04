<?php 
header('Content-Type: application/json');

$client = new PersonData();
$client->id = $_GET["id"];
$client->status = ($_GET["accion"]=='D') ? 0 : 1;
$client->del();
echo json_encode(["success" => true]);
exit;
?>