<?php
// ============================================================
// Reporte de Productos Vencidos y Por Vencer
// Lógica de vencimiento idéntica a searchproduct-action.php
// ============================================================

// Función auxiliar: calcula estado de vencimiento igual que searchproduct-action.php
function calcularEstadoVencimiento($fecha_venc_raw) {
    if (empty($fecha_venc_raw)) {
        return [
            'dias_restantes'  => PHP_INT_MAX,
            'badge_class'     => 'secondary',
            'mensaje'         => 'Sin fecha de vencimiento',
            'fecha_formateada'=> '—',
            'estado'          => 'sin_fecha',
        ];
    }
    $fecha_venc      = strtotime($fecha_venc_raw);
    $dias_restantes  = ceil(($fecha_venc - time()) / (60 * 60 * 24));
    $fecha_formateada = date('d/m/Y', $fecha_venc);

    if ($dias_restantes <= 0) {
        $badge_class = 'danger';
        $mensaje     = 'Vence: ' . $fecha_formateada . ' — ¡Vencido!, hace ' . abs($dias_restantes) . ' días';
        $estado      = 'vencido';
    } elseif ($dias_restantes <= 90) {
        $badge_class = 'danger';
        $mensaje     = 'Vence: ' . $fecha_formateada . ' — ¡Faltan ' . $dias_restantes . ' días para vencer!';
        $estado      = 'critico';
    } elseif ($dias_restantes <= 120) {
        $badge_class = 'warning';
        $mensaje     = 'Vence: ' . $fecha_formateada . ' — ¡Faltan ' . $dias_restantes . ' días para vencer!';
        $estado      = 'proximo';
    } else {
        $badge_class = 'success';
        $mensaje     = 'Vence: ' . $fecha_formateada;
        $estado      = 'vigente';
    }

    return compact('dias_restantes', 'badge_class', 'mensaje', 'fecha_formateada', 'estado');
}

// Carga todos los productos activos
$todos_productos = ProductData::getAll2();

// Clasifica con la misma lógica que searchproduct-action.php
$vencidos     = [];
$criticos     = []; // ≤ 90 días
$proximos     = []; // 91–120 días
$vigentes     = [];
$sin_fecha    = [];

foreach ($todos_productos as $p) {
    $info = calcularEstadoVencimiento($p->fecha_venc);
    $p->_venc = $info;
    switch ($info['estado']) {
        case 'vencido':   $vencidos[]  = $p; break;
        case 'critico':   $criticos[]  = $p; break;
        case 'proximo':   $proximos[]  = $p; break;
        case 'vigente':   $vigentes[]  = $p; break;
        default:          $sin_fecha[] = $p; break;
    }
}

// Filtro solicitado desde URL
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';

switch ($filtro) {
    case 'vencidos':  $lista_filtrada = $vencidos;  break;
    case 'criticos':  $lista_filtrada = $criticos;  break;
    case 'proximos':  $lista_filtrada = $proximos;  break;
    case 'vigentes':  $lista_filtrada = $vigentes;  break;
    case 'sin_fecha': $lista_filtrada = $sin_fecha; break;
    default:
        // "todos" muestra: vencidos + críticos + próximos (excluye vigentes y sin fecha)
        $lista_filtrada = array_merge($vencidos, $criticos, $proximos);
        break;
}
?>

<!-- ============================  ESTILOS  ============================ -->
<style>
.rv-stat-card {
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    color: #fff;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s;
    text-decoration: none !important;
}
.rv-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.2); }
.rv-stat-card .rv-icon { font-size: 2.4rem; opacity: .85; }
.rv-stat-card .rv-info h3 { margin: 0; font-size: 2rem; font-weight: 700; line-height: 1; }
.rv-stat-card .rv-info p  { margin: 0; font-size: .8rem; opacity: .9; }

