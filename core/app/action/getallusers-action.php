<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$users = UserData::getAll();
echo json_encode($users);
exit;
?>