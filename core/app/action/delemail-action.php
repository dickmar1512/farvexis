<?php
header('Content-Type: application/json; charset=utf-8');

if(isset($_POST["id"])){
    try {
        $email = EmailConfigData::getById($_POST["id"]);
        $email->del();
        
        echo json_encode(["status" => "success", "message" => "Correo eliminado exitosamente!"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error al eliminar: " . $e->getMessage()]);
    }
}
?>
