<?php
$traspaso = TraspasoData::getById($_GET["id"]);
$detalles = TraspasoDetalleData::getAllByTraspasoId($traspaso->id);
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class='fas fa-file-alt mr-2'></i> Traspaso <?php echo $traspaso->serie . '-' . $traspaso->comprobante; ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="./?view=traspasos">Traspasos</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Información del Documento Interno</h3>
                <div class="card-tools">
                    <?php if($traspaso->estado == 1): ?>
                        <span class="badge badge-warning p-2">ESTADO: EN TRÁNSITO</span>
                    <?php elseif($traspaso->estado == 2): ?>
                        <span class="badge badge-success p-2">ESTADO: RECEPCIONADO</span>
                    <?php else: ?>
                        <span class="badge badge-danger p-2">ESTADO: ANULADO</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-4 border-right">
                        <h6 class="text-muted">Sucursal Origen</h6>
                        <strong><?php echo $traspaso->getOrigen()->nombre; ?></strong><br>
                        <small>Enviado por: <?php echo $traspaso->getUser()->name; ?></small><br>
                        <small>Fecha: <?php echo $traspaso->fecha_envio; ?></small>
                    </div>
                    <div class="col-sm-4 border-right">
                        <h6 class="text-muted">Sucursal Destino</h6>
                        <strong><?php echo $traspaso->getDestino()->nombre; ?></strong><br>
                        <?php if($traspaso->estado == 2): ?>
                            <small>Recepcionado por: <?php echo $traspaso->getUserRecepcion()->name; ?></small><br>
                            <small>Fecha Recepción: <?php echo $traspaso->fecha_recepcion; ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-sm">
                        <thead class="bg-light">
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Laboratorio</th>
                            <th class="text-center">Cant. Enviada</th>
                        </thead>
                        <tbody>
                            <?php foreach($detalles as $d): 
                                $p = $d->getProductOrigen();
                            ?>
                            <tr>
                                <td><?php echo $p->barcode; ?></td>
                                <td class="font-weight-bold text-primary"><?php echo $p->name; ?></td>
                                <td><?php echo $p->laboratorio; ?></td>
                                <td class="text-center font-weight-bold"><?php echo $d->q; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-right">
                <a href="./?view=traspasos" class="btn btn-default"><i class="fas fa-arrow-left"></i> Volver</a>
                <?php 
                $sucursal_id = $_SESSION['sucursal_id'] ?? 1;
                if($traspaso->estado == 1 && $traspaso->sucursal_destino_id == $sucursal_id): ?>
                    <button class="btn btn-success btn-recepcionar" data-id="<?php echo $traspaso->id; ?>"><i class="fas fa-check-double"></i> Recepcionar Ahora</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function(){
    $('.btn-recepcionar').click(function(){
        var id = $(this).data('id');
        Swal.fire({
            title: '¿Recepcionar Traspaso?',
            text: "Se registrará el ingreso de los productos a tu almacén y se actualizará el stock.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, recepcionar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.post("./?action=recepcionar_traspaso", { id: id }, function(res) {
                    if(res.success) {
                        Swal.fire('Éxito', 'Traspaso recepcionado con éxito.', 'success').then(()=> window.location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, "json");
            }
        });
    });
});
</script>
