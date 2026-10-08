<?php
$product = $_GET["product"];
$products = ProductData::getLike($product); // getLike already filters by current sucursal
if (count($products) > 0) {
    ?>
    <table class="table table-bordered table-hover">
        <thead class="bg-primary text-white">
            <th>Código</th>
            <th>Nombre</th>
            <th>Laboratorio</th>
            <th>Stock Actual</th>
            <th>Cantidad</th>
            <th>Acción</th>
        </thead>
        <tbody>
            <?php foreach ($products as $p): 
                // Evitar traspasar si no hay stock
                if ($p->stock <= 0) continue;
            ?>
                <tr class="tr-traspaso-item" data-id="<?php echo $p->id; ?>">
                    <td><?php echo $p->barcode; ?></td>
                    <td><?php echo $p->name; ?></td>
                    <td><?php echo $p->laboratorio; ?></td>
                    <td class="font-weight-bold text-center"><?php echo $p->stock; ?></td>
                    <td style="width:150px;">
                        <input type="number" class="form-control text-center item-q" value="1" min="1" max="<?php echo $p->stock; ?>" required>
                    </td>
                    <td style="width:100px;">
                        <button type="button" class="btn btn-success btn-add-traspaso"><i class="fas fa-plus"></i> Añadir</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <script>
        $('.btn-add-traspaso').click(function(){
            var tr = $(this).closest('tr');
            var product_id = tr.data('id');
            var q = tr.find('.item-q').val();
            
            if (q <= 0) return;
            
            $.post("./?action=addtocart_traspaso", {product_id: product_id, q: q}, function(data){
                $("#search-results-traspaso").html('');
                $("#product_code_traspaso").val('').focus();
                $("#traspaso-cart-container").load("./?action=traspaso_cart_table");
            });
        });
    </script>
    <?php
} else {
    echo "<div class='alert alert-warning'>No se encontraron resultados en esta sucursal.</div>";
}
?>
