<?php
/**
 * Reporte de Ventas Agrupado por Cliente - Versión Pro
 * CarePharm Modernized Report
 */

$clients = PersonData::getClients();

// Obtener parámetros de filtro
$client_id = $_GET["client_id"] ?? "";
$sd = $_GET["sd"] ?? date("Y-m-01"); // Por defecto inicio de mes
$ed = $_GET["ed"] ?? date("Y-m-d");

$is_filtered = (isset($_GET["sd"]) && isset($_GET["ed"]));
$operations = array();

if ($is_filtered) {
    if ($client_id == "") {
        $operations = SellData::getAllByDateOp($sd, $ed, 2);
    } else {
        $operations = SellData::getAllByDateBCOp($client_id, $sd, $ed, 2);
    }
}

// Agrupación por cliente
$grouped_ops = array();
$total_v_neto = 0;
$total_v_desc = 0;
$total_v_final = 0;
$count_v = count($operations);

foreach ($operations as $op) {
    $cid = $op->person_id ?? 0; // 0 para clientes sin registrar o anónimos
    if (!isset($grouped_ops[$cid])) {
        $grouped_ops[$cid] = [
            'client' => $op->getPerson(),
            'docs_count' => 0,
            'total_neto' => 0,
            'total_desc' => 0,
            'total_final' => 0,
            'items' => []
        ];
    }
    
    $grouped_ops[$cid]['docs_count']++;
    $grouped_ops[$cid]['total_neto'] += $op->total;
    $grouped_ops[$cid]['total_desc'] += $op->discount;
    $grouped_ops[$cid]['total_final'] += ($op->total - $op->discount);
    $grouped_ops[$cid]['items'][] = $op;

    $total_v_neto += $op->total;
    $total_v_desc += $op->discount;
    $total_v_final += ($op->total - $op->discount);
}

$avg_ticket = ($count_v > 0) ? ($total_v_final / $count_v) : 0;

