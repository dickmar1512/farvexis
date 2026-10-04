<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)) {
    $is_admin = isset($data["is_admin"]) ? 1 : 0;
    $is_caja = isset($data["is_caja"]) ? 1 : 0;
    $is_dirtec = isset($data["is_dirtec"]) ? 1 : 0;
    if ($is_admin) {
        $is_dirtec = 0;
        $is_caja = 0;
    } elseif ($is_dirtec) {
        $is_caja = 0;
    }
    $is_active = isset($data["is_active"]) ? 1 : 0;
    $is_desc = isset($data["is_desc"]) ? 1 : 0;

    $user = UserData::getById($data["user_id"]);
    $user->name = $data["name"];
    $user->lastname = $data["lastname"];
    $user->username = $data["username"];
    $user->email = $data["email"];
    $user->montomax = $data["montomax"];
    $user->is_admin = $is_admin;
    $user->is_caja = $is_caja;
    $user->is_dirtec = $is_dirtec;
    $user->is_active = $is_active;
    $user->is_desc = $is_desc;
	$user->sucursal_id = (int)($data['sucursal_id'] ?? 1);
    $user->update();

    if(isset($data["password"])){
        $user->password = sha1(md5($data["password"]));
        $user->update_passwd();
    }

    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["success" => false, "error" => "No se recibieron datos"]);
exit;

?>