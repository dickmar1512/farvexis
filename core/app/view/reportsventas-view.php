<?php
$fechaInicio = $_GET['sd'] ?? date('Y-m-01');
$fechaFin = $_GET['ed'] ?? date('Y-m-d');
$tipoFiltro = $_GET['tipo'] ?? 'todos';
$estadoFiltro = $_GET['estado_sunat'] ?? '';
$numeroFiltro = trim($_GET['comprobante'] ?? '');

$rows = [];
$empresa = EmpresaData::getDatos();
$ruc = $empresa ? $empresa->Emp_Ruc : '';

function reporteEsc($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function reporteFecha($value): string
{
    if (!$value) return '-';
    $time = strtotime($value);
    return $time ? date('d/m/Y H:i', $time) : reporteEsc($value);
}

function reporteEstadoBadge($estado): string
{
    $estado = $estado ?: 'pendiente';
    $classes = ['aceptado' => 'success', 'rechazado' => 'danger', 'pendiente' => 'warning'];
    $label = ['aceptado' => 'Aceptado', 'rechazado' => 'Rechazado', 'pendiente' => 'Pendiente'];
    $class = $classes[$estado] ?? 'secondary';
    return '<span class="badge badge-' . $class . '">' . reporteEsc($label[$estado] ?? ucfirst($estado)) . '</span>';
}

function reporteAccionesVenta($venta, $ruc, $tipoSunat, $notaRelacionada = null): string
{
    $id = (int)$venta->id;
    $corr = str_pad((string)$venta->comprobante, 8, '0', STR_PAD_LEFT);
    $base = $ruc . '-' . $tipoSunat . '-' . $venta->serie . '-' . $corr;
    $xml = 'storage/FIRMA/' . $base . '.xml';
    $cdr = 'storage/RPTA/R-' . $base . '.zip';
    $html = '<a href="' . url('onesell', ['id' => $id, 'tipodoc' => (int)$venta->tipo_comprobante]) . '" class="btn btn-dark btn-xs mr-1" title="Ver detalle"><i class="fas fa-eye"></i></a>';
    if (file_exists($xml)) {
        //$html .= '<a href="' . reporteEsc($xml) . '" download="' . reporteEsc($base) . '.xml" class="btn btn-outline-info btn-xs mr-1" title="Descargar XML">XML</a>';
    }
    if (file_exists($cdr)) {
        //$html .= '<a href="' . reporteEsc($cdr) . '" download="R-' . reporteEsc($base) . '.zip" class="btn btn-outline-success btn-xs mr-1" title="Descargar CDR">CDR</a>';
    }
    // if ($notaRelacionada) {
    //     $notaDocumento = trim(($notaRelacionada->NOTA_SERIE ?? $notaRelacionada->SERIE ?? '') . '-' . ($notaRelacionada->NOTA_COMPROBANTE ?? $notaRelacionada->COMPROBANTE ?? ''), '-');
    //     $notaView = (int)$venta->tipo_comprobante === 3 ? 'notacreditoboletat' : 'notacreditot';
    //     $html .= '<a href="' . reporteEsc(url($notaView, ['num' => $notaDocumento])) . '" class="btn btn-danger btn-xs mr-1" title="Ver nota de crédito ' . reporteEsc($notaDocumento) . '"><i class="fas fa-file-invoice"></i> N.C. ' . reporteEsc($notaDocumento) . '</a>';
    // }
    if (!$notaRelacionada) {
        if ((int)$venta->tipo_comprobante === 3) {
            $html .= '<a href="' . url('nocboleta', ['id' => $id]) . '" class="btn btn-outline-danger btn-xs px-2 mr-1" title="Generar nota de credito"><i class="fas fa-file-invoice mr-1"></i> N.Cred</a>';
            $html .= '<a href="' . url('nodboleta', ['id' => $id]) . '" class="btn btn-outline-success btn-xs px-2 mr-1" title="Generar nota de debito"><i class="fas fa-file-invoice mr-1"></i> N.Deb</a>';
        } else {
            $html .= '<a href="' . url('nocfactura', ['id' => $id]) . '" class="btn btn-outline-danger btn-xs px-2 mr-1" title="Generar nota de credito"><i class="fas fa-file-invoice mr-1"></i> N.Cred</a>';
            $html .= '<a href="' . url('nodfactura', ['id' => $id]) . '" class="btn btn-outline-success btn-xs px-2 mr-1" title="Generar nota de debito"><i class="fas fa-file-invoice mr-1"></i> N.Deb</a>';
        }
    }
    if (($venta->estado_sunat ?? 'pendiente') === 'pendiente') {
        $html .= '<button type="button" onclick="enviarSunatReporte(' . $id . ')" class="btn btn-outline-warning btn-xs" title="Enviar a SUNAT"><i class="fas fa-paper-plane"></i></button>';
    }
    return $html;
}

if ($tipoFiltro !== 'nc_boleta' && $tipoFiltro !== 'nc_factura') {
    $ventas = SellData::getSells($fechaInicio, $fechaFin, 0);
    foreach ($ventas as $venta) {
        $html_nc = '';
        $tipo = (int)$venta->tipo_comprobante;
        if (!in_array($tipo, [1, 3], true)) continue;
        if ($tipoFiltro === 'boleta' && $tipo !== 3) continue;
        if ($tipoFiltro === 'factura' && $tipo !== 1) continue;
        if ($estadoFiltro !== '' && ($venta->estado_sunat ?? 'pendiente') !== $estadoFiltro) continue;
        if ($numeroFiltro !== '' && stripos($venta->serie . '-' . $venta->comprobante, $numeroFiltro) === false) continue;
        $person = $venta->person_id ? PersonData::getById($venta->person_id) : null;
        $notaRelacionada = NotData::getByIdComprobado($venta->serie . '-' . $venta->comprobante);
        $notaDocumento = $notaRelacionada
            ? trim(($notaRelacionada->NOTA_SERIE ?? $notaRelacionada->SERIE ?? '') . '-' . ($notaRelacionada->NOTA_COMPROBANTE ?? $notaRelacionada->COMPROBANTE ?? ''), '-')
            : '-';
        if ($notaRelacionada) {
            $notaDocumento = trim(($notaRelacionada->NOTA_SERIE ?? $notaRelacionada->SERIE ?? '') . '-' . ($notaRelacionada->NOTA_COMPROBANTE ?? $notaRelacionada->COMPROBANTE ?? ''), '-');
            $notaView = (int)$venta->tipo_comprobante === 3 ? 'notacreditoboletat' : 'notacreditot';
            $html_nc = '<a href="' . reporteEsc(url($notaView, ['num' => $notaDocumento])) . '" class="btn btn-danger btn-xs mr-1" title="Ver nota de crédito ' . reporteEsc($notaDocumento) . '"><i class="fas fa-file-invoice"></i> N.C. ' . reporteEsc($notaDocumento) . '</a>';
        }

        $rows[] = [
            'fecha' => $venta->created_at,
            'tipo' => $tipo === 3 ? 'Boleta' : 'Factura',
            'documento' => $venta->serie . '-' . $venta->comprobante,
            'cliente' => $person ? trim($person->name . ' ' . $person->lastname) : 'PUBLICO GENERAL',
            'nota' => $html_nc,
            'importe' => (float)$venta->total,
            'estado' => $venta->estado_sunat ?: 'pendiente',
            'acciones' => reporteAccionesVenta($venta, $ruc, $tipo === 3 ? '03' : '01', $notaRelacionada),
            'orden' => strtotime($venta->created_at) ?: 0
        ];
    }
}

if ($tipoFiltro !== 'boleta' && $tipoFiltro !== 'factura') {
    $notaFuentes = [];
    if ($tipoFiltro === 'todos' || $tipoFiltro === 'nc_boleta') {
        $notaFuentes[] = ['tipo' => 'nc_boleta', 'items' => NotData::get_notas_credito_boleta_x_fecha($fechaInicio, $fechaFin)];
    }
    if ($tipoFiltro === 'todos' || $tipoFiltro === 'nc_factura') {
        $notaFuentes[] = ['tipo' => 'nc_factura', 'items' => NotData::get_notas_credito_factura_x_fecha($fechaInicio, $fechaFin)];
    }
    foreach ($notaFuentes as $fuente) {
        foreach ($fuente['items'] as $nota) {
            $html_comprobante = '';
            $serie = $nota->NOTA_SERIE ?? $nota->SERIE ?? '';
            $comprobante = $nota->NOTA_COMPROBANTE ?? $nota->COMPROBANTE ?? '';
            $documento = trim($serie . '-' . $comprobante, '-');
            if ($numeroFiltro !== '' && stripos($documento, $numeroFiltro) === false) continue;
            $detalleUrl = $fuente['tipo'] === 'nc_boleta' ? './?view=notacreditoboletat&num=' . urlencode($documento) : './?view=notacreditot&num=' . urlencode($documento);
            $serieDocMOdifica = $nota->serieDocModifica;

            if ($serieDocMOdifica) {
                $notaDocumento = trim(($notaRelacionada->NOTA_SERIE ?? $notaRelacionada->SERIE ?? '') . '-' . ($notaRelacionada->NOTA_COMPROBANTE ?? $notaRelacionada->COMPROBANTE ?? ''), '-');
                $comprobanteView = (int)$venta->tipo_comprobante === 3 ? 'notacreditoboletat' : 'notacreditot';
                $html_comprobante = '<a href="' . reporteEsc(url($notaView, ['num' => $notaDocumento])) . '" class="btn btn-info btn-xs mr-1" title="Ver comprobante que modifica ' . reporteEsc($serieDocMOdifica) . '"><i class="fas fa-file-invoice"></i> N.C. ' . reporteEsc($serieDocMOdifica) . '</a>';
            }

            $rows[] = [
                'fecha' => $nota->fecEmision,
                'tipo' => $fuente['tipo'] === 'nc_boleta' ? 'NC Boleta' : 'NC Factura',
                'documento' => $documento,
                'cliente' => $nota->rznSocialUsuario ?? '-',
                'nota' => $html_comprobante,
                'importe' => (float)$nota->sumPrecioVenta,
                'estado' => $nota->estado_sunat ?? 'pendiente',
                'acciones' => '<a href="' . reporteEsc($detalleUrl) . '" class="btn btn-dark btn-xs" title="Ver nota"><i class="fas fa-eye"></i></a>',
                'orden' => strtotime($nota->fecEmision) ?: 0
            ];
        }
    }
}

usort($rows, function ($a, $b) { return $b['orden'] <=> $a['orden']; });
$totalReporte = array_sum(array_column($rows, 'importe'));
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-7"><h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2"></i>Reportes de comprobantes</h1></div>
            <div class="col-sm-5"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?view=home">Inicio</a></li><li class="breadcrumb-item active">Reportes</li></ol></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filtros de reporte</h3></div>
            <div class="card-body">
                <form method="get" class="row align-items-end">
                    <input type="hidden" name="view" value="reportsventas">
                    <div class="col-md-2 form-group"><label>Tipo</label><select name="tipo" class="form-control form-control-sm"><option value="todos" <?= $tipoFiltro === 'todos' ? 'selected' : '' ?>>Todos</option><option value="boleta" <?= $tipoFiltro === 'boleta' ? 'selected' : '' ?>>Boletas</option><option value="factura" <?= $tipoFiltro === 'factura' ? 'selected' : '' ?>>Facturas</option><option value="nc_boleta" <?= $tipoFiltro === 'nc_boleta' ? 'selected' : '' ?>>NC Boleta</option><option value="nc_factura" <?= $tipoFiltro === 'nc_factura' ? 'selected' : '' ?>>NC Factura</option></select></div>
                    <div class="col-md-2 form-group"><label>Estado SUNAT</label><select name="estado_sunat" class="form-control form-control-sm"><option value="">Todos</option><option value="pendiente" <?= $estadoFiltro === 'pendiente' ? 'selected' : '' ?>>Pendiente</option><option value="aceptado" <?= $estadoFiltro === 'aceptado' ? 'selected' : '' ?>>Aceptado</option><option value="rechazado" <?= $estadoFiltro === 'rechazado' ? 'selected' : '' ?>>Rechazado</option></select></div>
                    <div class="col-md-2 form-group"><label>Comprobante</label><input type="text" name="comprobante" value="<?= reporteEsc($numeroFiltro) ?>" class="form-control form-control-sm" placeholder="Serie-número"></div>
                    <div class="col-md-2 form-group"><label>Fecha inicial</label><input type="date" name="sd" value="<?= reporteEsc($fechaInicio) ?>" class="form-control form-control-sm"></div>
                    <div class="col-md-2 form-group"><label>Fecha final</label><input type="date" name="ed" value="<?= reporteEsc($fechaFin) ?>" class="form-control form-control-sm"></div>
                    <div class="col-md-2 form-group"><button class="btn btn-primary btn-sm btn-block"><i class="fas fa-search mr-1"></i>Consultar</button></div>
                </form>
            </div>
        </div>

        <div class="card card-success card-outline shadow-sm">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i>Resultados (<?= count($rows) ?>)</h3><div class="card-tools"><strong>Total: S/ <?= number_format($totalReporte, 2) ?></strong></div></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-hover table-striped mb-0 datatable" style="width:100%">
                    <thead class="thead-dark"><tr><th>Fecha</th><th>Tipo</th><th>Comprobante</th><th>Doc. Relacionado</th><th>Cliente</th><th class="text-right">Importe</th><th class="text-center">Estado</th><th class="text-center">Acciones</th></tr></thead>
                    <tbody><?php foreach ($rows as $row): ?><tr><td><?= reporteFecha($row['fecha']) ?></td><td><span class="badge badge-light border"><?= reporteEsc($row['tipo']) ?></span></td><td class="font-weight-bold"><?= reporteEsc($row['documento']) ?></td><td class="font-weight-bold text-danger"><?= $row['nota'] ?? '-' ?></td><td><?= reporteEsc($row['cliente']) ?></td><td class="text-right">S/ <?= number_format($row['importe'], 2) ?></td><td class="text-center"><?= $row['estado'] === 'legacy' ? '<span class="badge badge-secondary">Legacy</span>' : reporteEstadoBadge($row['estado']) ?></td><td class="text-center text-nowrap"><?= $row['acciones'] ?></td></tr><?php endforeach; ?></tbody>
                </table>
                <?php if (!$rows): ?><div class="alert alert-info m-3">No se encontraron comprobantes con los filtros seleccionados.</div><?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function enviarSunatReporte(sellId) {
    Swal.fire({title: 'Enviar a SUNAT', text: 'Se enviara el comprobante seleccionado.', icon: 'question', showCancelButton: true, confirmButtonText: 'Enviar', cancelButtonText: 'Cancelar'}).then(function (result) {
        if (!result.isConfirmed) return;
        Swal.fire({title: 'Enviando...', allowOutsideClick: false, showConfirmButton: false, didOpen: function () { Swal.showLoading(); }});
        $.post('./?action=send_sunat_ajax', {id: sellId}, function (response) {
            Swal.fire(response.exito ? 'Enviado' : 'Atencion', response.descripcion || 'Proceso terminado.', response.exito ? 'success' : 'warning').then(function () { window.location.reload(); });
        }, 'json').fail(function () { Swal.fire('Error', 'No se pudo comunicar con SUNAT.', 'error'); });
    });
}
</script>

