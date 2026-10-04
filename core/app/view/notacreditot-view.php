<?php
$numParam = $_GET["num"] ?? '';

// Try Factura credit note first, then Boleta credit note
$product = Factura2Data::getByNumDoc($numParam);
$tipoDocOriginal = '01';
if (!$product) {
    $product = BoletaData::getByNumDoc($numParam);
    $tipoDocOriginal = '03';
}

$comp_cab = null;
$detalles = [];
$comp_tri = null;
$comp_ley = null;
$sell = null;
$cajero = null;

if ($product) {
    $comp_cab = NotData::getById($product->id, 7);
    $detalles = DetData::getById($product->id, 7) ?: [];
    $comp_tri = TriData::getById($product->id, 7);
    $comp_ley = LeyData::getById($product->id, 7);

    if ($comp_cab && !empty($comp_cab->serieDocModifica)) {
        $sell = SellData::getByNumDoc($comp_cab->serieDocModifica) ?: SellData::getByNroDoc($comp_cab->serieDocModifica);
    }
}

if (!$comp_cab && $numParam) {
    $comp_cab = NotData::getByIdComprobado($numParam);
    if ($comp_cab) {
        $detalles = DetData::getById($comp_cab->id, 7) ?: [];
        $comp_tri = TriData::getById($comp_cab->id, 7);
        $comp_ley = LeyData::getById($comp_cab->id, 7);
        if (!empty($comp_cab->serieDocModifica)) {
            $sell = SellData::getByNumDoc($comp_cab->serieDocModifica) ?: SellData::getByNroDoc($comp_cab->serieDocModifica);
        }
    }
}

if ($sell && isset($sell->user_id)) {
    $cajero = UserData::getById($sell->user_id);
}
$empresa = EmpresaData::getDatos();

$motivosNota = [
    '01' => "Anulación de la operación",
    '1'  => "Anulación de la operación",
    '02' => "Anulación por error en el RUC",
    '2'  => "Anulación por error en el RUC",
    '03' => "Corrección por error en la descripción",
    '3'  => "Corrección por error en la descripción",
    '04' => "Descuento global",
    '4'  => "Descuento global",
    '05' => "Descuento por ítem",
    '5'  => "Descuento por ítem",
    '06' => "Devolución total",
    '6'  => "Devolución total",
    '07' => "Devolución por ítem",
    '7'  => "Devolución por ítem",
    '08' => "Bonificación",
    '8'  => "Bonificación",
    '09' => "Disminución en el valor",
    '9'  => "Disminución en el valor",
    '10' => "Otros conceptos",
    '13' => "Ajustes de montos / fechas de pago"
];
$codigoMotivo = $comp_cab->codTipoNota ?? '';
$textoMotivo = $motivosNota[$codigoMotivo] ?? ($comp_cab->descMotivo ?? "Anulación de la operación");

$op_gravada   = 0.0;
$op_exonerada = 0.0;
$op_inafecta  = 0.0;
$igv_total    = 0.0;

if ($comp_cab) {
    $valVenta = floatval($comp_cab->sumTotValVenta ?? 0);
    $tributos = floatval($comp_cab->sumTotTributos ?? 0);

    if ($comp_tri) {
        $ide = $comp_tri->ideTributo ?? '';
        $nom = strtoupper($comp_tri->nomTributo ?? '');
        if ($ide === '1000' || $nom === 'IGV') {
            $op_gravada = floatval($comp_tri->mtoBaseImponible ?? $valVenta);
            $igv_total  = floatval($comp_tri->mtoTributo ?? $tributos);
        } elseif ($ide === '9997' || $nom === 'EXO') {
            $op_exonerada = $valVenta;
        } elseif ($ide === '9998' || $nom === 'INA') {
            $op_inafecta = $valVenta;
        } else {
            $op_gravada = $valVenta;
            $igv_total  = $tributos;
        }
    } else {
        if ($tributos > 0) {
            $op_gravada = $valVenta;
            $igv_total  = $tributos;
        } else {
            $op_exonerada = $valVenta;
        }
    }

    $montoTotal = floatval($comp_cab->sumPrecioVenta ?? ($op_gravada + $igv_total + $op_exonerada + $op_inafecta));
} else {
    $sumDet = 0.0;
    foreach ($detalles as $d) {
        $sumDet += floatval($d->mtoValorVentaItem ?? 0);
    }
    $op_exonerada = $sumDet;
    $montoTotal   = $sumDet;
}

