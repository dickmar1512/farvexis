<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)){
    $unidad = new UnidadMedidaData();
    $unidad->name = $data["txtDescripcion"];
    $unidad->sigla = $data["txtsigla"];
    $unidad->add();
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["success" => false, "error" => "No se recibieron datos"]);
exit;
?>