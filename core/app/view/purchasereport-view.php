<?php
$months_to_analyze = isset($_GET["ma"]) ? intval($_GET["ma"]) : 4;
$months_to_provision = isset($_GET["mp"]) ? intval($_GET["mp"]) : 2;

// Obtenemos los datos desde el modelo
$proposals = OperationData::getPurchaseProposal($months_to_analyze);
$data_report = [];

foreach($proposals as $r){
    $avg_monthly = $r->total_sold / $months_to_analyze;
    $provision_needed = $avg_monthly * $months_to_provision;
    $proposed_purchase = $provision_needed - $r->stock_actual;
    
    if($proposed_purchase < 0) $proposed_purchase = 0;

    $data_report[] = [
        "id" => $r->id,
        "name" => $r->producto,
        "stock" => $r->stock_actual,
        "total_sold" => $r->total_sold,
        "avg" => $avg_monthly,
        "proposal" => ceil($proposed_purchase)
    ];
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-shopping-cart text-success mr-2"></i> Propuesta de Compra Sugerida
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mt-2">
                    <li class="breadcrumb-item"><a href="./?view=home">Inicio</a></li>
                    <li class="breadcrumb-item">Reportes</li>
                    <li class="breadcrumb-item active text-success">Propuesta Compra</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Filtros de búsqueda -->
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i>
                    Filtros de búsqueda
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="get" action="./index.php">
                    <input type="hidden" name="view" value="purchasereport">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ventas de los últimos (Meses)</label>
                                <select name="ma" class="form-control select2bs4">
                                    <option value="1" <?=$months_to_analyze==1?'selected':''?>>1 mes</option>
                                    <option value="2" <?=$months_to_analyze==2?'selected':''?>>2 meses</option>
                                    <option value="3" <?=$months_to_analyze==3?'selected':''?>>3 meses</option>
                                    <option value="4" <?=$months_to_analyze==4?'selected':''?>>4 meses (Recomendado)</option>
                                    <option value="6" <?=$months_to_analyze==6?'selected':''?>>6 meses</option>
                                    <option value="12" <?=$months_to_analyze==12?'selected':''?>>1 año</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Stock para los próximos (Meses)</label>
                                <select name="mp" class="form-control select2bs4">
                                    <option value="1" <?=$months_to_provision==1?'selected':''?>>1 mes</option>
                                    <option value="2" <?=$months_to_provision==2?'selected':''?>>2 meses (Estándar)</option>
                                    <option value="3" <?=$months_to_provision==3?'selected':''?>>3 meses</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" style="margin-top: 32px;">
                                <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                    <i class="fas fa-sync-alt"></i> Recalcular Análisis
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Resultados del Análisis de Reposición
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-purchase-proposal" class="table table-bordered table-striped" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 50px;">N°</th>
                                        <th>Producto</th>
                                        <th class="text-center">Stock Actual</th>
                                        <th class="text-center">Vendidos (<?=$months_to_analyze?> meses)</th>
                                        <th class="text-center">Promedio Mensual</th>
                                        <th class="text-right">Compra Propuesta (<?=$months_to_provision?> meses)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $n = 1;
                                    foreach($data_report as $item): 
                                        $stock_class = ($item['stock'] <= ($item['avg'] * 0.5)) ? 'text-danger font-weight-bold' : 'text-dark';
                                    ?>
                                    <tr class="text-sm">
                                        <td><?=$n++?></td>
                                        <td>
                                            <span class="font-weight-bold text-primary"><?=$item['name']?></span>
                                        </td>
                                        <td class="text-center <?=$stock_class?>">
                                            <?=number_format($item['stock'], 0)?>
                                        </td>
                                        <td class="text-center">
                                            <?=number_format($item['total_sold'], 0)?>
                                        </td>
                                        <td class="text-center text-muted">
                                            <?=number_format($item['avg'], 1)?>
                                        </td>
                                        <td class="text-right" data-order="<?=$item['proposal']?>">
                                            <?php if($item['proposal'] > 0): ?>
                                                <span class="badge badge-success px-3 py-2 shadow-sm" style="font-size: 1rem;">
                                                    + <?=$item['proposal']?> <small>uds</small>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-check-circle text-gray mr-1"></i> Stock Suficiente</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
    .bg-light-success { background-color: rgba(40, 167, 69, 0.05); }
</style>

<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#table-purchase-proposal')) {
            $('#table-purchase-proposal').DataTable().destroy();
        }

        $('#table-purchase-proposal').DataTable({
            "responsive": true, 
            "lengthChange": true,
            "autoWidth": false,
            "pageLength": 15,
            "lengthMenu": [[10, 15, 20, 50, 100, -1], [10, 15, 20, 50, 100, "Todos"]],
            "language": {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sSearch":         "Buscar:",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                }
            },
            "order": [[5, "desc"]], 
            "dom": '<"row"<"col-md-3"l><"col-md-6 text-center"B><"col-md-3"f>>rtip',
            "buttons": [
                { 
                    extend: "copy", 
                    text: "<i class='fas fa-copy'></i> Copiar",
                    className: "btn btn-primary btn-sm"
                }, 
                { 
                    extend: "csv", 
                    text: "<i class='fas fa-file-csv'></i> CSV",
                    className: "btn btn-primary btn-sm"
                }, 
                { 
                    extend: "excel", 
                    text: "<i class='fas fa-file-excel'></i> Excel",
                    className: "btn btn-success btn-sm",
                    title: "Reporte_Sugerencia_Compra_<?=date('d-m-Y')?>"
                },
                { 
                    extend: "pdf", 
                    text: "<i class='fas fa-file-pdf'></i> PDF",
                    className: "btn btn-danger btn-sm",
                    title: "Reporte de Sugerencia de Compra - <?=date('d/m/Y')?>"
                },
                { 
                    extend: "print", 
                    text: "<i class='fas fa-print'></i> Imprimir",
                    className: "btn btn-info btn-sm"
                }
            ]
        });
    });
</script>
