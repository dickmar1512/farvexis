<?php 
header('Content-Type: application/json');

$user = UserData::getById($_GET["id"]);
echo json_encode($user);
exit;
?>