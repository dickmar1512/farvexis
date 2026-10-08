<?php
if(isset($_SESSION["traspaso"]) && count($_SESSION["traspaso"]) > 0){
    $sucursales = SucursalData::getAll(true);
    ?>
    <div class="card card-outline card-success shadow-sm mt-3">
        <div class="card-header py-2">
            <h3 class="card-title text-sm"><i class="fas fa-shopping-cart mr-1"></i> DETALLE DEL TRASPASO</h3>
        </div>
        <div class="card-body py-0 px-0">
            <table class="table table-bordered table-striped table-hover mb-0 text-sm">
                <thead class="bg-light">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Laboratorio</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $items = $_SESSION["traspaso"];
                    foreach($items as $idx => $item): 
                        $p = ProductData::getById($item["product_id"]);
                    ?>
                    <tr>
                        <td><?php echo $p->barcode; ?></td>
                        <td class="font-weight-bold text-primary"><?php echo $p->name; ?></td>
                        <td><?php echo $p->laboratorio; ?></td>
                        <td class="text-center font-weight-bold"><?php echo $item["q"]; ?></td>
                        <td class="text-center">
                            <button class="btn btn-xs btn-danger btn-remove-item" data-idx="<?php echo $idx; ?>" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light">
            <form action="./?action=process_traspaso" method="post" id="form-process-traspaso">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label>Sucursal Destino:</label>
                        <select name="sucursal_destino_id" class="form-control" required>
                            <option value="">-- SELECCIONAR --</option>
                            <?php foreach($sucursales as $s): 
                                $current_sucursal = $_SESSION['sucursal_id'] ?? 1;
                                if($s->id != $current_sucursal):
                            ?>
                            <option value="<?php echo $s->id; ?>"><?php echo $s->nombre; ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8 text-right pt-4">
                        <button type="button" class="btn btn-outline-danger" id="btn-clear-traspaso"><i class="fas fa-times-circle"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Procesar Traspaso</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    $('.btn-remove-item').click(function(){
        var idx = $(this).data('idx');
        $.post("./?action=clear_traspaso", { index: idx }, function(){
            $("#traspaso-cart-container").load("./?action=traspaso_cart_table");
        });
    });
    $('#btn-clear-traspaso').click(function(){
        $.post("./?action=clear_traspaso", { clear_all: true }, function(){
            $("#traspaso-cart-container").load("./?action=traspaso_cart_table");
        });
    });
    $('#form-process-traspaso').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: '¿Procesar Traspaso?',
            text: "El stock se descontará de tu almacén y quedará en tránsito.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, procesar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post($(this).attr('action'), $(this).serialize(), function(res) {
                    if(res.success) {
                        Swal.fire('Éxito', 'Traspaso enviado.', 'success').then(()=> window.location = './?view=traspasos');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, "json");
            }
        });
    });
    </script>
    <?php
}
?>