.rv-card-danger  { background: linear-gradient(135deg, #c0392b, #e74c3c); }
.rv-card-orange  { background: linear-gradient(135deg, #e67e22, #f39c12); }
.rv-card-warning { background: linear-gradient(135deg, #d4ac0d, #f1c40f); color: #333 !important; }
.rv-card-success { background: linear-gradient(135deg, #1e8449, #27ae60); }
.rv-card-secondary { background: linear-gradient(135deg, #5d6d7e, #808b96); }

.rv-filter-active { box-shadow: 0 0 0 3px #fff, 0 0 0 5px rgba(0,0,0,.5) !important; }

.rv-table th { font-size: .82rem; vertical-align: middle; }
.rv-table td { font-size: .84rem; vertical-align: middle; }

.rv-badge-dias {
    font-size: .78rem;
    padding: 3px 8px;
    border-radius: 20px;
}

/* Fila crítica: rojo claro (distinto al rojo intenso de vencidos) */
.rv-row-critico {
    background-color: #fde8e8 !important;
}

@media print {
    .rv-no-print { display: none !important; }
    .content-header { display: none; }
    .rv-print-title { display: block !important; }
}
.rv-print-title { display: none; }
</style>

<!-- ============================  CABECERA  ============================ -->
<div class="content-header rv-no-print">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-calendar-times text-danger mr-2"></i> Reporte de Vencimientos
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right bg-transparent p-0 mt-2">
                    <li class="breadcrumb-item"><a href="./?view=home">Inicio</a></li>
                    <li class="breadcrumb-item active text-danger">Vencimientos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- ============================  CONTENIDO  ============================ -->
<section class="content">
<div class="container-fluid">

    <!-- Título para impresión -->
    <div class="rv-print-title mb-3">
        <h3><i class="fas fa-calendar-times"></i> Reporte de Productos Vencidos y Por Vencer</h3>
        <p class="text-muted">Generado el: <?php echo date('d/m/Y H:i'); ?> &nbsp;|&nbsp; Filtro: <strong><?php echo htmlspecialchars($filtro); ?></strong></p>
    </div>

    <!-- ============  TARJETAS RESUMEN  ============ -->
    <div class="row mb-4 rv-no-print">
        <!-- Vencidos -->
        <div class="col-6 col-md-4 col-lg mb-3">
            <a href="./?view=reportevencimientos&filtro=vencidos"
               class="rv-stat-card rv-card-danger <?php echo $filtro=='vencidos' ? 'rv-filter-active' : ''; ?>">
                <div class="rv-icon"><i class="fas fa-skull-crossbones"></i></div>
                <div class="rv-info">
                    <h3><?php echo count($vencidos); ?></h3>
                    <p>Vencidos</p>
                </div>
            </a>
        </div>
        <!-- Críticos ≤90d -->
        <div class="col-6 col-md-4 col-lg mb-3">
            <a href="./?view=reportevencimientos&filtro=criticos"
               class="rv-stat-card rv-card-orange <?php echo $filtro=='criticos' ? 'rv-filter-active' : ''; ?>">
                <div class="rv-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="rv-info">
                    <h3><?php echo count($criticos); ?></h3>
                    <p>Críticos (≤90 días)</p>
                </div>
            </a>
        </div>
        <!-- Próximos 91–120d -->
        <div class="col-6 col-md-4 col-lg mb-3">
            <a href="./?view=reportevencimientos&filtro=proximos"
               class="rv-stat-card rv-card-warning <?php echo $filtro=='proximos' ? 'rv-filter-active' : ''; ?>">
                <div class="rv-icon"><i class="fas fa-clock"></i></div>
                <div class="rv-info">
                    <h3><?php echo count($proximos); ?></h3>
                    <p>Próximos (91–120 días)</p>
                </div>
            </a>
        </div>
        <!-- Vigentes -->
        <div class="col-6 col-md-4 col-lg mb-3">
            <a href="./?view=reportevencimientos&filtro=vigentes"
               class="rv-stat-card rv-card-success <?php echo $filtro=='vigentes' ? 'rv-filter-active' : ''; ?>">
                <div class="rv-icon"><i class="fas fa-check-circle"></i></div>
                <div class="rv-info">
                    <h3><?php echo count($vigentes); ?></h3>
                    <p>Vigentes (&gt;120 días)</p>
                </div>
            </a>
        </div>
        <!-- Sin Fecha -->
        <div class="col-6 col-md-4 col-lg mb-3">
            <a href="./?view=reportevencimientos&filtro=sin_fecha"
               class="rv-stat-card rv-card-secondary <?php echo $filtro=='sin_fecha' ? 'rv-filter-active' : ''; ?>">
                <div class="rv-icon"><i class="fas fa-question-circle"></i></div>
                <div class="rv-info">
                    <h3><?php echo count($sin_fecha); ?></h3>
                    <p>Sin fecha registrada</p>
                </div>
            </a>
        </div>
        <!-- Ver todos con alerta -->
        <div class="col-6 col-md-4 col-lg mb-3">
            <a href="./?view=reportevencimientos&filtro=todos"
               class="rv-stat-card rv-card-danger <?php echo $filtro=='todos' ? 'rv-filter-active' : ''; ?>"
               style="background: linear-gradient(135deg,#6c3483,#9b59b6);">
                <div class="rv-icon"><i class="fas fa-list-alt"></i></div>
                <div class="rv-info">
                    <h3><?php echo count($vencidos)+count($criticos)+count($proximos); ?></h3>
                    <p>Con alerta (todos)</p>
                </div>
            </a>
        </div>
    </div>

    <!-- ============  CARD TABLA  ============ -->
    <div class="card card-outline card-danger shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <?php
                $titulos = [
                    'todos'     => '<i class="fas fa-list-alt text-danger mr-1"></i> Todos los productos con alerta de vencimiento',
                    'vencidos'  => '<i class="fas fa-skull-crossbones text-danger mr-1"></i> Productos Vencidos',
                    'criticos'  => '<i class="fas fa-exclamation-circle text-warning mr-1"></i> Productos Críticos (vencen en ≤ 90 días)',
                    'proximos'  => '<i class="fas fa-clock text-warning mr-1"></i> Productos Próximos a vencer (91 – 120 días)',
                    'vigentes'  => '<i class="fas fa-check-circle text-success mr-1"></i> Productos Vigentes (> 120 días)',
                    'sin_fecha' => '<i class="fas fa-question-circle text-secondary mr-1"></i> Productos sin fecha de vencimiento',
                ];
                echo $titulos[$filtro] ?? $titulos['todos'];
                ?>
                <span class="badge badge-secondary ml-2"><?php echo count($lista_filtrada); ?> producto(s)</span>
            </h3>
            <div class="card-tools rv-no-print">
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary" title="Imprimir">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <?php if (count($lista_filtrada) > 0): ?>
            <div class="table-responsive">
                <table id="tabla-vencimientos" class="table table-bordered table-hover rv-table mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Producto</th>
                            <th>Laboratorio</th>
                            <th class="text-center" style="width:90px;">Stock</th>
                            <th class="text-center" style="width:110px;">Fecha Venc.</th>
                            <th class="text-center" style="width:120px;">Días restantes</th>
                            <th class="text-center" style="width:130px;">Estado</th>
                            <th class="rv-no-print text-center" style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Ordena por días restantes (los más urgentes primero)
                    usort($lista_filtrada, function($a, $b) {
                        $da = ($a->_venc['dias_restantes'] === PHP_INT_MAX) ? 99999 : $a->_venc['dias_restantes'];
                        $db = ($b->_venc['dias_restantes'] === PHP_INT_MAX) ? 99999 : $b->_venc['dias_restantes'];
                        return $da <=> $db;
                    });
                    $i = 1;
                    foreach ($lista_filtrada as $p):
                        $v = $p->_venc;

                        // Clase de fila
                        if ($v['estado'] === 'vencido') {
                            $row_class = 'table-danger';
                        } elseif ($v['estado'] === 'critico') {
                            $row_class = 'rv-row-critico';  // rojo claro personalizado
                        } elseif ($v['estado'] === 'proximo') {
                            $row_class = 'table-warning';
                        } else {
                            $row_class = '';
                        }

                        // Badge texto días restantes
                        if ($v['estado'] === 'vencido') {
                            $dias_txt    = 'Vencido hace ' . abs($v['dias_restantes']) . ' días';
                            $dias_badge  = 'badge-danger';
                        } elseif ($v['dias_restantes'] === PHP_INT_MAX) {
                            $dias_txt    = '—';
                            $dias_badge  = 'badge-secondary';
                        } else {
                            $dias_txt    = $v['dias_restantes'] . ' días';
                            $dias_badge  = ($v['estado'] === 'proximo') ? 'badge-warning text-dark' : 'badge-' . $v['badge_class'];
                        }
                    ?>
                    <tr class="<?php echo $row_class; ?>">
                        <td class="text-center text-muted"><?php echo $i++; ?></td>
                        <td>
                            <span class="font-weight-bold text-primary"><?php echo htmlspecialchars($p->name); ?></span>
                            <?php if (!empty($p->principio_activo)): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($p->principio_activo); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty(trim($p->laboratorio ?? ''))): ?>
                                <span class="badge badge-light border text-dark"><?php echo htmlspecialchars($p->laboratorio); ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($p->is_stock == 0): ?>
                                <span class="badge badge-success">∞</span>
                            <?php else: ?>
                                <span class="badge <?php echo $p->stock <= 0 ? 'badge-danger' : 'badge-info'; ?>">
                                    <?php echo (int)$p->stock; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center font-weight-bold">
                            <?php echo $v['fecha_formateada']; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge rv-badge-dias <?php echo $dias_badge; ?>">
                                <?php echo $dias_txt; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php
                            $estados_label = [
                                'vencido'   => '<span class="badge badge-danger"><i class="fas fa-skull-crossbones"></i> Vencido</span>',
                                'critico'   => '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Crítico</span>',
                                'proximo'   => '<span class="badge badge-warning text-dark"><i class="fas fa-clock"></i> Próximo</span>',
                                'vigente'   => '<span class="badge badge-success"><i class="fas fa-check"></i> Vigente</span>',
                                'sin_fecha' => '<span class="badge badge-secondary"><i class="fas fa-question"></i> Sin fecha</span>',
                            ];
                            echo $estados_label[$v['estado']] ?? '';
                            ?>
                        </td>
                        <td class="text-center rv-no-print">
                            <a href="./?view=history&product_id=<?php echo $p->id; ?>"
                               class="btn btn-xs btn-success" title="Ver historial">
                                <i class="fas fa-history"></i>
                            </a>
                            <a href="./?view=input&product_id=<?php echo $p->id; ?>"
                               class="btn btn-xs btn-primary" title="Abastecer">
                                <i class="fas fa-plus-circle"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div><!-- /.table-responsive -->

            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4 class="text-muted">¡Sin productos en esta categoría!</h4>
                <p class="text-muted">No se encontraron productos con el filtro seleccionado.</p>
                <a href="./?view=reportevencimientos" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Ver todos
                </a>
            </div>
            <?php endif; ?>
        </div><!-- /.card-body -->

        <?php if (count($lista_filtrada) > 0): ?>
        <div class="card-footer text-muted rv-no-print" style="font-size:.82rem;">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>Reglas de clasificación</strong> (idénticas a la búsqueda de productos):
            &nbsp;<span class="badge badge-danger">Vencido</span> = fecha ≤ hoy
            &nbsp;<span class="badge badge-danger">Crítico</span> = ≤ 90 días restantes
            &nbsp;<span class="badge badge-warning text-dark">Próximo</span> = 91–120 días restantes
            &nbsp;<span class="badge badge-success">Vigente</span> = &gt; 120 días restantes
        </div>
        <?php endif; ?>
    </div><!-- /.card -->

</div><!-- /.container-fluid -->
</section>

<!-- DataTables init -->
<script>
$(function () {
    if ($.fn.DataTable) {
        $('#tabla-vencimientos').DataTable({
            "paging"   : true,
            "ordering" : false,   // ya vienen ordenados por días
            "info"     : true,
            "searching": true,
            "pageLength": 25,
            "language" : {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            "columnDefs": [
                { "targets": 7, "orderable": false }  // columna Acciones
            ]
        });
    }
});
</script>
