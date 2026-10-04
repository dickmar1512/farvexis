<?php
include "../core/controller/Database.php";
include "../core/controller/Executor.php";

$con = Database::getCon();

// 1. Buscar el módulo padre de Reportes
$sql_parent = "SELECT id FROM module WHERE name LIKE '%Reporte%' AND parent_id IS NULL LIMIT 1";
$res_parent = $con->query($sql_parent);
$parent = $res_parent->fetch_assoc();

if($parent){
    $parent_id = $parent['id'];
    echo "Padre 'Reportes' encontrado con ID: $parent_id\n";
    
    // 2. Insertar el nuevo módulo si no existe
    $view_name = "purchasereport";
    $name = "Sugerencia Compra";
    
    $sql_check = "SELECT id FROM module WHERE view_name = '$view_name'";
    $res_check = $con->query($sql_check);
    
    if($res_check->num_rows == 0){
        $sql_ins = "INSERT INTO module (name, view_name, parent_id, is_active) VALUES ('$name', '$view_name', $parent_id, 1)";
        if($con->query($sql_ins)){
            $new_id = $con->insert_id;
            echo "Módulo '$name' insertado con ID: $new_id\n";
            
            // 3. Dar acceso al usuario administrador (generalmente ID 1)
            $sql_access = "INSERT IGNORE INTO user_access (user_id, module_id) VALUES (1, $new_id)";
            $con->query($sql_access);
            echo "Acceso concedido al Administrador (ID 1)\n";
        }
    } else {
        echo "El módulo ya existe.\n";
    }
} else {
    echo "No se encontró un módulo padre llamado 'Reportes'.\n";
}
?>
