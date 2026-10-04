<?php
$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data["user_id"]) || !isset($data["permissions"])){
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$user_id = $data["user_id"];
$permissions = $data["permissions"]; // Array of module IDs

// Desactivar todos los permisos actuales (marcar como historial)
UserAccessData::delAllByUserId($user_id);

foreach($permissions as $module_id){
    // Verificar si ya existía en el historial
    $access = UserAccessData::getAnyByUserModule($user_id, $module_id);
    if($access){
        // Si ya existía, lo reactivamos
        $access->update_status(1);
    } else {
        // Si es nuevo, lo creamos
        $new_access = new UserAccessData();
        $new_access->user_id = $user_id;
        $new_access->module_id = $module_id;
        $new_access->add();
    }
}

echo json_encode(["success" => true]);
?>
