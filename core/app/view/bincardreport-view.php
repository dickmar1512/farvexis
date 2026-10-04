<?php
$sd = isset($_GET["sd"]) ? $_GET["sd"] : date("Y-m-01");
$ed = isset($_GET["ed"]) ? $_GET["ed"] : date("Y-m-t");
$user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : 0;

$users = UserData::getAll();
$operations = [];

if (isset($_GET["sd"]) && isset($_GET["ed"])) {
    $operations = OperationData::getBincartReport($sd, $ed, $user_id);
} else {
    // Si no se han enviado parámetros pero es la primera vez, cargar por defecto
    $operations = OperationData::getBincartReport($sd, $ed, $user_id);
}

// Mapear el ID de tipo de comprobante a su nombre (Factura, Boleta, etc.)
function getTipoComprobanteName($id) {
    if ($id == '1' || $id == '01') return 'FACTURA';
    if ($id == '3' || $id == '03') return 'BOLETA';
    if ($id == '7' || $id == '07') return 'NOTA DE CRÉDITO';
    if ($id == '8' || $id == '08') return 'NOTA DE DÉBITO';
    if ($id == '60') return 'INGRESO DIVERSO';
    if ($id == '65') return 'SALIDA DIVERSA';
    if ($id == '70') return 'ORDEN DE VENTA';
    
    // Si no tiene tipo asignado, puede ser una operación de ajuste de stock o anulación general
    if (empty($id)) return 'SALDO';
    return 'DOC-'.$id;
}
?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fa fa-list-alt text-primary"></i> Reporte Bincard (Kardex Detallado)</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="./?view=home">Inicio</a></li>
                    <li class="breadcrumb-item active">Bincart</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <form method="GET" action="./">
                    <input type="hidden" name="view" value="bincardreport">
                    <div class="row">
                        <div class="col-md-3">
                            <label><i class="fa fa-calendar-alt text-muted"></i> Desde:</label>
                            <input type="date" name="sd" value="<?=$sd?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label><i class="fa fa-calendar-alt text-muted"></i> Hasta:</label>
                            <input type="date" name="ed" value="<?=$ed?>" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label><i class="fa fa-user text-muted"></i> Usuario Responsable:</label>
                            <select name="user_id" class="form-control select2bs4">
                                <option value="0">-- TODOS LOS USUARIOS --</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?=$u->id?>" <?=($user_id==$u->id)?'selected':''?>><?=$u->name." ".$u->lastname?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body">
                <?php if(count($operations)>0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm" id="table-bincard">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center">FECHA</th>
                                    <th rowspan="2" class="align-middle text-center">PRODUCTO</th>
                                    <th rowspan="2" class="align-middle text-center">TIPO MOVIMIENTO</th>
                                    <th colspan="2" class="text-center">COMPROBANTE</th>
                                    <th rowspan="2" class="align-middle text-center">USUARIO</th>
                                    <th colspan="3" class="text-center bg-info">MOVIMIENTOS</th>
                                </tr>
                                <tr>
                                    <th class="text-center">TIPO</th>
                                    <th class="text-center">NRO.</th>
                                    <th class="text-center bg-success">ENTRADA</th>
                                    <th class="text-center bg-danger">SALIDA</th>
                                    <th class="text-center bg-primary">SALDO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($operations as $op): 
                                    $is_inactivo = ($op->estado_op == 0);
                                    
                                    // Row styling for cancelled operations
                                    $tr_class = $is_inactivo ? 'text-muted bg-light' : '';
                                    
                                    $entrada = ($op->operation_type_id == 1) ? $op->q : '';
                                    $salida = ($op->operation_type_id == 2) ? $op->q : '';
                                    
                                    // Si la operación está inactiva, se tacha la cantidad para que no confunda el saldo visualmente
                                    if ($is_inactivo) {
                                        $entrada = $entrada ? "<s>$entrada</s>" : '';
                                        $salida = $salida ? "<s>$salida</s>" : '';
                                    }
                                    
                                    $comprobante = $op->serie ? $op->serie.'-'.str_pad($op->comprobante, 8, "0", STR_PAD_LEFT) : $op->comprobante;

                                    // Determinar Tipo de Movimiento
                                    $serie_op = strtoupper(trim($op->serie ?? ''));
                                    $tipo_comp = $op->tipo_comprobante_id ?? '';
                                    if ($op->operation_type_id == 1) {
                                        // Entradas
                                        if ($tipo_comp == '60') {
                                            $tipo_mov = '<span class="badge badge-secondary"><i class="fas fa-exchange-alt mr-1"></i>INGRESO</span>';
                                        } else {
                                            if ($tipo_comp == '1' || $tipo_comp == '3') {
                                                $tipo_mov = '<span class="badge badge-success"><i class="fas fa-cart-arrow-down mr-1"></i>COMPRA</span>';
                                            } else{
                                                $tipo_mov = '<span class="badge badge-info"><i class="fas fa-rotate mr-1"></i>SALDO</span>';
                                            }    
                                        }
                                    } else {
                                        // Salidas
                                        if ($tipo_comp == '65') {
                                            $tipo_mov = '<span class="badge badge-warning"><i class="fas fa-exchange-alt mr-1"></i>SALIDA</span>';
                                        }elseif($tipo_comp == '70'){
                                            $tipo_mov = '<span class="badge badge-warning"><i class="fas fa-shopping-cart mr-1"></i>NOTA</span>';
                                        }else{        
                                            $tipo_mov = '<span class="badge badge-danger"><i class="fas fa-shopping-cart mr-1"></i>VENTA</span>';
                                        }
                                    }
                                    if ($is_inactivo) {
                                        $tipo_mov = '<span class="badge badge-dark"><i class="fas fa-ban mr-1"></i>ANULADO</span>';
                                    }
                                ?>
                                <tr class="<?=$tr_class?>">
                                    <td class="text-center align-middle"><?=date("d/m/Y", strtotime($op->fecha))?></td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold"><?=$op->producto?></div>
                                        <?php if(isset($op->laboratorio) && trim($op->laboratorio) != ""): ?>
                                            <small class="badge badge-light border text-muted"><?=$op->laboratorio?></small>
                                        <?php endif; ?>
                                        <?php if(isset($op->barcode) && trim($op->barcode) != ""): ?>
                                            <small class="badge badge-light border text-muted"><i class="fas fa-barcode"></i> <?=$op->barcode?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle"><?=$tipo_mov?></td>
                                    <td class="text-center align-middle"><?=getTipoComprobanteName($op->tipo_comprobante_id)?></td>
                                    <td class="text-center align-middle"><?=$comprobante?></td>
                                    <td class="text-center align-middle"><?=$op->usuario?></td>
                                    <td class="text-center text-success font-weight-bold align-middle"><?=$entrada?></td>
                                    <td class="text-center text-danger font-weight-bold align-middle"><?=$salida?></td>
                                    <td class="text-center text-primary font-weight-bold align-middle" style="font-size: 1.1em;"><?=$op->saldo?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fa fa-info-circle fa-2x mb-2"></i><br>
                        No se encontraron movimientos de inventario para los filtros seleccionados.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        // Usamos un pequeño retraso para asegurar que nuestra configuración 
        // se aplique después de cualquier script global de la plantilla.
        setTimeout(function() {
            if ($.fn.DataTable.isDataTable('#table-bincard')) {
                $('#table-bincard').DataTable().destroy();
            }
            
            $('#table-bincard').DataTable({
                "ordering": true,
                "order": [[1, "asc"]], // Forzar orden por Producto (index 1)
                "orderCellsTop": true, // Importante para cabeceras con rowspan/colspan
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
                "dom": 'Bfrtip',
                "buttons": [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel"></i> Exportar a Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Reporte_Bincard_<?php echo date("Ymd"); ?>'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print"></i> Imprimir',
                        className: 'btn btn-info btn-sm'
                    }
                ]
            });
        }, 500);
    });
</script>