$numLetras = "";
if (class_exists("NumeroLetras")) {
    $numLetras = NumeroLetras::convertir(number_format($montoTotal, 2, '.', ''));
}

// Tipo de documento modificado (Factura o Boleta)
$docModificaTipoLabel = "DOCUMENTO";
if (!empty($comp_cab->serieDocModifica)) {
    if (strpos($comp_cab->serieDocModifica, 'F') === 0 || ($comp_cab->tipDocModifica ?? '') == '01') {
        $docModificaTipoLabel = "FACTURA";
    } elseif (strpos($comp_cab->serieDocModifica, 'B') === 0 || ($comp_cab->tipDocModifica ?? '') == '03') {
        $docModificaTipoLabel = "BOLETA";
    }
}

// Archivos SUNAT
$emp_ruc = $empresa ? $empresa->Emp_Ruc : '20000000001';
$serieNota = $product->SERIE ?? ($comp_cab->NOTA_SERIE ?? '');
$compNota  = $product->COMPROBANTE ?? ($comp_cab->NOTA_COMPROBANTE ?? '');
$correlativoPadded = str_pad($compNota, 8, '0', STR_PAD_LEFT);
$nombreArchSunat = "{$emp_ruc}-07-{$serieNota}-{$correlativoPadded}";
$xmlLocalPath = "storage/FIRMA/{$nombreArchSunat}.xml";
$cdrLocalPath = "storage/RPTA/R-{$nombreArchSunat}.zip";
$hasXml = file_exists($xmlLocalPath);
$hasCdr = file_exists($cdrLocalPath);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Crédito <?php echo htmlspecialchars($serieNota . "-" . $compNota); ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.onesell.css">
    <script src="<?= BASE_URL ?>/assets/plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <style>
        .badge-nota { display: inline-block; padding: 0.35em 0.8em; font-size: 85%; font-weight: 700; border-radius: 999px; background-color: #dc3545; color: #fff; margin-top: 8px; }
        .modifica-label { font-size: 0.8rem; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: block; }
        .amount-in-words { margin-top: 10px; font-size: 0.85rem; font-weight: 600; color: #444; border-top: 1px dashed #ccc; padding-top: 6px; }
        @media print {
            .header-section, .controls-section, .btn-back, .btn { display: none !important; }
            body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
            .main-container { max-width: 100% !important; padding: 0 !important; margin: 0 !important; box-shadow: none !important; }
            .receipt-container { box-shadow: none !important; border: none !important; padding: 10px !important; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header Section Compacto -->
        <div class="header-section fade-in">
            <div class="header-title">
                <div class="title-group">
                    <i class="fas fa-file-invoice"></i>
                    <h1>Nota de Crédito</h1>
                </div>
                <div class="breadcrumb">
                    <i class="fas fa-home"></i> Reportes > Nota de Crédito
                </div>
            </div>

            <div class="controls-section">
                <button class="btn btn-back" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>

                <button class="btn btn-primary" id="imprimir">
                    <i class="fas fa-print"></i> Imprimir
                </button>

                <?php if ($hasXml): ?>
                    <a href="<?php echo $xmlLocalPath; ?>" download="<?php echo $nombreArchSunat; ?>.xml" class="btn btn-info" style="color:#fff; text-decoration:none;">
                        <i class="fas fa-file-code"></i> XML
                    </a>
                <?php endif; ?>

                <?php if ($hasCdr): ?>
                    <a href="<?php echo $cdrLocalPath; ?>" download="R-<?php echo $nombreArchSunat; ?>.zip" class="btn btn-success" style="color:#fff; text-decoration:none;">
                        <i class="fas fa-file-archive"></i> CDR
                    </a>
                <?php endif; ?>

                <?php if (empty($comp_cab->estado_sunat) || $comp_cab->estado_sunat !== 'aceptado'): ?>
                    <button class="btn btn-warning" onclick="enviarSunatNota('<?php echo htmlspecialchars($serieNota . '-' . $compNota); ?>')">
                        <i class="fas fa-paper-plane"></i> Enviar SUNAT
                    </button>
                    <button class="btn btn-danger" onclick="regenerarDetalleXML('<?php echo htmlspecialchars($serieNota . '-' . $compNota); ?>')">
                        <i class="fas fa-sync-alt"></i> Regenerar XML
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Receipt Container Compacto (idéntico a onesell-view) -->
        <div class="receipt-container fade-in" id="receiptArea">
            <!-- Company Header -->
            <div class="company-info">
                <div class="company-logo">
                    <i class="fas fa-building" style="font-size: 1.5rem;"></i>
                </div>
                <div class="company-details">
                    <h3><?php echo htmlspecialchars($empresa->Emp_RazonSocial ?? ''); ?></h3>
                    <p><?php echo htmlspecialchars($empresa->Emp_Direccion ?? ''); ?></p>
                    <p>📞 <?php echo htmlspecialchars($empresa->Emp_Telefono ?? ''); ?></p>
                    <p>✉ <?php echo htmlspecialchars($empresa->Emp_Celular ?? ''); ?></p>
                </div>
                <div class="document-info text-center">
                    <div style="font-size: 0.85rem;"><strong>RUC: <?php echo htmlspecialchars($empresa->Emp_Ruc ?? ''); ?></strong></div>
                    <div style="margin: 6px 0;">
                        <label style="font-size: 0.9rem; font-weight: bold; color: #dc3545;">NOTA DE CRÉDITO ELECTRÓNICA</label>
                    </div>
                    <div class="document-number"><?php echo htmlspecialchars($serieNota . "-" . $compNota); ?></div>
                    <div class="badge-nota">
                        <?php echo htmlspecialchars($textoMotivo); ?>
                    </div>
                </div>
            </div>

            <!-- Modification & Customer Info -->
            <div class="customer-info border-bottom pb-3 mb-3">
                <span class="modifica-label">Documento que modifica</span>
                <div class="info-row">
                    <span class="info-label"><?php echo $docModificaTipoLabel; ?></span>
                    <span class="info-value"><?php echo ": " . htmlspecialchars($comp_cab ? $comp_cab->serieDocModifica : ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">DOC. CLIENTE</span>
                    <span class="info-value"><?php echo ": " . htmlspecialchars($comp_cab ? $comp_cab->numDocUsuario : ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">CLIENTE</span>
                    <span class="info-value"><?php echo ": " . htmlspecialchars($comp_cab ? $comp_cab->rznSocialUsuario : ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">MOTIVO SUNAT</span>
                    <span class="info-value text-bold"><?php echo ": [" . htmlspecialchars($codigoMotivo) . "] " . htmlspecialchars($textoMotivo); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">FECHA EMISIÓN</span>
                    <span class="info-value"><?php echo ": " . ($comp_cab && isset($comp_cab->fecEmision) ? date("d/m/Y H:i", strtotime($comp_cab->fecEmision)) : ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">ESTADO SUNAT</span>
                    <span class="info-value">: 
                        <?php 
                            $est = $comp_cab->estado_sunat ?? 'pendiente';
                            if ($est === 'aceptado') echo '<span style="color: #28a745; font-weight: bold;">ACEPTADO SUNAT</span>';
                            elseif ($est === 'rechazado') echo '<span style="color: #dc3545; font-weight: bold;">RECHAZADO SUNAT</span>';
                            else echo '<span style="color: #ffc107; font-weight: bold;">PENDIENTE</span>';
                        ?>
                    </span>
                </div>
                <?php if (!empty($comp_cab->codigo_hash)): ?>
                <div class="info-row">
                    <span class="info-label">HASH</span>
                    <span class="info-value" style="font-size: 0.75rem;">: <?= htmlspecialchars($comp_cab->codigo_hash) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Products Table -->
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">CANT</th>
                        <th>DESCRIPCIÓN</th>
                        <th style="width: 80px;" class="text-right">P.U.</th>
                        <th style="width: 100px;" class="text-right">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($detalles)): ?>
                        <?php foreach ($detalles as $det): ?>
                            <tr>
                                <td class="quantity-cell text-center"><?php echo floatval($det->ctdUnidadItem); ?></td>
                                <td class="description-cell">
                                    <div class="product-name"><?php echo htmlspecialchars($det->desItem); ?></div>
                                </td>
                                <td class="price-cell text-right"><?php echo number_format($det->mtoValorUnitario, 2); ?></td>
                                <td class="price-cell text-right"><?php echo number_format($det->mtoValorVentaItem, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay detalles registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Totals Section -->
            <div class="totals-section">
                <div class="user-info">
                    <i class="fas fa-user-tie" style="color: #dc3545;"></i>
                    <div>
                        <div><strong>Cajero:</strong> <?php echo htmlspecialchars($cajero ? $cajero->username : ''); ?></div>
                        <small style="color: #666;">Terminal: CAJA-01</small>
                    </div>
                </div>

                <div class="totals-table">
                    <table>
                        <tr><td>Op. Gratuita</td><td class="text-right">S/ 0.00</td></tr>
                        <tr><td>Op. Exonerada</td><td class="text-right">S/ <?php echo number_format($op_exonerada, 2); ?></td></tr>
                        <tr><td>Op. Inafecta</td><td class="text-right">S/ <?php echo number_format($op_inafecta, 2); ?></td></tr>
                        <tr><td>Op. Gravada</td><td class="text-right">S/ <?php echo number_format($op_gravada, 2); ?></td></tr>
                        <tr><td>IGV (18%)</td><td class="text-right">S/ <?php echo number_format($igv_total, 2); ?></td></tr>
                        <tr class="total-row" style="border-top: 2px solid #333;">
                            <td>TOTAL ANULADO</td>
                            <td class="text-right">S/ <?php echo number_format($montoTotal, 2); ?></td>
                        </tr>
                    </table>
                    <?php if (!empty($numLetras)): ?>
                        <div class="amount-in-words">
                            SON: <?php echo htmlspecialchars($numLetras); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="text-center mt-4 pt-3 border-top small text-muted">
                <p>Representación impresa de la Nota de Crédito Electrónica.<br>Consulte su validez en el portal de la SUNAT.</p>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/plugins/jquery/jquery.min.js"></script>
    <script>
        $('#imprimir').click(function() {
            window.print();
        });

        function enviarSunatNota(numDoc) {
            Swal.fire({
                title: '¿Enviar a SUNAT?',
                text: 'Se enviará la nota de crédito directamente a SUNAT vía web service SOAP.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Enviando...',
                        text: 'Conectando con el servicio de SUNAT',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    $.post('./?action=send_sunat_ajax', { num: numDoc }, function(res) {
                        if (res.exito) {
                            Swal.fire('¡Éxito!', res.descripcion || 'Aceptado por SUNAT', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Atención', res.descripcion || 'No se pudo enviar', 'warning').then(() => {
                                location.reload();
                            });
                        }
                    }, 'json').fail(function() {
                        Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
                    });
                }
            });
        }

        function regenerarDetalleXML(numDoc) {
            Swal.fire({
                title: '¿Regenerar XML?',
                text: 'Se extraerán los productos del documento original y se regenerará la estructura de esta nota de crédito.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, regenerar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Reconstruyendo detalles desde el documento origen',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    $.post('./?action=regenerate_nota_xml', { num: numDoc }, function(res) {
                        if (res.exito) {
                            Swal.fire('¡Éxito!', res.descripcion, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Atención', res.descripcion || 'No se pudo regenerar', 'warning');
                        }
                    }, 'json').fail(function() {
                        Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>

