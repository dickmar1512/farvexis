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

$SERIE_F = "BND1";
$ultimo_num = 0;
$resCorr = $conexion->query("SELECT ultimo_numero FROM comprobante_correlativo WHERE tipo_comprobante = '08' AND serie = '$SERIE_F' LIMIT 1");
if ($resCorr && $rCorr = $resCorr->fetch_assoc()) {
    $ultimo_num = (int)$rCorr['ultimo_numero'];
}
$resBol = $conexion->query("SELECT COALESCE(MAX(CAST(COMPROBANTE AS UNSIGNED)), 0) AS max_num FROM boleta WHERE TIPO = '08' AND SERIE = '$SERIE_F'");
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
        background: linear-gradient(135deg, #16a34a, #166534);
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
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 14px;
        padding: 18px 18px 10px;
        margin-bottom: 20px;
    }
    .note-rules h5 {
        margin: 0 0 12px;
        color: #166534;
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
        color: #166534;
        border: 1px solid #bbf7d0;
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
        border-color: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
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
        background: linear-gradient(135deg, #22c55e, #15803d);
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
        box-shadow: 0 12px 20px rgba(34, 197, 94, 0.25);
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
                <h1 class="m-0" style="color: #166534;"><i class='fas fa-file-invoice-dollar'></i> Nota de débito boleta</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Facturación</a></li>
                    <li class="breadcrumb-item active">Nota de débito boleta</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="note-form-shell">
            <div class="note-form-header">
                <h1><i class="fa fa-file"></i> Generar nota de débito</h1>
                <small>La nota de débito permite informar un incremento del importe adeudado por intereses, cargos o ajustes en la boleta.</small>
            </div>
            <div class="note-form-body">
                <?php if ($product === null): ?>
                    <div class="note-info-alert">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Atención!</h5>
                        No se encontró ninguna Boleta asociada al ID de venta <b><?php echo htmlspecialchars($_GET["id"] ?? ""); ?></b>. Asegúrese de que la venta existe y tiene un comprobante de tipo Boleta generado.
                    </div>
                <?php endif; ?>

                <div class="note-rules">
                    <h5><i class="fas fa-file-contract"></i> Reglas SUNAT para notas de débito</h5>
                    <ul class="note-rule-list">
                        <li>El documento relacionado debe apuntar exactamente al comprobante origen: <strong><?php echo $product ? htmlspecialchars($product->SERIE . '-' . $product->COMPROBANTE) : 'B001-00000000'; ?></strong>.</li>
                        <li>La serie de la nota debe mantener la misma inicial del comprobante origen: si el original es factura, la nota debe ser <strong>F...</strong>; si es boleta, debe ser <strong>B...</strong>.</li>
                        <li>El sustento o motivo es obligatorio y debe estar redactado de forma legible para justificar la operación.</li>
                    </ul>
                    <div class="note-type-grid">
                        <span class="note-type-tag">01 Intereses</span>
                        <span class="note-type-tag">02 Aumento</span>
                        <span class="note-type-tag">03 Penalidades</span>
                        <span class="note-type-tag">11 Exportación</span>
                    </div>
                </div>

                <div class="note-form-grid">
                    <div class="note-form-field">
                        <label><i class="icon-barcode2 position-left"></i> Serie</label>
                        <input type="text" name="serie_comprobante" id="serie_comprobante" value="<?php echo $SERIE_F; ?>" readonly>
                    </div>
                    <div class="note-form-field">
                        <label><i class="icon-file-text2 position-left"></i> Número</label>
                        <input type="text" name="numero_comprobante" id="numero_comprobante" value="<?php echo $COMPROBANTE_F; ?>" readonly>
                    </div>
                    <div class="note-form-field">
                        <label><i class="icon-file-text position-left"></i> N° Doc. Modificado</label>
                        <?php if ($product): ?>
                            <input type="text" name="num_comprobante_modificado" id="num_comprobante_modificado" readonly value="<?php echo $product->SERIE . "-" . $product->COMPROBANTE; ?>">
                        <?php else: ?>
                            <input type="text" name="num_comprobante_modificado" id="num_comprobante_modificado" readonly value="DOCUMENTO NO ENCONTRADO" style="background:#fff3cd; color:#7c4a00; border-color:#f6c453;">
                        <?php endif; ?>
                    </div>
                    <div class="note-form-field">
                        <label><i class="icon-profile position-left"></i> Tipo <span class="text-danger">*</span></label>
                        <select title="Selecciona el Tipo" data-placeholder="Selecciona el Tipo" name="notacredito_motivo_id" id="notacredito_motivo_id" required tabindex="-1" aria-hidden="true">
                            <option value="01">INTERESES POR MORA</option>
                            <option value="02">AUMENTO EN EL VALOR</option>
                            <option value="03">PENALIDADES/OTROS CONCEPTOS</option>
                            <option value="11">AJUSTES DE OPERACIONES DE EXPORTACIÓN</option>
                        </select>
                    </div>
                    <div class="note-form-field full">
                        <label><i class="icon-barcode2 position-left"></i> Motivo</label>
                        <input type="text" name="motivo" id="motivo" value="" required>
                    </div>
                    <div class="note-form-field" id="dscto_global" style="display:block;">
                        <label><i class="icon-barcode2 position-left"></i> Intereses por mora (Monto S/)</label>
                        <input type="number" name="interes" id="interes" value="" required>
                    </div>
                </div>

                <div class="note-form-actions">
                    <button type="button" id="buscar" name="buscar" <?php if (!$product) echo "disabled"; ?>>Continuar</button>
                </div>

                <div id="datos" name="datos"></div>
            </div>
        </div>
    </div>
</section>

<script>
$(function () {
    function validarNotaDebito() {
        var originalDoc = ($('#num_comprobante_modificado').val() || '').trim();
        var motivo = ($('#motivo').val() || '').trim();
        var serieNueva = ($('#serie_comprobante').val() || '').trim();

        if (!originalDoc || originalDoc.indexOf('-') === -1 || originalDoc.indexOf('DOCUMENTO NO ENCONTRADO') !== -1) {
            Swal.fire('Validación SUNAT', 'Debe existir un documento relacionado válido para la nota de débito.', 'warning');
            return false;
        }

        if (!motivo) {
            Swal.fire('Validación SUNAT', 'Debe registrar un sustento o motivo legible para la nota de débito.', 'warning');
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

    $('#buscar').on('click', function (event) {
        event.preventDefault();

        if (!validarNotaDebito()) {
            return;
        }

        var numDoc = $('#num_comprobante_modificado').val();
        var tipo = $('#notacredito_motivo_id').val();
        var serie = $('#serie_comprobante').val();
        var comp = $('#numero_comprobante').val();
        var motivo = $('#motivo').val();

        if (tipo === '01') {
            var interes = $('#interes').val();

            $.ajax({
                type: 'POST',
                data: {
                    numDoc: numDoc,
                    motivo: motivo,
                    serie: serie,
                    comp: comp,
                    tipo: tipo,
                    interes: interes
                },
                url: './?action=addnotadebitoboleta',
                success: function () {
                    window.location.href = './?view=notadebitoboletat&num=' + serie + '-' + comp;
                }
            });
            return;
        }

        if (['03', '05', '07'].indexOf(tipo) !== -1) {
            $.ajax({
                type: 'POST',
                data: {
                    numDoc: numDoc,
                    tipo: tipo
                },
                url: './?action=obtener_datos_factura_ajax',
                success: function (data) {
                    $('#datos').html(data);
                }
            });
            return;
        }

        if (tipo === '04') {
            $.ajax({
                type: 'POST',
                data: {
                    numDoc: numDoc,
                    motivo: motivo,
                    serie: serie,
                    comp: comp,
                    tipo: tipo,
                    dscto: $('#dscto').val() || 0
                },
                url: './?action=addnotacredito',
                success: function (data) {
                    $('#datos').html(data);
                }
            });
        }
    });

    $('#notacredito_motivo_id').on('change', function () {
        $('#dscto_global').toggle($(this).val() === '01');
    });
});
</script>

