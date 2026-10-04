<?php
$conexion = Database::getCon();

$idParam = $_GET["id"] ?? 0;
$product = BoletaData::getByExtra($idParam);
if (!$product) {
    $product = BoletaData::getById($idParam);
}
if (!$product && $idParam) {
    $sell = SellData::getById($idParam);
    if ($sell) {
        $product = BoletaData::getByNumDoc($sell->serie . '-' . $sell->comprobante);
        if (!$product) {
            $product = new BoletaData();
            $product->id = 0;
            $product->SERIE = $sell->serie;
            $product->COMPROBANTE = str_pad((string)$sell->comprobante, 8, '0', STR_PAD_LEFT);
            $product->EXTRA1 = $sell->id;
            $product->RUC = EmpresaData::getDatos()->Emp_Ruc ?? '';
        }
    }
}

$SERIE_F = "BNC1";
$ultimo_num = 0;
$resCorr = $conexion->query("SELECT ultimo_numero FROM comprobante_correlativo WHERE tipo_comprobante = '07' AND serie = '$SERIE_F' LIMIT 1");
if ($resCorr && $rCorr = $resCorr->fetch_assoc()) {
    $ultimo_num = (int)$rCorr['ultimo_numero'];
}
$resBol = $conexion->query("SELECT COALESCE(MAX(CAST(COMPROBANTE AS UNSIGNED)), 0) AS max_num FROM boleta WHERE TIPO = '07' AND SERIE = '$SERIE_F'");
if ($resBol && $rBol = $resBol->fetch_assoc()) {
    $ultimo_num = max($ultimo_num, (int)$rBol['max_num']);
}
$COMPROBANTE_F = str_pad((string)($ultimo_num + 1), 8, '0', STR_PAD_LEFT);

$empresa = EmpresaData::getDatos();
?>

