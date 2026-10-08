<?php
if ($_GET["tipodoc"] == 3):
	$venta = BoletaData::getByExtra($_GET["id"]);
	$desComprobante = "BOLETA ELECTRÓNICA";
	$docLabel = "DNI";
	$nomLabel = "SEÑOR(ES)";
else:
	$venta = Factura2Data::getByExtra($_GET["id"]);
	$desComprobante = "FACTURA ELECTRÓNICA";
	$docLabel = "RUC";
	$nomLabel = "RAZON SOCIAL";
endif;

$comp_cab = CabData::getById($venta->id, $_GET["tipodoc"]);
$comp_aca = AcaData::getById($venta->id, $_GET["tipodoc"]);
$detalles = DetData::getById($venta->id, $_GET["tipodoc"]);
$comp_tri = TriData::getById($venta->id, $_GET["tipodoc"]);
$comp_ley = LeyData::getById($venta->id, $_GET["tipodoc"]);
$sellTemp = SellData::getById($venta->EXTRA1);

$paciente_nombre = "";
$paciente_dni = "";
$medico_nombre = "";
$medico_cmp = "";
if (!empty($sellTemp->paciente_id)) {
    $con = Database::getCon();
    $res = $con->query("SELECT nombres, numero_documento FROM persona WHERE id = " . intval($sellTemp->paciente_id));
    if ($r = $res->fetch_assoc()) {
        $paciente_nombre = $r['nombres'];
        $paciente_dni = $r['numero_documento'];
    }
}
if (!empty($sellTemp->medico_id)) {
    $con = Database::getCon();
    $res = $con->query("SELECT p.nombres, m.cmp FROM medico m JOIN persona p ON m.persona_id = p.id WHERE m.id = " . intval($sellTemp->medico_id));
    if ($r = $res->fetch_assoc()) {
        $medico_nombre = $r['nombres'];
        $medico_cmp = $r['cmp'];
    }
}

$sell = (object)[
	'id'=> $sellTemp->id,
	'person_id'=> $sellTemp->person_id,
	'user_id'=> $sellTemp->user_id,
	'total'=> $sellTemp->total,
	'cash'=> $sellTemp->cash,
	'discount'=> $sellTemp->discount,
	'created_at'=> $sellTemp->created_at,
	'tipo_comprobante'=> $sellTemp->tipo_comprobante,
	'serie'=> $sellTemp->serie,
	'comprobante'=> $sellTemp->comprobante,
	'estado'=> $sellTemp->estado,
	'tipo_pago'=> $sellTemp->tipo_pago,
	'box_id'=> $sellTemp->box_id,
	'operation_type_id'=> $sellTemp->operation_type_id,
	'observacion'=> $sellTemp->observacion,
	'forma_pago'=> $sellTemp->forma_pago,
	'fec_vencimiento'=> $sellTemp->fec_vencimiento,
	'estado_sunat'=> $sellTemp->estado_sunat,
	'codigo_hash'=> $sellTemp->codigo_hash,
	'cdr_codigo'=> $sellTemp->cdr_codigo,
	'cdr_descripcion'=> $sellTemp->cdr_descripcion
];

$pagoParcial = SellData::getImportePagoParcial($venta->EXTRA1);

$datoPagoParcial = [
    'id' => $pagoParcial[0]->id ?? 0,
    'importepp' => $pagoParcial[0]->importepp ?? 0
];

$operations = OperationData::getAllProductsBySellId($_GET["id"]);

$cajero = null;
$cajero = UserData::getById($sell->user_id)->username;
$empresa = EmpresaData::getDatos();

$fechaObj = new DateTime($comp_cab->fecEmision);
$fechaFormateada = $fechaObj->format('d/m/Y');

$selected = isset($sell->tipo_pago) ? $sell->tipo_pago : null;

