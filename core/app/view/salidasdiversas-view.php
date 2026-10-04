<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-sign-out-alt text-warning mr-2"></i> Salidas Diversas (Serie 004)</h1>
                <p class="text-muted text-sm">Registro de productos vencidos, malogrados o bajas de inventario.</p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="./?view=newsalidadiversa" class="btn btn-warning shadow-sm">
                    <i class="fas fa-plus-circle mr-1"></i> Nueva Salida Diversa
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header py-2">
                <h3 class="card-title text-sm font-weight-bold uppercase">Historial de Salidas Diversas</h3>
            </div>
            <div class="card-body">
                <?php
                // Tipo comprobante 60 = Salida Diversa, Serie 004
                $sql = "SELECT * FROM sell WHERE tipo_comprobante='65' AND serie='004' ORDER BY created_at DESC";
                $query = Executor::doit($sql);
                $salidas = Model::many($query[0], new SellData());
                
                if(count($salidas) > 0):
                ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm table-valign-middle" id="table-salidas">
                        <thead class="bg-light text-xs uppercase text-muted">
                            <tr>
                                <th class="pl-3">ID</th>
                                <th>Nro. Documento</th>
                                <th>Fecha</th>
                                <th>Motivo / Observación</th>
                                <th class="text-right">Items</th>
                                <th class="text-right pr-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($salidas as $salida): 
                                $operations = OperationData::getAllProductsBySellId($salida->id);
                                $items_count = count($operations);
                            ?>
                            <tr class="text-sm">
                                <td class="pl-3 text-muted">#<?php echo $salida->id; ?></td>
                                <td class="font-weight-bold"><?php echo $salida->serie . "-" . $salida->comprobante; ?></td>
                                <td><?php echo date("d/m/Y H:i", strtotime($salida->created_at)); ?></td>
                                <td><span class="text-muted italic"><?php echo $salida->observacion ? $salida->observacion : "Sin observación"; ?></span></td>
                                <td class="text-right font-weight-bold"><?php echo $items_count; ?></td>
                                <td class="text-right pr-3">
                                    <a href="<?= url('onesellsd', ['id' => $salida->id]) ?>" class="btn btn-xs btn-outline-info shadow-sm" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="docPdf.php?id=<?php echo $salida->id; ?>" target="_blank" class="btn btn-xs btn-outline-secondary shadow-sm ml-1" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3 opacity-2"></i>
                    <h5 class="text-muted">No se registraron salidas diversas aún</h5>
                    <p class="text-xs text-muted">Use el botón "Nueva Salida Diversa" para dar de baja productos.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        if ($('#table-salidas').length) {
            $('#table-salidas').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json" },
                "pageLength": 25,
                "ordering": true,
                "responsive": true
            });
        }
    });
</script>
