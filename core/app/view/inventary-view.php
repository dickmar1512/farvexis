<?php
// Obtenemos solo los productos que han tenido movimientos para evitar ruido
$inventory = OperationData::getProductsWithMovement();
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-boxes text-primary mr-2"></i> Control de Inventario Real
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mt-2">
                    <li class="breadcrumb-item"><a href="./?view=home">Inicio</a></li>
                    <li class="breadcrumb-item active text-primary">Inventario</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i> Listado General de Productos y Existencias
                        </h3>
                        <div class="card-tools">
                            <a href="./?view=reportevencimientos" class="btn btn-danger btn-sm text-white mr-2">
                                <i class="fas fa-calendar-times mr-1"></i> Reporte de Vencimientos
                            </a>
                            <a href="./?view=lotes" class="btn btn-primary btn-sm text-white mr-2">
                                <i class="fas fa-layer-group mr-1"></i> Administrar lotes
                            </a>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-inventory" class="table table-bordered table-striped table-hover datatable" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 120px;">Código</th>
                                        <th>Nombre del Producto</th>
                                        <th class="text-center">Stock Mín.</th>
                                        <th class="text-center">Disponible</th>
                                        <th class="text-center">Estado Stock</th>
                                        <th class="text-center" style="width: 180px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inventory as $p): 
                                        $is_low_stock = ($p->is_stock == 1 && $p->stock_real <= $p->inventary_min);
                                        $is_critical = ($p->is_stock == 1 && $p->stock_real <= ($p->inventary_min / 2));
                                        
                                        $status_badge = "";
                                        $row_class = "";
                                        
                                        if($p->is_stock == 0){
                                            $status_badge = '<span class="badge badge-secondary px-2">Ilimitado</span>';
                                        } else if($p->stock_real <= 0){
                                            $status_badge = '<span class="badge badge-danger px-2"><i class="fas fa-times-circle"></i> Agotado</span>';
                                            $row_class = "table-danger";
                                        } else if($is_critical){
                                            $status_badge = '<span class="badge badge-warning px-2"><i class="fas fa-exclamation-triangle"></i> Crítico</span>';
                                            $row_class = "table-warning";
                                        } else if($is_low_stock){
                                            $status_badge = '<span class="badge badge-info px-2"><i class="fas fa-arrow-down"></i> Bajo</span>';
                                        } else {
                                            $status_badge = '<span class="badge badge-success px-2"><i class="fas fa-check"></i> Óptimo</span>';
                                        }
                                    ?>
                                    <tr class="<?=$row_class?>">
                                        <td class="font-weight-bold text-muted text-xs"><?=$p->barcode?></td>
                                        <td>
                                            <span class="font-weight-bold"><?=$p->name?></span>
                                            <?php
                                        // Verificar si la fecha es nula o vacía
                                        if (empty($p->fecha_venc)) {
                                            $fecha_venc = strtotime('2099-01-31 00:00:00');
                                            $dias_restantes = ceil(($fecha_venc - time()) / (60 * 60 * 24));
                                            $badge_class = 'secondary';
                                            $mensaje = 'Sin fecha de vencimiento';
                                            $fecha_formateada = '31/01/2099';
                                        } else {
                                            $fecha_venc = strtotime($p->fecha_venc);
                                            $dias_restantes = ceil(($fecha_venc - time()) / (60 * 60 * 24));
                                            $fecha_formateada = date('d/m/Y', $fecha_venc);
                                            
                                            // Determinar clase del badge y mensaje según días restantes
                                            if ($dias_restantes <= 0) {
                                                $badge_class = 'danger';
                                                $mensaje = 'Vence: ' . $fecha_formateada. ' - ¡Vencido!, hace '.abs($dias_restantes).' dias';
                                            }elseif ($dias_restantes <= 90) {
                                                $badge_class = 'danger';
                                                $mensaje = 'Vence: ' . $fecha_formateada. ' - ¡Faltan ' . $dias_restantes . ' días para vencer!';
                                            } elseif ($dias_restantes <= 120) {
                                                $badge_class = 'warning';
                                                $mensaje = 'Vence: ' . $fecha_formateada. ' - ¡Faltan ' . $dias_restantes . ' días para vencer!';
                                            } else {
                                                $badge_class = 'success';
                                                $mensaje = 'Vence: ' . $fecha_formateada;
                                            }
                                        }
                                        ?>

                                            <small class="badge badge-<?php echo $badge_class; ?> border text-<?php echo ($badge_class == 'warning' ? 'dark' : 'white'); ?>">
                                                <?php echo $mensaje; ?>
                                            </small>
                                        </td>
                                        <td class="text-center text-muted">
                                            <?=$p->is_stock == 1 ? $p->inventary_min : '-'?>
                                        </td>
                                        <td class="text-center">
                                            <h5 class="mb-0 font-weight-bold <?=($p->stock_real <= $p->inventary_min && $p->is_stock == 1) ? 'text-danger' : 'text-dark'?>">
                                                <?=$p->is_stock == 1 ? number_format($p->stock_real, 0) : '∞'?>
                                            </h5>
                                        </td>
                                        <td class="text-center">
                                            <?=$status_badge?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="./?view=input&product_id=<?=$p->id?>" class="btn btn-primary btn-sm" title="Aumentar Stock">
                                                    <i class="fas fa-plus-circle"></i>
                                                </a>
                                                <a href="./?view=history&product_id=<?=$p->id?>" class="btn btn-success btn-sm" title="Ver Historial (Kardex)">
                                                    <i class="fas fa-history"></i> Historial
                                                </a>
                                            </div>
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

<?php
// Script de inicialización específica si fuera necesario, pero ahora usamos el global
?>