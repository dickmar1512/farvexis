<?php 
header('Content-Type: application/json');

$client = PersonData::getById($_GET["id"]);
echo json_encode($client);
exit;
?>