// Ordenar por importe total final (descendente)
uasort($grouped_ops, function($a, $b) {
    return $b['total_final'] <=> $a['total_final'];
});
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-users-cog text-info mr-2"></i> Ventas por Cliente
                </h1>
                <p class="text-muted text-sm mb-0">Resumen consolidado de facturación por cliente en el periodo seleccionado.</p>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mt-2">
                    <li class="breadcrumb-item"><a href="./?view=home"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item">Reportes</li>
                    <li class="breadcrumb-item active text-info">Venta x Cliente</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Panel de Filtros -->
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-header py-2">
                <h3 class="card-title text-sm font-weight-bold uppercase"><i class="fas fa-filter mr-1"></i> Filtros de Periodo</h3>
            </div>
            <div class="card-body py-3">
                <form method="get" action="./">
                    <input type="hidden" name="view" value="sellreports">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="text-xs font-weight-bold text-muted mb-1 uppercase">Filtrar por Cliente (Opcional)</label>
                            <select name="client_id" class="form-control form-control-sm select2 shadow-none">
                                <option value="">--- TODOS LOS CLIENTES ---</option>
                                <?php foreach ($clients as $p): ?>
                                    <option value="<?php echo $p->id; ?>" <?php echo ($client_id == $p->id) ? "selected" : ""; ?>>
                                        <?php echo $p->name . " " . $p->lastname . " - " . ($p->numero_documento ?? "S/D"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="text-xs font-weight-bold text-muted mb-1 uppercase">Rango de Fechas</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="sd" value="<?php echo $sd; ?>" class="form-control">
                                <div class="input-group-append"><span class="input-group-text">al</span></div>
                                <input type="date" name="ed" value="<?php echo $ed; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info btn-sm btn-block shadow-sm font-weight-bold">
                                <i class="fas fa-sync-alt mr-1"></i> GENERAR REPORTE
                            </button>
                        </div>
                        <div class="col-md-1 text-center">
                            <?php if ($is_filtered): ?>
                            <a href="./?view=sellreports" class="btn btn-link btn-xs text-muted" title="Limpiar Filtros">
                                <i class="fas fa-eraser"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($is_filtered): ?>
            <!-- Resumen Estadístico -->
            <div class="row mb-4">
                <div class="col-lg-4 col-12">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-xs uppercase font-weight-bold">Total Final Facturado</span>
                            <span class="info-box-number h4 mb-0">S/ <?php echo number_format($total_v_final, 2); ?></span>
                            <div class="progress progress-sm mt-1" style="height: 2px;">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                            <span class="progress-description text-xs text-muted mt-1">
                                <?php echo $count_v; ?> Comprobantes emitidos
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-warning elevation-1 text-white"><i class="fas fa-tag"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-xs uppercase font-weight-bold">Ahorro / Descuentos</span>
                            <span class="info-box-number h4 mb-0 text-warning">S/ <?php echo number_format($total_v_desc, 2); ?></span>
                            <div class="progress progress-sm mt-1" style="height: 2px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo ($total_v_neto > 0) ? ($total_v_desc/$total_v_neto)*100 : 0; ?>%"></div>
                            </div>
                            <span class="progress-description text-xs text-muted mt-1">
                                <?php echo ($total_v_neto > 0) ? number_format(($total_v_desc/$total_v_neto)*100, 1) : 0; ?>% del valor total
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="info-box shadow-sm border">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-basket"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-xs uppercase font-weight-bold">Ticket Promedio</span>
                            <span class="info-box-number h4 mb-0 text-success">S/ <?php echo number_format($avg_ticket, 2); ?></span>
                            <div class="progress progress-sm mt-1" style="height: 2px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                            <span class="progress-description text-xs text-muted mt-1">
                                Promedio por cada transacción
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Resumen por Cliente -->
            <div class="card card-default shadow-sm elevation-1">
                <div class="card-header py-2 bg-light d-flex align-items-center border-bottom-0">
                    <h3 class="card-title text-sm font-weight-bold uppercase mb-0"><i class="fas fa-list-ul mr-1"></i> Resumen Consolidado por Cliente</h3>
                </div>
                <div class="card-body p-0">
                    <?php if (count($grouped_ops) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm table-valign-middle mb-0" id="table-grouped-reports">
                                <thead class="bg-light text-xs uppercase text-muted border-top">
                                    <tr>
                                        <th class="pl-4 py-3">Cliente</th>
                                        <th class="text-center py-3">Nro. Comprobantes</th>
                                        <th class="text-right py-3">Importe Neto</th>
                                        <th class="text-right py-3">Descuentos</th>
                                        <th class="text-right pr-4 py-3">Importe Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grouped_ops as $cid => $data): 
                                        $client = $data['client'];
                                        $client_name = $client ? ($client->name . " " . $client->lastname) : "CLIENTE GENERAL / ANÓNIMO";
                                    ?>
                                    <tr class="text-sm">
                                        <td class="pl-4 py-2">
                                            <a href="javascript:void(0);" class="text-info font-weight-bold btn-view-details" 
                                               data-client-name="<?php echo $client_name; ?>"
                                               data-target="#modal-details-<?php echo $cid; ?>">
                                                <i class="fas fa-external-link-alt mr-1 text-xs opacity-5"></i>
                                                <?php echo $client_name; ?>
                                            </a>
                                            <div class="text-xs text-muted"><?php echo $client ? "DNI/RUC: " . ($client->numero_documento ?? 'S/D') : "Ventas sin registro de cliente"; ?></div>
                                        </td>
                                        <td class="text-center py-2">
                                            <span class="badge badge-light border px-2 py-1"><?php echo $data['docs_count']; ?> docs</span>
                                        </td>
                                        <td class="text-right py-2 text-muted" data-order="<?php echo $data['total_neto']; ?>">S/ <?php echo number_format($data['total_neto'], 2); ?></td>
                                        <td class="text-right py-2 text-warning" data-order="<?php echo $data['total_desc']; ?>">-S/ <?php echo number_format($data['total_desc'], 2); ?></td>
                                        <td class="text-right pr-4 py-2 font-weight-bold text-dark" style="font-size: 1rem;" data-order="<?php echo $data['total_final']; ?>">
                                            S/ <?php echo number_format($data['total_final'], 2); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-light font-weight-bold text-sm">
                                    <tr>
                                        <td class="pl-4 py-3 uppercase">Gran Total Acumulado:</td>
                                        <td class="text-center py-3"><?php echo $count_v; ?> docs</td>
                                        <td class="text-right py-3 text-muted">S/ <?php echo number_format($total_v_neto, 2); ?></td>
                                        <td class="text-right py-3 text-warning">-S/ <?php echo number_format($total_v_desc, 2); ?></td>
                                        <td class="text-right pr-4 py-3 text-success" style="font-size: 1.2rem;">S/ <?php echo number_format($total_v_final, 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search-minus fa-4x text-muted opacity-2 mb-3"></i>
                            <h5 class="text-muted">No se encontraron resultados</h5>
                            <p class="text-xs text-muted mb-0">No hay ventas registradas en el rango seleccionado.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- MODALES (Fuera de la tabla para no romper el layout) -->
            <?php foreach ($grouped_ops as $cid => $data): 
                $client = $data['client'];
                $client_name = $client ? ($client->name . " " . $client->lastname) : "CLIENTE GENERAL / ANÓNIMO";
            ?>
            <div class="modal fade" id="modal-details-<?php echo $cid; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                    <div class="modal-content shadow-lg border-0">
                        <div class="modal-header bg-light py-2">
                            <h5 class="modal-title text-sm font-weight-bold uppercase">
                                <i class="fas fa-file-invoice mr-2 text-info"></i> Detalle de Comprobantes: <?php echo $client_name; ?>
                            </h5>
                            <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-xs text-muted uppercase font-weight-bold">Periodo:</span>
                                    <span class="text-sm ml-2 font-weight-bold"><?php echo date("d/m/Y", strtotime($sd)); ?> - <?php echo date("d/m/Y", strtotime($ed)); ?></span>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-info px-3 py-2" style="font-size: 0.9rem;">Total Cliente: S/ <?php echo number_format($data['total_final'], 2); ?></span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead class="bg-light text-xs text-muted uppercase">
                                        <tr>
                                            <th class="pl-4">Fecha</th>
                                            <th>Comprobante</th>
                                            <th>Concepto/Obs.</th>
                                            <th class="text-right">Subtotal</th>
                                            <th class="text-right text-warning">Desc.</th>
                                            <th class="text-right pr-4">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($data['items'] as $item): ?>
                                        <tr class="text-sm">
                                            <td class="pl-4"><?php echo date("d/m/Y H:i", strtotime($item->created_at)); ?></td>
                                            <td class="font-weight-bold">
                                                <a href="<?= url('onesell', ['id' => $item->id, 'tipodoc' => $item->tipo_comprobante]) ?>" target="_blank" class="text-info">
                                                    <?php echo $item->serie . "-" . $item->comprobante; ?>
                                                </a>
                                            </td>
                                            <td class="text-xs text-muted italic"><?php echo $item->observacion ?? '---'; ?></td>
                                            <td class="text-right">S/ <?php echo number_format($item->total, 2); ?></td>
                                            <td class="text-right text-warning">-S/ <?php echo number_format($item->discount, 2); ?></td>
                                            <td class="text-right pr-4 font-weight-bold text-success">S/ <?php echo number_format($item->total - $item->discount, 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-xs font-weight-bold uppercase" data-dismiss="modal">Cerrar Detalle</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        <?php else: ?>
            <!-- Estado Inicial -->
            <div class="card shadow-none border bg-light">
                <div class="card-body text-center py-5">
                    <div class="mb-3"><i class="fas fa-chart-pie fa-5x text-info opacity-1"></i></div>
                    <h4 class="text-muted font-weight-bold">Dashboard de Ventas Consolidado</h4>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                        Seleccione un rango de fechas y presione <b>Generar Reporte</b> para ver el resumen de facturación agrupado por sus clientes más importantes.
                    </p>
                    <button type="button" onclick="$('form').submit();" class="btn btn-info btn-sm shadow-sm px-4 font-weight-bold">
                        MOSTRAR DATOS DEL MES ACTUAL
                    </button>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
    .opacity-1 { opacity: 0.1; }
    .opacity-5 { opacity: 0.5; }
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
    .table-valign-middle td { vertical-align: middle !important; }
    #table-grouped-reports_wrapper .dt-buttons { margin-bottom: 15px; }
    .btn-view-details:hover { text-decoration: underline !important; color: #17a2b8 !important; }
</style>

<script>
    $(document).ready(function() {
        // Select2 para clientes
        if($('.select2').length) {
            $('.select2').select2({ theme: 'bootstrap4', placeholder: "Filtrar por cliente..." });
        }

        // Abrir modales
        $(document).on('click', '.btn-view-details', function() {
            const target = $(this).data('target');
            $(target).modal('show');
        });

        // DataTable profesional
        if ($('#table-grouped-reports').length) {
            $('#table-grouped-reports').DataTable({
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                "pageLength": 50,
                "ordering": true,
                "order": [[4, "desc"]], // Columna 4 es 'Importe Final'
                "columnDefs": [
                    { "type": "num", "targets": [1, 2, 3, 4] } // Forzar tipo numérico en estas columnas
                ],
                "responsive": true,
                "dom": '<"row d-flex justify-content-between mx-2 py-3"<"col-sm-6"B><"col-sm-6"f>>rtip',
                "buttons": [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel mr-1"></i> Exportar a Excel',
                        className: 'btn btn-success btn-xs shadow-sm',
                        title: 'Resumen_Ventas_Clientes_<?php echo date("Ymd"); ?>'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-1"></i> Imprimir Tabla',
                        className: 'btn btn-dark btn-xs shadow-sm'
                    }
                ]
            });
        }
    });
</script>