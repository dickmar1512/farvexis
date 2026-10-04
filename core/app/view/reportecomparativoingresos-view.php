<?php
// En tu controlador o archivo PHP
$anio = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$cuadroVentas = SellData::getMonthlySalesComparison($anio, 0);
$arrayLimpio = array_filter($cuadroVentas, function($item) {
    return $item['ventas_soles'] !== 'S/ 0.00';
});

// Si quieres reindexar el array (índices desde 0)
$arrayLimpio = array_values($arrayLimpio);

$cuadroVentas = $arrayLimpio;
?>

<!-- Vista AdminLTE3 -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Reporte de Crecimiento Mensual de Ventas
                    </h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <select class="form-control" id="selectYear">
                                <?php
                                $currentYear = date('Y');
                                for ($year = $currentYear; $year >= ($currentYear - 5); $year--) {
                                    $selected = ($year == $anio) ? 'selected' : '';
                                    echo "<option value='$year' $selected>$year</option>";
                                }
                                ?>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-primary" onclick="cargarReporte()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="15%">MES</th>
                                    <th width="25%">VENTAS (SOLES)</th>
                                    <th width="25%">CRECIMIENTO (SOLES)</th>
                                    <th width="20%">CRECIMIENTO (%)</th>
                                    <!-- <th width="15%">TENDENCIA</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($cuadroVentas)): ?>
                                    <?php foreach ($cuadroVentas as $mes): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?php echo $mes['mes']; ?></td>
                                            <td class="text-success font-weight-bold"><?php echo $mes['ventas_soles']; ?></td>
                                            <td>
                                                <?php 
                                                $crecimientoSoles = str_replace('S/ ', '', $mes['crecimiento_soles']);
                                                if ($crecimientoSoles == '-') {
                                                    echo '<span class="text-muted font-weight-bold">' . $mes['crecimiento_soles'] . '</span>';
                                                } elseif ($crecimientoSoles > 0) {
                                                    echo '<span class="text-success font-weight-bold">' . $mes['crecimiento_soles'] . ' <i class="fas fa-arrow-up"></i></span>';
                                                } else {
                                                    echo '<span class="text-danger font-weight-bold">' . $mes['crecimiento_soles'] . ' <i class="fas fa-arrow-down"></i></span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $porcentaje = str_replace('%', '', $mes['crecimiento_porcentaje']);
                                                if ($porcentaje == 0) {
                                                    echo '<span class="text-muted font-weight-bold">' . $mes['crecimiento_porcentaje'] . '</span>';
                                                } elseif ($porcentaje > 0) {
                                                    echo '<span class="text-success font-weight-bold">' . $mes['crecimiento_porcentaje'] . ' <i class="fas fa-arrow-up"></i></span>';
                                                } else {
                                                    echo '<span class="text-danger font-weight-bold">' . $mes['crecimiento_porcentaje'] . ' <i class="fas fa-arrow-down"></i></span>';
                                                }
                                                ?>
                                            </td>
                                            <!-- <td class="text-center">
                                                <?php 
                                                // $porcentaje = str_replace('%', '', $mes['crecimiento_porcentaje']);
                                                // if ($porcentaje > 20) {
                                                //     echo '<span class="badge bg-success"><i class="fas fa-rocket"></i> ALTA</span>';
                                                // } elseif ($porcentaje > 0) {
                                                //     echo '<span class="badge bg-info"><i class="fas fa-chart-line"></i> MEDIA</span>';
                                                // } elseif ($porcentaje == 0) {
                                                //     echo '<span class="badge bg-secondary"><i class="fas fa-minus"></i> ESTABLE</span>';
                                                // } else {
                                                //     echo '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> BAJA</span>';
                                                // }
                                                ?>
                                            </td> -->
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                            No hay datos disponibles para mostrar
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td class="font-weight-bold">TOTALES</td>
                                    <td class="font-weight-bold text-success">
                                        <?php
                                        $totalVentas = 0;
                                        foreach ($cuadroVentas as $mes) {
                                            $venta = str_replace(['S/ ', ','], '', $mes['ventas_soles']);
                                            $totalVentas += floatval($venta);
                                        }
                                        echo 'S/ ' . number_format($totalVentas, 2);
                                        ?>
                                    </td>
                                    <td class="font-weight-bold">
                                        <?php
                                        $totalCrecimiento = 0;
                                        foreach ($cuadroVentas as $mes) {
                                            $crecimiento = str_replace(['S/ ', ','], '', $mes['crecimiento_soles']);
                                            if ($crecimiento != '-') {
                                                $totalCrecimiento += floatval($crecimiento);
                                            }
                                        }
                                        if ($totalCrecimiento > 0) {
                                            echo '<span class="text-success">S/ ' . number_format($totalCrecimiento, 2) . ' <i class="fas fa-arrow-up"></i></span>';
                                        } elseif ($totalCrecimiento < 0) {
                                            echo '<span class="text-danger">S/ ' . number_format($totalCrecimiento, 2) . ' <i class="fas fa-arrow-down"></i></span>';
                                        } else {
                                            echo '<span class="text-muted">S/ -</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="font-weight-bold">
                                        <?php
                                        $porcentajePromedio = 0;
                                        $mesesConCrecimiento = 0;
                                        foreach ($cuadroVentas as $mes) {
                                            $porcentaje = str_replace('%', '', $mes['crecimiento_porcentaje']);
                                            if ($porcentaje != 0) {
                                                $porcentajePromedio += floatval($porcentaje);
                                                $mesesConCrecimiento++;
                                            }
                                        }
                                        $promedio = $mesesConCrecimiento > 0 ? $porcentajePromedio / $mesesConCrecimiento : 0;
                                        
                                        if ($promedio > 0) {
                                            echo '<span class="text-success">' . round($promedio, 2) . '% <i class="fas fa-arrow-up"></i></span>';
                                        } elseif ($promedio < 0) {
                                            echo '<span class="text-danger">' . round($promedio, 2) . '% <i class="fas fa-arrow-down"></i></span>';
                                        } else {
                                            echo '<span class="text-muted">0%</span>';
                                        }
                                        ?>
                                    </td>
                                    <!-- <td class="text-center">
                                        <?php
                                        // if ($promedio > 20) {
                                        //     echo '<span class="badge bg-success">CRECIMIENTO FUERTE</span>';
                                        // } elseif ($promedio > 0) {
                                        //     echo '<span class="badge bg-info">CRECIMIENTO MODERADO</span>';
                                        // } elseif ($promedio == 0) {
                                        //     echo '<span class="badge bg-secondary">SIN CAMBIOS</span>';
                                        // } else {
                                        //     echo '<span class="badge bg-warning">DECRECIMIENTO</span>';
                                        // }
                                        ?>
                                    </td> -->
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Última actualización: <?php echo date('d/m/Y H:i:s'); ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <!-- <button class="btn btn-sm btn-outline-primary" onclick="exportarExcel()">
                                <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                            </button> -->
                            <button class="btn btn-sm btn-outline-danger" onclick="imprimirReporte()">
                                <i class="fas fa-print mr-1"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico adicional opcional -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Ventas Mensuales
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="ventasChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Distribución de Crecimiento
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="crecimientoChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cargarReporte() {
    const year = document.getElementById('selectYear').value;
    // Aquí puedes implementar la carga asíncrona del reporte
    window.location.href = `?view=reportecomparativoingresos&year=${year}`;
}

function exportarExcel() {
    // Implementar exportación a Excel
    alert('Funcionalidad de exportación a Excel');
}

function imprimirReporte() {
    window.print();
}

// Gráfico de ventas mensuales
document.addEventListener('DOMContentLoaded', function() {
    const ventasData = <?php echo json_encode(array_column($cuadroVentas, 'ventas_soles')); ?>;
    const crecimientoData = <?php echo json_encode(array_column($cuadroVentas, 'crecimiento_soles')); ?>;
    const meses = <?php echo json_encode(array_column($cuadroVentas, 'mes')); ?>;
    
    // Limpiar formato monetario para el gráfico
    const ventasNumeros = ventasData.map(venta => {
        return parseFloat(venta.replace('S/ ', '').replace(',', ''));
    });

    // Limpiar formato porcentual para el gráfico de crecimiento
    // const crecimientoNumeros = crecimientoData.map(crecimiento => {
    //     return parseFloat(crecimiento.replace('S/', ''));
    // });

    const crecimientoSolesNumeros = crecimientoData.map(crecimiento => {
        if (crecimiento === 'S/ -') {
            return 0;
        }
        return parseFloat(crecimiento.replace('S/ ', '').replace(',', ''));
    });
    
    const ctx = document.getElementById('ventasChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Ventas en Soles',
                data: ventasNumeros,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(201, 203, 207, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(201, 203, 207, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'S/ ' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

     // Gráfico de Distribución de Crecimiento - Doughnut (EN SOLES)
    const ctxCrecimiento = document.getElementById('crecimientoChart').getContext('2d');
    new Chart(ctxCrecimiento, {
        type: 'doughnut',
        data: {
            labels: meses,
            datasets: [{
                label: 'Crecimiento (S/)',
                data: crecimientoSolesNumeros,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',    // ROJO - Enero
                    'rgba(54, 162, 235, 0.8)',    // AZUL - Febrero
                    'rgba(255, 205, 86, 0.8)',    // AMARILLO - Marzo
                    'rgba(75, 192, 192, 0.8)',    // VERDE AGUA - Abril
                    'rgba(153, 102, 255, 0.8)',   // PURPURA - Mayo
                    'rgba(255, 159, 64, 0.8)',    // NARANJA - Junio
                    'rgba(201, 203, 207, 0.8)',   // GRIS - Julio
                    'rgba(0, 204, 102, 0.8)',     // VERDE - Agosto
                    'rgba(255, 0, 127, 0.8)',     // ROSADO - Setiembre
                    'rgba(102, 0, 204, 0.8)',     // ÍNDIGO - Octubre
                    'rgba(0, 153, 204, 0.8)',     // AZUL CLARO - Noviembre
                    'rgba(204, 102, 0, 0.8)'      // MARRÓN - Diciembre
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)',
                    'rgba(0, 204, 102, 1)',
                    'rgba(255, 0, 127, 1)',
                    'rgba(102, 0, 204, 1)',
                    'rgba(0, 153, 204, 1)',
                    'rgba(204, 102, 0, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 10
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const backgroundColor = data.datasets[0].backgroundColor[i];
                                    
                                    // Determinar icono según el valor
                                    let icono = '';
                                    if (value > 0) icono = '▲';
                                    else if (value < 0) icono = '▼';
                                    else icono = '●';
                                    
                                    return {
                                        text: `${icono} ${label}: S/ ${Math.abs(value).toLocaleString('es-PE', {minimumFractionDigits: 2})}`,
                                        fillStyle: backgroundColor,
                                        strokeStyle: data.datasets[0].borderColor[i],
                                        lineWidth: data.datasets[0].borderWidth,
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            let tendencia = '';
                            if (value > 0) tendencia = ' ↗';
                            else if (value < 0) tendencia = ' ↘';
                            
                            return `${label}: S/ ${value.toLocaleString('es-PE', {minimumFractionDigits: 2})}${tendencia}`;
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Crecimiento Mensual en Soles',
                    font: {
                        size: 16
                    }
                }
            },
            cutout: '50%'
        }
    });
});
</script>

<style>
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-bottom: 1rem;
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.badge {
    font-size: 0.75em;
}

@media print {
    .card-tools, .card-footer, .btn {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>