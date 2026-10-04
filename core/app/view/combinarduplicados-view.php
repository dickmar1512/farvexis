<?php
$products = ProductData::getAll2();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fa fa-compress-arrows-alt"></i> Combinar Productos Duplicados</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Productos - Servicios</a></li>
                    <li class="breadcrumb-item active">Combinar Duplicados</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Seleccionar Productos para Combinar</h3>
            </div>
            <div class="card-body">
                <form id="mergeForm" method="post" action="./?action=combinarduplicados">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="primary_id">Producto Principal (El que se mantendrá)</label>
                                <select name="primary_id" id="primary_id" class="form-control select2" required>
                                    <option value="">-- SELECCIONAR PRODUCTO --</option>
                                    <?php foreach($products as $p): ?>
                                        <option value="<?php echo $p->id; ?>"><?php echo $p->barcode." - ".$p->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Este producto conservará su ID, nombre y configuraciones. El stock de los duplicados se sumará a este.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duplicate_ids">Productos Duplicados (Se eliminarán)</label>
                                <select name="duplicate_ids[]" id="duplicate_ids" class="form-control select2" multiple="multiple" required>
                                    <?php foreach($products as $p): ?>
                                        <option value="<?php echo $p->id; ?>"><?php echo $p->barcode." - ".$p->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Estos productos se eliminarán. Todas sus operaciones, lotes e historial se moverán al producto principal.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fa fa-compress-arrows-alt"></i> COMBINAR PRODUCTOS AHORA
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <p class="text-danger"><b>¡Atención!</b> Esta acción es irreversible. Se actualizarán las siguientes tablas:</p>
                <ul>
                    <li>Operaciones (Ventas, Compras, Salidas)</li>
                    <li>Lotes (Stocks por lote)</li>
                    <li>Historial de Precios</li>
                    <li>Detalles de Paquetes/Kits</li>
                    <li>Detalles de Órdenes de Trabajo</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        $('.select2').each(function() {
            $(this).select2({
                theme: 'bootstrap4',
                placeholder: $(this).attr('placeholder')
            });
        });

        $('#mergeForm').on('submit', function(e) {
            var primary = $('#primary_id').val();
            var duplicates = $('#duplicate_ids').val();

            if (!primary) {
                Swal.fire('Error', 'Debe seleccionar un producto principal.', 'error');
                return false;
            }

            if (!duplicates || duplicates.length === 0) {
                Swal.fire('Error', 'Debe seleccionar al menos un producto duplicado.', 'error');
                return false;
            }

            if (duplicates.includes(primary)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El producto principal no puede estar en la lista de duplicados.'
                });
                return false;
            }

            e.preventDefault();
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se combinarán los productos seleccionados y los duplicados se ELIMINARÁN. Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, combinar ahora',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });
    });
</script>
