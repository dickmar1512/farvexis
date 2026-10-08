<?php
if(isset($_GET["id"])){
    $email = EmailConfigData::getById($_GET["id"]);
    $email->del();
    Core::alert("Correo eliminado exitosamente!");
    print "<script>window.location='index.php?view=emails';</script>";
}
?>
