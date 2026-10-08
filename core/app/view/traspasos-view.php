<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class='fas fa-exchange-alt mr-2'></i> Traspasos de Almacén</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="./">Inicio</a></li>
                    <li class="breadcrumb-item active">Traspasos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <a href="./?view=newtraspaso" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Traspaso</a>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="traspasoTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="recibidos-tab" data-toggle="tab" href="#recibidos" role="tab">Recibidos / Entrantes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="enviados-tab" data-toggle="tab" href="#enviados" role="tab">Enviados / Salientes</a>
                    </li>
                </ul>
                <div class="tab-content" id="traspasoTabContent">
                    <!-- RECIBIDOS -->
                    <div class="tab-pane fade show active p-3" id="recibidos" role="tabpanel">
                        <?php
                        $sucursal_id = $_SESSION['sucursal_id'] ?? 1;
                        $recibidos = TraspasoData::getAllByDestino($sucursal_id);
                        if(count($recibidos) > 0){
                        ?>
                        <table class="table table-bordered table-hover datatable text-sm">
                            <thead class="bg-light">
                                <th>Nro</th>
                                <th>Origen</th>
                                <th>Fecha Envío</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </thead>
                            <tbody>
                                <?php foreach($recibidos as $t): ?>
                                <tr>
                                    <td><?php echo $t->serie . '-' . $t->comprobante; ?></td>
                                    <td><?php echo $t->getOrigen()->nombre; ?></td>
                                    <td><?php echo $t->fecha_envio; ?></td>
                                    <td>
                                        <?php if($t->estado == 1): ?>
                                            <span class="badge badge-warning">En Tránsito</span>
                                        <?php elseif($t->estado == 2): ?>
                                            <span class="badge badge-success">Recepcionado</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Anulado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="./?view=onetraspaso&id=<?php echo $t->id; ?>" class="btn btn-xs btn-info" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                                        <?php if($t->estado == 1): ?>
                                            <button class="btn btn-xs btn-success btn-recepcionar" data-id="<?php echo $t->id; ?>" title="Recepcionar Traspaso"><i class="fas fa-check-double"></i> Recepcionar</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php } else { echo "<p class='alert alert-info'>No hay traspasos entrantes.</p>"; } ?>
                    </div>
                    
                    <!-- ENVIADOS -->
                    <div class="tab-pane fade p-3" id="enviados" role="tabpanel">
                        <?php
                        $enviados = TraspasoData::getAllByOrigen($sucursal_id);
                        if(count($enviados) > 0){
                        ?>
                        <table class="table table-bordered table-hover datatable text-sm">
                            <thead class="bg-light">
                                <th>Nro</th>
                                <th>Destino</th>
                                <th>Fecha Envío</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </thead>
                            <tbody>
                                <?php foreach($enviados as $t): ?>
                                <tr>
                                    <td><?php echo $t->serie . '-' . $t->comprobante; ?></td>
                                    <td><?php echo $t->getDestino()->nombre; ?></td>
                                    <td><?php echo $t->fecha_envio; ?></td>
                                    <td>
                                        <?php if($t->estado == 1): ?>
                                            <span class="badge badge-warning">En Tránsito</span>
                                        <?php elseif($t->estado == 2): ?>
                                            <span class="badge badge-success">Recepcionado</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Anulado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="./?view=onetraspaso&id=<?php echo $t->id; ?>" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php } else { echo "<p class='alert alert-info'>No has enviado ningún traspaso.</p>"; } ?>
                    </div>
                </div>
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
