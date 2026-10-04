<?php
/**
 * addfacturap-action.php
 * Genera los archivos planos (.cab, .det, .tri, .aca, .ley) para el sistema
 * efact1.3.4 a partir de una proforma de FACTURA (sell_id recibido por POST).
 * Usa Database::getCon() como única fuente de conexión.
 * Responde JSON para consumo AJAX.
 */
header('Content-Type: application/json; charset=utf-8');

if (count($_POST) > 0) {
    $conexion = Database::getCon();

    $empresa = EmpresaData::getDatos();
    $sell_id = $_POST['sell_id'];
    $sell    = SellData::get_ventas_x_id($sell_id);

    $RUC    = $empresa->Emp_Ruc;
    $TIPO   = $sell->tipo_comprobante;
    $estado = 1;

    // ── Cabecera ─────────────────────────────────────────────────────────────
    $tipOperacion   = '0101';
    $fecEmision     = date('Y-m-d');
    $horEmision     = date('H:i:s');
    $fecVencimiento = '-';
    $codLocalEmisor = '0001';
    $sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
    $SERIE = $TIPO == 1 ? 'F001' : 'B001';
    $COMPROBANTE = ComprobanteCorrelativoData::siguiente($conexion, (string)$TIPO, $SERIE, $codLocalEmisor, $sucursalId);
    $tipDocUsuario  = ($TIPO == 1) ? 6 : 1;

    $person_id = $sell->person_id;
    $numDocUsuario      = '00000000';
    $rznSocialUsuario   = 'CLIENTE GENERAL';
    $codUbigeoCliente   = '';
    $desDireccionCliente = '';

    if (!is_null($person_id)) {
        $person              = PersonData::getById($person_id);
        $numDocUsuario       = $person->numero_documento;
        $rznSocialUsuario    = $person->name . ' ' . $person->lastname;
        $codUbigeoCliente    = $person->ubigeo;
        $desDireccionCliente = $person->address1;
    }

    $tipMoneda      = 'PEN';
    $sumTotTributos = 0;
    $sumTotValVenta = 0;
    $sumPrecioVenta = 0;

    // ── Detalle ──────────────────────────────────────────────────────────────
    $codUnidadMedida             = 'NIU';
    $codProducto                 = 0;
    $codProductoSUNAT            = '-';
    $codTriIGV                   = '9997';
    $mtoIgvItem                  = 0;
    $nomTributoIgvItem           = 'EXO';
    $codTipTributoIgvItem        = 'VAT';
    $tipAfeIGV                   = 20;
    $porIgvItem                  = 0;
    $codTriISC                   = '-';
    $mtoIscItem                  = '';
    $mtoBaseIscItem              = 0;
    $nomTributoIscItem           = '';
    $codTipTributoIscItem        = '-';
    $tipSisISC                   = '';
    $porIscItem                  = '';
    $codTriOtroItem              = '-';
    $mtoTriOtroItem              = '';
    $mtoBaseTriOtroItem          = '';
    $nomTributoIOtroItem         = '';
    $codTipTributoIOtroItem      = '';
    $porTriOtroItem              = '';
    $mtoValorReferencialUnitario = 0;
    $sumTotTributosItem          = 0;

    // ── Tributo ──────────────────────────────────────────────────────────────
    $ideTributo      = '9997';
    $nomTributo      = 'EXO';
    $codTipTributo   = 'VAT';
    $mtoBaseImponible = 0;
    $mtoTributo      = 0;

    // ── Leyenda ──────────────────────────────────────────────────────────────
    $codLeyenda = '2001';
    $desLeyenda = 'BIENES TRANSFERIDOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA';

    // ── ACA ──────────────────────────────────────────────────────────────────
    $ctaBancoNacionDetraccion = '-';
    $codBienDetraccion        = '-';
    $porDetraccion            = '-';
    $mtoDetraccion            = '-';
    $codPaisCliente           = 'PE';
    $codPaisEntrega           = '-';
    $codUbigeoEntrega         = '-';
    $desDireccionEntrega      = '-';

    // ── Insertar en tabla factura y obtener ID ─────────────────────────────
    $conexion->query("INSERT INTO factura (RUC, TIPO, SERIE, COMPROBANTE, EXTRA1) VALUES (
        '{$RUC}', '{$TIPO}', '{$SERIE}', '{$COMPROBANTE}', '{$sell_id}'
    )");
    $id_factura_impresa = $conexion->insert_id;

    $TIPO_DOC    = $TIPO;
    $ID_TIPO_DOC = $id_factura_impresa;

    // ── Cambiar estado de proforma → venta ───────────────────────────────
    $sellObj = new SellData();
    $sellObj->id     = $sell_id;
    $sellObj->estado = $estado;
    $sellObj->update_proforma_venta();

    // ── Procesar operaciones (DET) ────────────────────────────────────────
    $operations   = OperationData::getAllProductsBySellId($sell_id);
    $filecontent2 = '';

    foreach ($operations as $item) {
        $product                = $item->getProduct();
        $cantidad               = $item->q;
        $precio_unitario        = $item->prec_alt;
        $descripcion_producto   = $conexion->real_escape_string($product->name);
        $mtoValorVentaItem      = $cantidad * $precio_unitario;
        $mtoPrecioVentaUnitario = $precio_unitario;
        $mtoBaseIgvItem         = $mtoValorVentaItem;

        $filecontent2 .= "{$codUnidadMedida}|{$cantidad}|{$codProducto}|{$codProductoSUNAT}|{$descripcion_producto}|{$precio_unitario}|{$sumTotTributosItem}|{$codTriIGV}|{$mtoIgvItem}|{$mtoBaseIgvItem}|{$nomTributoIgvItem}|{$codTipTributoIgvItem}|{$tipAfeIGV}|{$porIgvItem}|{$codTriISC}|{$mtoIscItem}|{$mtoBaseIscItem}|{$nomTributoIscItem}|{$codTipTributoIscItem}|{$tipSisISC}|{$porIscItem}|{$codTriOtroItem}|{$mtoTriOtroItem}|{$mtoBaseTriOtroItem}|{$nomTributoIOtroItem}|{$codTipTributoIOtroItem}|{$porTriOtroItem}|{$mtoPrecioVentaUnitario}|{$mtoValorVentaItem}|{$mtoValorReferencialUnitario}|" . PHP_EOL;

        $conexion->query("INSERT INTO det (TIPO_DOC, ID_TIPO_DOC, codUnidadMedida, ctdUnidadItem, codProducto, codProductoSUNAT, desItem, mtoValorUnitario, sumTotTributosItem, codTriIGV, mtoIgvItem, mtoBaseIgvItem, nomTributoIgvItem, codTipTributoIgvItem, tipAfeIGV, porIgvItem, codTriISC, mtoIscItem, mtoBaseIscItem, nomTributoIscItem, codTipTributoIscItem, tipSisISC, porIscItem, codTriOtroItem, mtoTriOtroItem, mtoBaseTriOtroItem, nomTributoIOtroItem, codTipTributoIOtroItem, porTriOtroItem, mtoPrecioVentaUnitario, mtoValorVentaItem, mtoValorReferencialUnitario)
        VALUES ('{$TIPO_DOC}','{$ID_TIPO_DOC}','{$codUnidadMedida}','{$cantidad}','{$codProducto}','{$codProductoSUNAT}','{$descripcion_producto}','{$precio_unitario}','{$sumTotTributosItem}','{$codTriIGV}','{$mtoIgvItem}','{$mtoBaseIgvItem}','{$nomTributoIgvItem}','{$codTipTributoIgvItem}','{$tipAfeIGV}','{$porIgvItem}','{$codTriISC}','{$mtoIscItem}','{$mtoBaseIscItem}','{$nomTributoIscItem}','{$codTipTributoIscItem}','{$tipSisISC}','{$porIscItem}','{$codTriOtroItem}','{$mtoTriOtroItem}','{$mtoBaseTriOtroItem}','{$nomTributoIOtroItem}','{$codTipTributoIOtroItem}','{$porTriOtroItem}','{$mtoPrecioVentaUnitario}','{$mtoValorVentaItem}','{$mtoValorReferencialUnitario}')");

        $sumTotValVenta = $sumTotValVenta + $mtoValorVentaItem;
        $sumPrecioVenta = $sumTotValVenta + $sumTotTributos;
    }

    $mtoBaseImponible  = $sumTotValVenta;
    $sumDescTotal      = 0;
    $sumOtrosCargos    = 0;
    $sumTotalAnticipos = 0;
    $sumImpVenta       = $sumPrecioVenta - $sumDescTotal + $sumOtrosCargos - $sumTotalAnticipos;
    $ublVersionId      = '2.1';
    $customizationId   = '2.0';

    // ── CAB BD ───────────────────────────────────────────────────────────────
    $conexion->query("INSERT INTO cab (TIPO_DOC, ID_TIPO_DOC, tipOperacion, fecEmision, horEmision, fecVencimiento, codLocalEmisor, tipDocUsuario, numDocUsuario, rznSocialUsuario, tipMoneda, sumTotTributos, sumTotValVenta, sumPrecioVenta, sumDescTotal, sumOtrosCargos, sumTotalAnticipos, sumImpVenta, ublVersionId, customizationId)
    VALUES ('{$TIPO_DOC}','{$ID_TIPO_DOC}','{$tipOperacion}','{$fecEmision}','{$horEmision}','{$fecVencimiento}','{$codLocalEmisor}','{$tipDocUsuario}','{$numDocUsuario}','{$conexion->real_escape_string($rznSocialUsuario)}','{$tipMoneda}','{$sumTotTributos}','{$sumTotValVenta}','{$sumPrecioVenta}','{$sumDescTotal}','{$sumOtrosCargos}','{$sumTotalAnticipos}','{$sumImpVenta}','{$ublVersionId}','{$customizationId}')");

    // ── TRI BD ───────────────────────────────────────────────────────────────
    $conexion->query("INSERT INTO tri (TIPO_DOC, ID_TIPO_DOC, ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo)
    VALUES ('{$TIPO_DOC}','{$ID_TIPO_DOC}','{$ideTributo}','{$nomTributo}','{$codTipTributo}','{$mtoBaseImponible}','{$mtoTributo}')");

    // ── ACA BD ───────────────────────────────────────────────────────────────
    $conexion->query("INSERT INTO aca (TIPO_DOC, ID_TIPO_DOC, ctaBancoNacionDetraccion, codBienDetraccion, porDetraccion, mtoDetraccion, codPaisCliente, codUbigeoCliente, desDireccionCliente, codPaisEntrega, codUbigeoEntrega, desDireccionEntrega)
    VALUES ('{$TIPO_DOC}','{$ID_TIPO_DOC}','{$ctaBancoNacionDetraccion}','{$codBienDetraccion}','{$porDetraccion}','{$mtoDetraccion}','{$codPaisCliente}','{$codUbigeoCliente}','{$conexion->real_escape_string($desDireccionCliente)}','{$codPaisEntrega}','{$codUbigeoEntrega}','{$desDireccionEntrega}')");

    // ── LEY BD ───────────────────────────────────────────────────────────────
    $conexion->query("INSERT INTO ley (TIPO_DOC, ID_TIPO_DOC, codLeyenda, desLeyenda)
    VALUES ('{$TIPO_DOC}','{$ID_TIPO_DOC}','{$codLeyenda}','{$conexion->real_escape_string($desLeyenda)}')");

    // ── Archivos planos ─────────────────────────────────────────────────────
    $base_path = "../efact1.3.4/sunat_archivos/sfs/DATA/{$RUC}-{$TIPO}-{$SERIE}-{$COMPROBANTE}";

    file_put_contents("{$base_path}.det", $filecontent2, FILE_APPEND);
    file_put_contents("{$base_path}.cab",
        "{$tipOperacion}|{$fecEmision}|{$horEmision}|{$fecVencimiento}|{$codLocalEmisor}|{$tipDocUsuario}|{$numDocUsuario}|{$rznSocialUsuario}|{$tipMoneda}|{$sumTotTributos}|{$sumTotValVenta}|{$sumPrecioVenta}|{$sumDescTotal}|{$sumOtrosCargos}|{$sumTotalAnticipos}|{$sumImpVenta}|{$ublVersionId}|{$customizationId}|",
        FILE_APPEND);
    file_put_contents("{$base_path}.tri",
        "{$ideTributo}|{$nomTributo}|{$codTipTributo}|{$mtoBaseImponible}|{$mtoTributo}|",
        FILE_APPEND);
    file_put_contents("{$base_path}.aca",
        "{$ctaBancoNacionDetraccion}|{$codBienDetraccion}|{$porDetraccion}|{$mtoDetraccion}|{$codPaisCliente}|{$codUbigeoCliente}|{$desDireccionCliente}|{$codPaisEntrega}|{$codUbigeoEntrega}|{$desDireccionEntrega}|",
        FILE_APPEND);
    file_put_contents("{$base_path}.ley",
        "{$codLeyenda}|{$desLeyenda}|",
        FILE_APPEND);

    echo json_encode([
        'status'   => 'success',
        'message'  => 'Factura generada correctamente.',
        'redirect' => "./?view=onesell2&id={$sell_id}"
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Petición no válida.']);
}
?>
