<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)) {
	$provider = PersonData::getById($data["provider_id"]);
    $tipo = $data["optTipoPersona"];
    $provider->tipo_persona = $tipo;

	if ($tipo == 3){
        $provider->numero_documento = $data["dni"];
		$provider->name = $data["name"];
		$provider->lastname = $data["lastname"];
	}else{
		$provider->numero_documento = $data["ruc"];
		$provider->name = $data["razon_social"];
		$provider->lastname = "";
	}
		
	$provider->address1 = $data["address1"];
	$provider->ubigeo = $data["ubigeo"] ?? "";
	$provider->email1 = $data["email1"];
	$provider->phone1 = $data["phone1"];
	$provider->update_provider();

	echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["success" => false, "error" => "No se recibieron datos"]);
exit;
?>