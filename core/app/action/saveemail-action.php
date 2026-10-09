<?php
header('Content-Type: application/json; charset=utf-8');

if(isset($_POST["email"])){
    try {
        $email = new EmailConfigData();
        $email->email = $_POST["email"];
        $email->type = $_POST["type"];
        $email->is_active = $_POST["is_active"];

        if(isset($_POST["id"]) && $_POST["id"] != ""){
            $email->id = $_POST["id"];
            $email->update();
            $msg = "Correo actualizado exitosamente!";
        } else {
            $email->add();
            $msg = "Correo agregado exitosamente!";
        }
        
        echo json_encode(["status" => "success", "message" => $msg]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
    }
}
?>
