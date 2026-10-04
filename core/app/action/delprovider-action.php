<?php 
header('Content-Type: application/json');

$provider = new PersonData();
$provider->id = $_GET["id"];
$provider->status = ($_GET["accion"]=='D') ? 0 : 1;
$provider->del();
echo json_encode(["success" => true]);
exit;
?>
