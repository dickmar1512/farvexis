<?php
if (!isset($_GET["product_id"])) {
    print "<script>window.location='./?view=inventary';</script>";
}

$product = ProductData::getById($_GET["product_id"]);
if (!$product) {
    print "<script>window.location='./?view=inventary';</script>";
}

$operations = OperationData::getAllByProductId($product->id);

// Totales
$itotal = OperationData::GetInputQYesF($product->id);
$ototal = -1 * OperationData::GetOutputQYesF($product->id);
$disponible = $product->stock;
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold">
                    <i class="fas fa-history text-success mr-2"></i> Historial de Inventario
                </h1>
                <p class="text-muted mb-0">Producto: <span class="text-dark font-weight-bold"><?= $product->name ?></span></p>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mt-2">
                    <li class="breadcrumb-item"><a href="./?view=home">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="./?view=inventary">Inventario</a></li>
                    <li class="breadcrumb-item active">Historial</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Tarjetas de Resumen -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-arrow-circle-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Entradas Totales</span>
                        <span class="info-box-number h4 mb-0"><?= number_format($itotal, 0) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-arrow-circle-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Salidas Totales</span>
                        <span class="info-box-number h4 mb-0"><?= number_format($ototal, 0) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-boxes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Stock Disponible</span>
                        <span class="info-box-number h4 mb-0"><?= number_format($disponible, 0) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- Columna de Movimientos -->
            <div class="col-md-8">
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-list mr-1"></i> Detalle de Movimientos</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover datatable" style="width:100%" data-order='[[ 0, "desc" ]]'>
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Fecha / Hora</th>
                                        <th class="text-center">Tipo</th>
                                        <th class="text-center text-info">Cantidad</th>
                                        <th class="text-right">P. Unitario</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($operations as $op): 
                                        $type_name = $op->getOperationType()->name;
                                        $is_input = ($op->operation_type_id == 1);
                                        $badge_class = $is_input ? 'badge-success' : 'badge-danger';
                                        $type_icon = $is_input ? 'fa-arrow-down' : 'fa-arrow-up';
                                    ?>
                                    <tr>
                                        <td class="text-xs"><?= date("d/m/Y H:i", strtotime($op->created_at)) ?></td>
                                        <td class="text-center">
                                            <span class="badge <?=$badge_class?> px-2">
                                                <i class="fas <?=$type_icon?> mr-1 text-xs"></i> <?= strtoupper($type_name) ?>
                                            </span>
                                        </td>
                                        <td class="text-center font-weight-bold <?= $is_input ? 'text-success' : 'text-danger' ?>">
                                            <?= $is_input ? '+' : '-' ?> <?= number_format($op->q, 0) ?>
                                        </td>
                                        <td class="text-right font-weight-bold text-primary">
                                            S/ <?= number_format($op->prec_alt, 2) ?>
                                        </td>
                                        <td class="text-xs text-muted"><?= $op->descripcion ?></td>
                                        <td class="text-center">
                                            <button onclick="confirmDelete(<?= $op->id ?>, <?= $op->product_id ?>)" class="btn btn-xs btn-outline-danger shadow-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna de Descargas -->
            <div class="col-md-4">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-file-excel mr-1 text-success"></i> Descargar Kardex Mensual</h3>
                    </div>
                    <div class="card-body">
                        <form action="./?action=excel_kardex" method="POST" id="formKardex">
                            <input type="hidden" name="id_producto" value="<?= $product->id ?>">
                            
                            <div class="form-group">
                                <label class="text-xs font-weight-bold mb-1">Seleccionar Mes:</label>
                                <select class="form-control form-control-sm select2bs4" name="selMes" id="selMes" required>
                                    <option value="">:: Seleccione Mes ::</option>
                                    <?php 
                                    $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                                    foreach($meses as $i => $m): ?>
                                        <option value="<?= $i+1 ?>" <?= (date('m')==$i+1)?'selected':'' ?>><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="text-xs font-weight-bold mb-1">Seleccionar Año:</label>
                                <select class="form-control form-control-sm select2bs4" name="selAnio" id="selAnio" required>
                                    <?php 
                                    $anio_actual = date('Y');
                                    for($a = $anio_actual-3; $a <= $anio_actual+1; $a++): ?>
                                        <option value="<?= $a ?>" <?= ($a==$anio_actual)?'selected':'' ?>><?= $a ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success btn-block shadow-sm mt-3">
                                <i class="fas fa-download mr-2"></i> Generar Excel
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm"><i class="fas fa-info-circle mr-1"></i> Información</h3>
                    </div>
                    <div class="card-body p-3 text-xs text-muted">
                        <p>Los movimientos marcados como <b>ENTRADA</b> aumentan el stock actual, mientras que las <b>SALIDAS</b> lo disminuyen.</p>
                        <p>Si elimina un movimiento, el stock del producto se recalculará automáticamente para mantener la consistencia.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
    function confirmDelete(opid, pid) {
        Swal.fire({
            title: '¿Eliminar movimiento?',
            text: "Esta acción afectará el stock actual del producto. ¿Deseas continuar?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('./?action=deleteoperation', { opid: opid }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Error al eliminar', 'error');
                    }
                }, 'json').fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error en el servidor', 'error');
                });
            }
        })
    }

    $(document).ready(function() {
        // Inicializar Select2 si está disponible
        if ($.fn.select2) {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
        }
    });
</script>