// Archivos SUNAT en storage
$emp_ruc = $empresa ? $empresa->Emp_Ruc : '20000000001';
$tipoDocSunat = ($_GET["tipodoc"] == 1) ? '01' : '03';
$correlativoPadded = str_pad($venta->COMPROBANTE, 8, '0', STR_PAD_LEFT);
$nombreArchSunat = "{$emp_ruc}-{$tipoDocSunat}-{$venta->SERIE}-{$correlativoPadded}";

$xmlLocalPath = "storage/FIRMA/{$nombreArchSunat}.xml";
$cdrLocalPath = "storage/RPTA/R-{$nombreArchSunat}.zip";
$hasXml = file_exists($xmlLocalPath);
$hasCdr = file_exists($cdrLocalPath);

// Desglose dinámico de impuestos
$subtotal_gravado   = 0.00;
$igv_total          = 0.00;
$subtotal_exonerado = 0.00;
$subtotal_inafecto  = 0.00;
$subtotal_gratuito  = 0.00;

foreach ($operations as $ope) {
    $q = (float)$ope->q;
    $pu = (float)$ope->prec_alt;
    $tipo = $ope->igv_tipo ?? '20';
    if ($tipo === '10') {
        $base = round($q * ($pu / 1.18), 2);
        $igvLine = round($q * $pu, 2) - $base;
        $subtotal_gravado += $base;
        $igv_total += $igvLine;
    } elseif ($tipo === '20') {
        $subtotal_exonerado += round($q * $pu, 2);
    } elseif ($tipo === '30') {
        $subtotal_inafecto += round($q * $pu, 2);
    } else {
        $subtotal_gratuito += round($q * $pu, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.onesell.css">
    <script src="<?= BASE_URL ?>/assets/plugins/sweetalert2/sweetalert2.all.min.js"></script>
</head>
<body>
    <div class="main-container">
        <!-- Header Section Compacto -->
        <div class="header-section fade-in">
            <div class="header-title">
                <div class="title-group">
                    <i class="fas fa-receipt"></i>
                    <h1>Comprobante</h1>
                </div>
                <div class="breadcrumb">
                    <i class="fas fa-home"></i> Ventas > Comprobante
                </div>
            </div>

            <div class="controls-section">
                <button class="btn btn-back" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>

                <button class="btn btn-primary" id="imprimir80mm">
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

                <?php if (empty($sellTemp->estado_sunat) || $sellTemp->estado_sunat !== 'aceptado'): ?>
                    <button class="btn btn-warning" onclick="enviarSunat(<?php echo $venta->EXTRA1; ?>)">
                        <i class="fas fa-paper-plane"></i> Enviar SUNAT
                    </button>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="selTipoPago">Tipo de Pago</label>
                    <select id="selTipoPago" class="form-control">
                        <option value="1" <?= $selected == 1 ? 'selected' : '' ?>>💵 Efectivo</option>
                        <option value="2" <?= $selected == 2 ? 'selected' : '' ?>>📱 Plin</option>
                        <option value="3" <?= $selected == 3 ? 'selected' : '' ?>>📱 Yape</option>
                        <option value="4" <?= $selected == 4 ? 'selected' : '' ?>>💳 T. Débito</option>
                        <option value="5" <?= $selected == 5 ? 'selected' : '' ?>>💳 T. Crédito</option>
                    </select>
                    <input type="hidden" id="sellid" name="sellid" value="<?=$venta->EXTRA1?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="importeParcial">Pago Parcial Efectivo</label>
                    <input type="text" id="importeParcial" name="importeParcial" class="form-control" style="width: 100px;" placeholder="0.00" value="<?= number_format($datoPagoParcial['importepp'], 2, '.', ',') ?>">
                </div>

                <button class="btn btn-primary" id="actualizarTipoPago">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
        </div>

        <!-- Receipt Container Compacto -->
        <div class="receipt-container fade-in" id="receiptArea">
            <!-- Company Header Compacto -->
            <div class="company-info">
                <div class="company-logo">
                    <i class="fas fa-building" style="font-size: 1.5rem;"></i>
                </div>
                <div class="company-details">
                    <h3><?php echo $empresa->Emp_RazonSocial ?></h3>
                    <p><?php echo $empresa->Emp_Direccion ?></p>
                    <p>📞 <?php echo $empresa->Emp_Telefono ?></p>
                    <p>✉ <?php echo $empresa->Emp_Celular ?></p>
                </div>
                <div class="document-info">
                    <div style="font-size: 0.85rem;"><strong>RUC: <?php echo $empresa->Emp_Ruc ?></strong></div>
                    <div style="margin: 6px 0;">
                        <label for="numeroComprobante" style="font-size: 0.9rem;"><?php echo $desComprobante;?></label>
                    </div>
                    <div class="document-number" id="numeroComprobante" name="numeroComprobante"><?php echo $venta->SERIE . "-" . $venta->COMPROBANTE; ?></div>
                </div>
            </div>

            <!-- Customer Information Compacto -->
            <div class="customer-info">
                <div class="info-row">
                    <span class="info-label"><?= $docLabel ?></span>
                    <span class="info-value"><?php echo ": " . $comp_cab->numDocUsuario; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= $nomLabel ?></span>
                    <span class="info-value"><?php echo ": " . $comp_cab->rznSocialUsuario; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">DIRECCIÓN</span>
                    <span class="info-value"><?php echo ": " . $comp_aca->desDireccionCliente; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">FECHA</span>
                    <span class="info-value"><?php echo ": " . $fechaFormateada . "  " . $comp_cab->horEmision; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">FORMA PAGO</span>
                    <span class="info-value">: <?= ($sellTemp->forma_pago == 2) ? '<b style="color:#d9534f;">CRÉDITO (VENCE: ' . ($sellTemp->fec_vencimiento ?: '-') . ')</b>' : 'CONTADO' ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">ESTADO SUNAT</span>
                    <span class="info-value">: 
                        <?php 
                            $est = $sellTemp->estado_sunat ?? 'pendiente';
                            if ($est === 'aceptado') echo '<span style="color: #28a745; font-weight: bold;">ACEPTADO SUNAT</span>';
                            elseif ($est === 'rechazado') echo '<span style="color: #dc3545; font-weight: bold;">RECHAZADO SUNAT</span>';
                            else echo '<span style="color: #ffc107; font-weight: bold;">PENDIENTE</span>';
                        ?>
                    </span>
                </div>
                <?php if (!empty($sellTemp->codigo_hash)): ?>
                <div class="info-row">
                    <span class="info-label">HASH</span>
                    <span class="info-value" style="font-size: 0.75rem;">: <?= htmlspecialchars($sellTemp->codigo_hash) ?></span>
                </div>
                <?php endif; ?>
                <?php if(!empty($paciente_nombre)): ?>
                <div class="info-row mt-2 pt-2 border-top">
                    <span class="info-label text-danger">RECETA MÉDICA</span>
                    <span class="info-value"></span>
                </div>
                <div class="info-row">
                    <span class="info-label">PACIENTE</span>
                    <span class="info-value">: <?= htmlspecialchars($paciente_nombre) . " (DNI: " . htmlspecialchars($paciente_dni) . ")" ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">MÉDICO</span>
                    <span class="info-value">: <?= htmlspecialchars($medico_nombre) . " (CMP: " . htmlspecialchars($medico_cmp) . ")" ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Products Table Compacto -->
            <table class="products-table">
                <thead>
                    <tr>
                        <th>CANT</th>
                        <th>DESCRIPCIÓN</th>
                        <th>AFECT.</th>
                        <th>P.U.</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
						$total = 0;
						foreach ($operations as $ope) {
                            $product = ProductData::getById($ope->product_id);
                            $subtotal = $ope->q * $ope->prec_alt;
                            $tipoBadge = ($ope->igv_tipo === '10') ? 'GRA' : (($ope->igv_tipo === '20') ? 'EXO' : (($ope->igv_tipo === '30') ? 'INA' : 'GRA'));
					?>
                    <tr>
                        <td class="quantity-cell"><?php echo $ope->q; ?></td>
                        <td class="description-cell">
                            <div class="product-name"><?php echo $product->name; ?></div>
                            <?php if ($ope->descripcion != ""): ?>
                                <div class="product-desc"><?php echo $ope->descripcion;?></div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 0.75rem; text-align: center;"><?= $tipoBadge ?></td>
                        <td class="price-cell"><?php echo $ope->prec_alt; ?></td>
                        <td class="price-cell"><?php echo number_format($subtotal, 2, '.', ','); ?></td>
                    </tr>
                    <?php
						    $total = $subtotal + $total;
						}
                        $totalConDesc = $total - (float)$comp_cab->sumDescTotal;
                        $numLetra = NumeroLetras::convertir(number_format($totalConDesc, 2, '.', ''));
                        
                        $datosComprobante = array(
                            "venta"             => $venta,
                            "detalles"          => $detalles,
                            "comp_cab"          => $comp_cab,
                            "comp_aca"          => $comp_aca,
                            "comp_tri"          => $comp_tri,
                            "comp_ley"          => $comp_ley,
                            "empresa"           => $empresa,
                            "cajero"            => $cajero,
                            "numLetra"          => $numLetra,
                            "sell"              => $sell,
                            "pagoParcial"       => $datoPagoParcial,
                            "totales_impuestos" => [
                                "gravado"   => $subtotal_gravado,
                                "igv"       => $igv_total,
                                "exonerado" => $subtotal_exonerado,
                                "inafecto"  => $subtotal_inafecto,
                                "gratuito"  => $subtotal_gratuito
                            ]
                        );
					?>
                </tbody>
            </table>

            <!-- Totals Section Compacto -->
            <div class="totals-section">
                <div class="user-info">
                    <i class="fas fa-user-tie" style="color: #667eea;"></i>
                    <div>
                        <div><strong>Cajero:</strong> <?=$cajero?></div>
                        <small style="color: #666;">Terminal: CAJA-01</small>
                    </div>
                </div>

                <div class="totals-table">
                    <table>
                        <tr><td>Op. Gratuita</td><td>S/ <?php echo number_format($subtotal_gratuito, 2, '.', ','); ?></td></tr>
                        <tr><td>Op. Exonerada</td><td>S/ <?php echo number_format($subtotal_exonerado, 2, '.', ','); ?></td></tr>
                        <tr><td>Op. Inafecta</td><td>S/ <?php echo number_format($subtotal_inafecto, 2, '.', ','); ?></td></tr>
                        <tr><td>Op. Gravada</td><td>S/ <?php echo number_format($subtotal_gravado, 2, '.', ','); ?></td></tr>
                        <tr><td>IGV (18%)</td><td>S/ <?php echo number_format($igv_total, 2, '.', ','); ?></td></tr>
                        <tr class="total-row">
                            <td>TOTAL</td>
                            <td>S/ <?php echo number_format($totalConDesc, 2, '.', ','); ?></td>
                        </tr>
                    </table>
                    <div class="amount-in-words">
                        SON: <?php echo $numLetra; ?>
                    </div>
                    <?php
						echo "<input type='hidden' id='datosComprobante' name='datosComprobante' value='" . json_encode($datosComprobante) . "'>";
					?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function enviarSunat(sellId) {
        Swal.fire({
            title: '¿Enviar a SUNAT?',
            text: 'Se enviará el comprobante directamente a SUNAT vía web service SOAP.',
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
                $.post('./?action=send_sunat_ajax', { id: sellId }, function(res) {
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
    </script>
</body>
</html>