<style>
    .note-form-shell {
        background: linear-gradient(135deg, rgba(255,255,255,0.97), rgba(248,250,252,0.96));
        border: 1px solid rgba(148,163,184,0.25);
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }
    .note-form-header {
        background: linear-gradient(135deg, #ef4444, #7f1d1d);
        color: white;
        padding: 22px 24px;
    }
    .note-form-header h1 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .note-form-header small {
        display: block;
        margin-top: 8px;
        opacity: 0.85;
    }
    .note-form-body {
        padding: 24px;
    }
    .note-info-alert {
        background: #fff7e6;
        border: 1px solid #f7d590;
        color: #8a5d1e;
        padding: 14px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    .note-rules {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        border-radius: 14px;
        padding: 18px 18px 10px;
        margin-bottom: 20px;
    }
    .note-rules h5 {
        margin: 0 0 12px;
        color: #991b1b;
        font-weight: 700;
    }
    .note-rule-list {
        margin: 0;
        padding-left: 18px;
        color: #1f2937;
        line-height: 1.7;
    }
    .note-type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 8px;
        margin-top: 10px;
    }
    .note-type-tag {
        display: inline-block;
        background: #fff;
        color: #991b1b;
        border: 1px solid #fecdd3;
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 0.76rem;
        font-weight: 700;
        text-align: center;
    }
    .note-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(220px, 1fr));
        gap: 16px;
        align-items: end;
    }
    .note-form-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .note-form-field label {
        font-weight: 700;
        color: #374151;
        font-size: 0.82rem;
    }
    .note-form-field input,
    .note-form-field select {
        width: 100%;
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        padding: 10px 12px;
        background: #fff;
        color: #1f2937;
        font-size: 0.95rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .note-form-field input:focus,
    .note-form-field select:focus {
        outline: none;
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
    }
    .note-form-field.full {
        grid-column: 1 / -1;
    }
    .note-form-actions {
        text-align: center;
        margin-top: 22px;
    }
    .note-form-actions button {
        min-width: 180px;
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 12px 18px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .note-form-actions button:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 20px rgba(239, 68, 68, 0.25);
    }
    @media (max-width: 768px) {
        .note-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: #b91c1c;"><i class='fas fa-file-invoice'></i> Nota de crédito boleta</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Reportes</a></li>
                    <li class="breadcrumb-item active">Nota de crédito boleta</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="note-form-shell">
            <div class="note-form-header">
                <h1><i class="fa fa-file"></i> Generar nota de crédito</h1>
                <small>La nota de crédito permitirá anular una boleta en caso de que el proceso de venta haya sido anulado o corregido.</small>
            </div>
            <div class="note-form-body">
                <?php if ($product === null): ?>
                    <div class="note-info-alert">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Atención!</h5>
                        No se encontró ninguna Boleta asociada al ID de venta <b><?php echo htmlspecialchars($_GET["id"] ?? ""); ?></b>. Asegúrese de que la venta existe y tiene un comprobante de tipo Boleta generado.
                    </div>
                <?php endif; ?>

                <div class="note-rules">
                    <h5><i class="fas fa-file-contract"></i> Reglas SUNAT para notas de crédito</h5>
                    <ul class="note-rule-list">
                        <li>El documento relacionado debe apuntar exactamente al comprobante origen: <strong><?php echo $product ? htmlspecialchars($product->SERIE . '-' . $product->COMPROBANTE) : 'B001-00000000'; ?></strong>.</li>
                        <li>La serie de la nota debe mantener la misma inicial del comprobante origen: si el original es factura, la nota debe ser <strong>F...</strong>; si es boleta, debe ser <strong>B...</strong>.</li>
                        <li>El sustento o motivo es obligatorio y debe estar redactado de forma legible para justificar la operación.</li>
                    </ul>
                    <div class="note-type-grid">
                        <span class="note-type-tag">01 Anulación</span>
                        <span class="note-type-tag">04 Descuento</span>
                        <span class="note-type-tag">06 Devolución</span>
                        <span class="note-type-tag">10 Otros</span>
                        <span class="note-type-tag">13 Cuotas</span>
                    </div>
                </div>

                <div class="note-form-grid">
                    <div class="note-form-field">
                        <label><i class="icon-barcode2 position-left"></i> Serie</label>
                        <input type="text" name="serie_comprobanteb" id="serie_comprobanteb" value="<?php echo $SERIE_F; ?>" readonly>
                    </div>
                    <div class="note-form-field">
                        <label><i class="icon-file-text2 position-left"></i> Número</label>
                        <input type="text" name="numero_comprobanteb" id="numero_comprobanteb" value="<?php echo $COMPROBANTE_F; ?>" readonly>
                    </div>
                    <div class="note-form-field">
                        <label><i class="icon-file-text position-left"></i> N° Doc. Modificado</label>
                        <?php if ($product): ?>
                            <input type="text" name="num_comprobante_modificadob" id="num_comprobante_modificadob" readonly value="<?php echo $product->SERIE."-".$product->COMPROBANTE; ?>" style="background:#fff7ed; border-color:#fdba74;">
                        <?php else: ?>
                            <input type="text" name="num_comprobante_modificadob" id="num_comprobante_modificadob" readonly value="DOCUMENTO NO ENCONTRADO" style="background:#fff3cd; color:#7c4a00; border-color:#f6c453;">
                        <?php endif; ?>
                    </div>
                    <div class="note-form-field">
                        <label><i class="icon-profile position-left"></i> Tipo <span class="text-danger">*</span></label>
                        <select title="Selecciona el Tipo" data-placeholder="Selecciona el Tipo" name="notacredito_motivo_idb" id="notacredito_motivo_idb" required tabindex="-1" aria-hidden="true">
                            <option value="01">ANULACION DE LA OPERACION</option>
                            <option class="hide" value="02">ANULACION POR ERROR EN EL RUC</option>
                            <option class="hide" value="03">CORRECION POR ERROR EN LA DESCRIPCION</option>
                            <option class="hide" value="04">DESCUENTO GLOBAL</option>
                            <option class="hide" value="05">DESCUENTO POR ITEM</option>
                            <option class="hide" value="06">DEVOLUCION TOTAL</option>
                            <option class="hide" value="07">DEVOLUCION POR ITEM</option>
                            <option class="hide" value="08">BONIFICACION</option>
                            <option class="hide" value="09">DISMINUCION EN EL VALOR</option>
                            <option class="hide" value="10">OTROS CONCEPTOS</option>
                            <option class="hide" value="13">MODIFICACION DE CUOTAS / MONTOS NETOS</option>
                        </select>
                    </div>
                    <div class="note-form-field">
                        <label><i class="icon-paperplane position-left"></i> Envío a SUNAT</label>
                        <select name="envio_sunatb" id="envio_sunatb" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #cbd5e1;">
                            <option value="manual" selected>Manual (Pendiente)</option>
                            <option value="automatico">Automático (Inmediato)</option>
                        </select>
                    </div>
                    <div class="note-form-field full">
                        <label><i class="icon-barcode2 position-left"></i> Motivo</label>
                        <input type="text" name="motivob" id="motivob" value="" autofocus required>
                    </div>
                    <div class="note-form-field" id="dscto_globalb" style="display:none;">
                        <label><i class="icon-barcode2 position-left"></i> Descuento</label>
                        <input type="number" name="dsctob" id="dsctob" value="" required>
                    </div>
                </div>

                <div class="note-form-actions">
                    <button type="button" id="buscarb" name="buscarb" <?php if (!$product) echo "disabled"; ?>>Continuar</button>
                </div>

                <div id="datosb" name="datosb"></div>
            </div>
        </div>
    </div>
</section>

<script>
$(function () {
    function validarNotaCreditoBoleta() {
        var originalDoc = ($('#num_comprobante_modificadob').val() || '').trim();
        var motivo = ($('#motivob').val() || '').trim();
        var serieNueva = ($('#serie_comprobanteb').val() || '').trim();

        if (!originalDoc || originalDoc.indexOf('-') === -1 || originalDoc.indexOf('DOCUMENTO NO ENCONTRADO') !== -1) {
            Swal.fire('Validación SUNAT', 'Debe existir un documento relacionado válido para la nota de crédito.', 'warning');
            return false;
        }

        if (!motivo) {
            Swal.fire('Validación SUNAT', 'Debe registrar un sustento legible que justifique la nota de crédito.', 'warning');
            return false;
        }

        var docOrigen = originalDoc.split('-')[0].trim();
        var prefijoOrigen = docOrigen.charAt(0).toUpperCase();
        var prefijoNueva = (serieNueva.charAt(0) || '').toUpperCase();

        if (prefijoOrigen && prefijoNueva && prefijoOrigen !== prefijoNueva) {
            Swal.fire('Validación SUNAT', 'La serie de la nota debe corresponder al tipo de comprobante origen: factura con F y boleta con B.', 'warning');
            return false;
        }

        return true;
    }

    $('#buscarb').on('click', function (event) {
        event.preventDefault();

        if (!validarNotaCreditoBoleta()) {
            return;
        }

        var tipo = $('#notacredito_motivo_idb').val();
        var data = {
            numDoc: $('#num_comprobante_modificadob').val(),
            motivo: $('#motivob').val(),
            serie: $('#serie_comprobanteb').val(),
            comp: $('#numero_comprobanteb').val(),
            tipo: tipo,
            envio_sunat: $('#envio_sunatb').val() || 'manual',
            dscto: $('#dsctob').val() || 0
        };

        if (['03', '05', '07'].indexOf(tipo) !== -1) {
            $.post('./?action=obtener_datos_factura_ajax', { numDoc: data.numDoc, tipo: tipo }, function (response) {
                $('#datosb').html(response);
            });
            return;
        }

        $.post('./?action=addnotacreditoboleta', data, function (response) {
            var message = response.message || 'Nota de credito procesada.';
            Swal.fire(response.status === 'success' ? 'Procesada' : 'Pendiente', message, response.status === 'success' ? 'success' : 'warning');
        }, 'json').fail(function (xhr) {
            Swal.fire('Error', xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'No se pudo generar la nota.', 'error');
        });
    });

    $('#notacredito_motivo_idb').on('change', function () {
        $('#dscto_globalb').toggle($(this).val() === '04');
    });
});
</script>
<!-- /.content -->	
