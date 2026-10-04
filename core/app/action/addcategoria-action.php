<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)) {
	$categoria = new CategoryData();
	$categoria->name = $data["txtCategoria"];
    $categoria->description = $data["txtDescripcion"];
    $categoria->created_at  = date('Y-m-d H:i:s');
	$categoria->add();

    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["success" => false, "error" => "No se recibieron datos"]);
exit;
?>