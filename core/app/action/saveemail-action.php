<?php
if(isset($_POST["email"])){
    $email = new EmailConfigData();
    $email->email = $_POST["email"];
    $email->type = $_POST["type"];
    $email->is_active = $_POST["is_active"];

    if(isset($_POST["id"]) && $_POST["id"] != ""){
        $email->id = $_POST["id"];
        $email->update();
        Core::alert("Correo actualizado exitosamente!");
    } else {
        $email->add();
        Core::alert("Correo agregado exitosamente!");
    }
    print "<script>window.location='index.php?view=emails';</script>";
}
?>
