<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Reporte de Ventas Mensuales por Producto</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="./?view=home">Inicio</a></li>
                    <li class="breadcrumb-item active">Reporte Mensual</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="filterForm" class="form-horizontal" method="get">
                    <input type="hidden" name="view" value="monthlyreport">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label for="sd" class="col-sm-4 col-form-label">Desde:</label>
                                <div class="col-sm-8">
                                    <input type="date" name="sd" id="sd" value="<?php echo isset($_GET['sd']) ? $_GET['sd'] : date("Y-m-01");; ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label for="ed" class="col-sm-4 col-form-label">Hasta:</label>
                                <div class="col-sm-8">
                                    <input type="date" name="ed" id="ed" value="<?php echo isset($_GET['ed']) ? $_GET['ed'] : date("Y-m-d");; ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Resumen de Ventas por Producto (Solo con movimientos)</h3>
                <div class="card-tools">
                    <button id="btnExportExcelCustom" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Exportar a Excel (Formato)
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php
                $sd = isset($_GET['sd']) && $_GET['sd'] != "" ? $_GET['sd'] : date('Y-m-01');
                $ed = isset($_GET['ed']) && $_GET['ed'] != "" ? $_GET['ed'] : date('Y-m-d');
                $reports = OperationData::getMonthlySalesSummary($sd, $ed);
                if(count($reports) > 0):
                ?>
                <div class="table-responsive">
                    <table id="tableMonthlyReport" class="table table-bordered table-striped table-hover" style="width:100%">
                        <thead class="thead-dark">
                            <tr>
                                <th>N°</th>
                                <th>Producto</th>
                                <th>Stock Actual</th>
                                <th>Cantidad Vendida</th>
                                <th>Monto Total</th>
                                <th>Meses con Ventas</th>
                                <th>Promedio Mensual (Cant.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $nro = 1;
                            foreach($reports as $report): 
                            ?>
                            <tr>
                                <td><?php echo $nro++; ?></td>
                                <td><?php echo $report->name; ?></td>
                                <td class="text-center"><strong><?php echo (int)$report->stock; ?></strong></td>
                                <td class="text-center"><?php echo (int)$report->total_qty; ?></td>
                                <td>S/ <?php echo number_format($report->total_amount, 2); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?php echo $report->months_list; ?></span>
                                    <small>(<?php echo $report->total_months; ?>)</small>
                                </td>
                                <td class="text-center"><?php echo (int)round($report->avg_qty_month); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="icon fas fa-info"></i> No se encontraron productos con movimientos en el rango seleccionado.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-info card-outline mt-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Desglose Detallado por Mes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php
                $details = OperationData::getMonthlySalesDetails($sd, $ed);
                if(count($details) > 0):
                ?>
                <div class="table-responsive">
                    <table id="tableMonthlyDetails" class="table table-bordered table-striped table-hover" style="width:100%">
                        <thead class="thead-dark">
                            <tr>
                                <th>Año</th>
                                <th>Mes</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Venta Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                            foreach($details as $detail): 
                            ?>
                            <tr>
                                <td><?php echo $detail->anio; ?></td>
                                <td><?php echo $meses[$detail->mes]; ?></td>
                                <td><?php echo $detail->producto; ?></td>
                                <td class="text-center"><?php echo (int)$detail->cantidad_total; ?></td>
                                <td>S/ <?php echo number_format($detail->total_venta, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="icon fas fa-info"></i> No hay detalles mensuales para el rango seleccionado.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
window.addEventListener('load', function() {
    if (typeof jQuery !== 'undefined') {
        $(document).ready(function() {
            var languageConfig = {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "No se encontraron resultados",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "copy": "Copiar",
                    "copyTitle": "Copiado al portapapeles",
                    "copySuccess": {
                        _: "Se copiaron %d filas",
                        1: "Se copió 1 fila"
                    },
                    "print": "Imprimir",
                    "csv": "CSV",
                    "excel": "Excel",
                    "pdf": "PDF",
                    "colvis": "Columnas visibles"
                }
            };

            var buttonsConfig = [
                { extend: "copy", text: "Copiar", className: "btn btn-primary btn-sm" },
                { extend: "csv", text: "CSV", className: "btn btn-primary btn-sm" },
                { extend: "excel", text: "Excel", className: "btn btn-primary btn-sm" },
                { extend: "pdf", text: "PDF", className: "btn btn-primary btn-sm" },
                { extend: "print", text: "Imprimir", className: "btn btn-primary btn-sm" },
                { extend: "colvis", text: "Columnas", className: "btn btn-primary btn-sm" }
            ];

            $("#tableMonthlyReport").DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "order": [[5, "desc"]],
                "dom": '<"row"<"col-md-3"l><"col-md-6 text-center"B><"col-md-3"f>>rtip',
                "buttons": buttonsConfig,
                "language": languageConfig
            });

            $("#tableMonthlyDetails").DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "order": [[0, "desc"], [1, "desc"]],
                "dom": '<"row"<"col-md-3"l><"col-md-6 text-center"B><"col-md-3"f>>rtip',
                "buttons": buttonsConfig,
                "language": languageConfig
            });

            $("#btnExportExcelCustom").click(function() {
                var sd = $("#sd").val();
                var ed = $("#ed").val();
                window.location.href = "./?view=excel_monthlyreport&sd=" + sd + "&ed=" + ed;
            });
        });
    }
});
</script>
