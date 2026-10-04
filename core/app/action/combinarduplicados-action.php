<?php
if(isset($_POST["primary_id"]) && isset($_POST["duplicate_ids"])){
    $primary_id = $_POST["primary_id"];
    $duplicate_ids = $_POST["duplicate_ids"];
    $user_id = $_SESSION["user_id"];

    $db = Database::getCon();

    try {
        $db->begin_transaction();

        // Asegurar que la tabla de historial exista
        $sql_create_history = "CREATE TABLE IF NOT EXISTS merged_product_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            primary_product_id INT,
            primary_product_name VARCHAR(255),
            duplicate_product_id INT,
            duplicate_product_name VARCHAR(255),
            duplicate_barcode VARCHAR(50),
            merged_stock DECIMAL(10,2),
            user_id INT,
            created_at DATETIME
        )";
        $db->query($sql_create_history);

        $primary_product = ProductData::getById($primary_id);

        foreach($duplicate_ids as $dup_id){
            if($dup_id == $primary_id) continue;

            // 1. Obtener datos del duplicado para el historial
            $dup_product = ProductData::getById($dup_id);
            if(!$dup_product) continue;
            
            $dup_stock = $dup_product->stock;

            // 2. Guardar en el historial
            $sql_historial = "INSERT INTO merged_product_history 
                (primary_product_id, primary_product_name, duplicate_product_id, duplicate_product_name, duplicate_barcode, merged_stock, user_id, created_at) 
                VALUES 
                ($primary_id, '{$primary_product->name}', $dup_id, '{$dup_product->name}', '{$dup_product->barcode}', $dup_stock, $user_id, NOW())";
            $db->query($sql_historial);

            // 3. Sumar stock al principal
            $sql_update_stock = "UPDATE product SET stock = stock + $dup_stock WHERE id = $primary_id";
            $db->query($sql_update_stock);

            // 4. Actualizar referencias en todas las tablas identificadas
            $sql_op = "UPDATE operation SET product_id = $primary_id WHERE product_id = $dup_id";
            $db->query($sql_op);

            $sql_lote = "UPDATE lote SET id_prod = $primary_id WHERE id_prod = $dup_id";
            $db->query($sql_lote);

            $sql_hist = "UPDATE price_history SET product_id = $primary_id WHERE product_id = $dup_id";
            $db->query($sql_hist);

            $sql_paq = "UPDATE detalle_paq SET idprod = $primary_id WHERE idprod = $dup_id";
            $db->query($sql_paq);

            $sql_det_ord = "UPDATE detalle_orden SET product_id = $primary_id WHERE product_id = $dup_id";
            $db->query($sql_det_ord);

            // 5. Eliminar el producto duplicado
            $sql_del = "DELETE FROM product WHERE id = $dup_id";
            $db->query($sql_del);
        }

        $db->commit();
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Productos combinados correctamente. El historial ha sido registrado.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                window.location.href = './?view=products';
            });
        </script>";
    } catch (Exception $e) {
        $db->rollback();
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error Crítico',
                text: 'No se pudo completar la fusión: " . addslashes($e->getMessage()) . "',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Cerrar'
            }).then((result) => {
                window.location.href = './?view=combinarduplicados';
            });
        </script>";
    }
} else {
    Core::redir("./?view=combinarduplicados");
}
?>
