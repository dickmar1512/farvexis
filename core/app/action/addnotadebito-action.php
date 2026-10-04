<?php
/**
 * addnotadebito-view.php
 * Genera la Nota de Débito (08), guarda en base de datos y envía directamente a SUNAT mediante SunatService.
 */

$conexion = Database::getCon();

if (count($_POST) > 0) {
    $TIPO        = '08';
    $TIPO_NOTA   = $_POST["tipo"] ?? "01";
    $MOTIVO      = $_POST["motivo"] ?? "Interés por mora";
    $SERIE       = $_POST["serie"] ?? "FD01";
    $COMPROBANTE = $_POST["comp"] ?? "1";
    $NUM         = $_POST["numDoc"] ?? "";
    $RUC         = '';
    $SELLID      = 0;

    $product = Factura2Data::getByNumDoc($NUM);
    if (!$product) {
        $sell = SellData::getByNumDoc($NUM);
        if ($sell) {
            $product = new Factura2Data();
            $product->id = 0;
            $product->SERIE = $sell->serie;
            $product->COMPROBANTE = str_pad((string)$sell->comprobante, 8, '0', STR_PAD_LEFT);
            $product->EXTRA1 = $sell->id;
            $product->RUC = EmpresaData::getDatos()->Emp_Ruc ?? '';
        } else {
            throw new RuntimeException('No se encontró la factura de referencia.');
        }
    }
    $RUC      = $product->RUC ?: (EmpresaData::getDatos()->Emp_Ruc ?? '');
    $SELLID   = $product->EXTRA1;
    if (!$SELLID || $SELLID == 0) {
        $sell = SellData::getByNumDoc($NUM);
        if ($sell) {
            $SELLID = $sell->id;
        }
    }

    $comp_cab = ($product->id > 0) ? CabData::getById($product->id, 1) : null;
    $detalles = ($product->id > 0) ? DetData::getByIdNota($product->id, 1) : [];
    $comp_tri = ($product->id > 0) ? TriData::getById($product->id, 1) : null;
    $comp_ley = ($product->id > 0) ? LeyData::getById($product->id, 1) : null;

    $sellObj = SellData::getById($SELLID);
    $personObj = ($sellObj && $sellObj->person_id) ? PersonData::getById($sellObj->person_id) : null;

    $tipOperacion      = $comp_cab->tipOperacion ?? '0101';
    $fecEmision        = date("Y-m-d");
    $horEmision        = date('H:i:s');
    $fecVencimiento    = $comp_cab->fecVencimiento ?? '-';
    $codLocalEmisor    = $comp_cab->codLocalEmisor ?? '0000';
    $tipDocUsuario     = $comp_cab->tipDocUsuario ?? '6';
    $numDocUsuario     = $comp_cab->numDocUsuario ?? '';
    $rznSocialUsuario  = $comp_cab->rznSocialUsuario ?? '';
    $tipMoneda         = $comp_cab->tipMoneda ?? 'PEN';
    $codTipoNota       = $TIPO_NOTA;
    $descMotivo        = $MOTIVO;
    $tipDocModifica    = (strpos($NUM, 'F') === 0) ? '01' : '03';
    $serieDocModifica  = $NUM;
    $sumTotTributos    = 0;
    $sumTotValVenta    = 0;
    $sumPrecioVenta    = 0;
    $sumDescTotal      = 0;
    $sumOtrosCargos    = 0;
    $sumTotalAnticipos = 0;
    $sumImpVenta       = 0;
    $ublVersionId      = '2.1';
    $customizationId   = '2.0';

    $codUnidadMedida   = 'NIU';
    $codProducto       = '0';
    $codProductoSUNAT  = '-';
    $codTriIGV         = '9997';
    $mtoIgvItem        = 0;
    $mtoBaseIgvItem    = 0;
    $nomTributoIgvItem = 'EXO';
    $codTipTributoIgvItem = 'VAT';
    $tipAfeIGV         = '20';
    $porIgvItem        = 0;
    $codTriISC         = "-";
    $mtoIscItem        = 0;
    $mtoBaseIscItem    = 0;
    $nomTributoIscItem = "";
    $codTipTributoIscItem = "";
    $tipSisISC         = "";
    $porIscItem        = "";
    $codTriOtroItem    = "-";
    $sumTotTributosItem = 0;
    $mtoTriOtroItem    = '';
    $mtoBaseTriOtroItem = '';
    $nomTributoIOtroItem = '';
    $codTipTributoIOtroItem = '';
    $porTriOtroItem    = '';
    $mtoValorReferencialUnitario = 0;

    $ideTributo        = '9997';
    $nomTributo        = 'EXO';
    $codTipTributo     = 'VAT';
    $mtoBaseImponible  = 0;
    $mtoTributo        = 0;
    $codLeyenda        = '1000';
    $desLeyenda        = '';

    $sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
    $COMPROBANTE = ComprobanteCorrelativoData::siguiente($conexion, $TIPO, $SERIE, $codLocalEmisor, $sucursalId);

    $sql_DOC = "INSERT INTO factura (RUC, TIPO, SERIE, COMPROBANTE, EXTRA1)
                VALUES ('$RUC', '$TIPO', '$SERIE', '$COMPROBANTE', '$SELLID')";
    $conexion->query($sql_DOC);
    $id_factura_impresa = $conexion->insert_id;
    $TIPO_DOC           = $TIPO;
    $ID_TIPO_DOC        = $id_factura_impresa;

    $detallesSunat = [];
    $itemIndex = 1;

    $insertarDetalle = function (
        $cantidad, $precio_unitario, $descripcion_producto,
        $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem, $igvItemTipo = '20'
    ) use (
        $conexion, &$detallesSunat, &$itemIndex,
        $TIPO_DOC, $ID_TIPO_DOC,
        $codUnidadMedida, $codProducto, $codProductoSUNAT,
        $sumTotTributosItem, $codTriIGV, $mtoIgvItem,
        $nomTributoIgvItem, $codTipTributoIgvItem, $tipAfeIGV, $porIgvItem,
        $codTriISC, $mtoIscItem, $mtoBaseIscItem,
        $nomTributoIscItem, $codTipTributoIscItem, $tipSisISC, $porIscItem,
        $codTriOtroItem, $mtoTriOtroItem, $mtoBaseTriOtroItem,
        $nomTributoIOtroItem, $codTipTributoIOtroItem, $porTriOtroItem,
        $mtoValorReferencialUnitario
    ) {
        $sql_DET = "INSERT INTO det (
            TIPO_DOC, ID_TIPO_DOC,
            codUnidadMedida, ctdUnidadItem, codProducto, codProductoSUNAT,
            desItem, mtoValorUnitario, sumTotTributosItem,
            codTriIGV, mtoIgvItem, mtoBaseIgvItem,
            nomTributoIgvItem, codTipTributoIgvItem, tipAfeIGV, porIgvItem,
            codTriISC, mtoIscItem, mtoBaseIscItem,
            nomTributoIscItem, codTipTributoIscItem, tipSisISC, porIscItem,
            codTriOtroItem, mtoTriOtroItem, mtoBaseTriOtroItem,
            nomTributoIOtroItem, codTipTributoIOtroItem, porTriOtroItem,
            mtoPrecioVentaUnitario, mtoValorVentaItem, mtoValorReferencialUnitario
        ) VALUES (
            '$TIPO_DOC', '$ID_TIPO_DOC',
            '$codUnidadMedida', '$cantidad', '$codProducto', '$codProductoSUNAT',
            '" . $conexion->real_escape_string($descripcion_producto) . "', '$precio_unitario', '$sumTotTributosItem',
            '$codTriIGV', '$mtoIgvItem', '$mtoBaseIgvItem',
            '$nomTributoIgvItem', '$codTipTributoIgvItem', '$tipAfeIGV', '$porIgvItem',
            '$codTriISC', '$mtoIscItem', '$mtoBaseIscItem',
            '$nomTributoIscItem', '$codTipTributoIscItem', '$tipSisISC', '$porIscItem',
            '$codTriOtroItem', '$mtoTriOtroItem', '$mtoBaseTriOtroItem',
            '$nomTributoIOtroItem', '$codTipTributoIOtroItem', '$porTriOtroItem',
            '$mtoPrecioVentaUnitario', '$mtoValorVentaItem', '$mtoValorReferencialUnitario'
        )";
        $conexion->query($sql_DET);

        $detallesSunat[] = [
            'item'            => $itemIndex++,
            'codigo'          => $codProducto,
            'descripcion'     => $descripcion_producto,
            'unidad_medida'   => $codUnidadMedida,
            'cantidad'        => $cantidad,
            'precio_unitario' => $precio_unitario,
            'precio_con_igv'  => $mtoPrecioVentaUnitario,
            'valor_venta'     => $mtoValorVentaItem,
            'igv_monto'       => $mtoIgvItem,
            'igv_tipo'        => $igvItemTipo
        ];
    };

    if ($TIPO_NOTA == '01') {
        $cantidad             = 1;
        $precio_unitario      = floatval($_POST['interes'] ?? 0);
        $descripcion_producto = $MOTIVO;
        $mtoValorVentaItem    = $precio_unitario;
        $mtoPrecioVentaUnitario = $precio_unitario;
        $mtoBaseIgvItem       = $mtoValorVentaItem;

        $insertarDetalle(
            $cantidad, $precio_unitario, $descripcion_producto,
            $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem, '20'
        );

        $sumTotValVenta = $cantidad * $precio_unitario;
        $sumPrecioVenta = $sumTotValVenta + $sumTotTributos;
    }

    $numLetra   = NumeroLetras::convertir(number_format($sumPrecioVenta, 2, '.', ''));
    $desLeyenda = $numLetra;

    $sql_CAB = "INSERT INTO nota (
        TIPO_DOC, ID_TIPO_DOC,
        tipOperacion, fecEmision, horEmision,
        codLocalEmisor, tipDocUsuario, numDocUsuario, rznSocialUsuario,
        tipMoneda, codTipoNota, descMotivo, tipDocModifica, serieDocModifica,
        sumTotTributos, sumTotValVenta, sumPrecioVenta,
        sumDescTotal, sumOtrosCargos, sumTotalAnticipos,
        sumImpVenta, ublVersionId, customizationId
    ) VALUES (
        '$TIPO_DOC', '$ID_TIPO_DOC',
        '$tipOperacion', DATE(NOW()), TIME(NOW()),
        '$codLocalEmisor', '$tipDocUsuario', '$numDocUsuario', '" . $conexion->real_escape_string($rznSocialUsuario) . "',
        '$tipMoneda', '$codTipoNota', '" . $conexion->real_escape_string($descMotivo) . "', '$tipDocModifica', '$serieDocModifica',
        '$sumTotTributos', '$sumTotValVenta', '$sumPrecioVenta',
        '$sumDescTotal', '$sumOtrosCargos', '$sumTotalAnticipos',
        '$sumImpVenta', '$ublVersionId', '$customizationId'
    )";
    $conexion->query($sql_CAB);

    $sql_TRI = "INSERT INTO tri (
        TIPO_DOC, ID_TIPO_DOC,
        ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo
    ) VALUES (
        '$TIPO_DOC', '$ID_TIPO_DOC',
        '$ideTributo', '$nomTributo', '$codTipTributo',
        '$mtoBaseImponible', '$mtoTributo'
    )";
    $conexion->query($sql_TRI);

    $sql_LEY = "INSERT INTO ley (
        TIPO_DOC, ID_TIPO_DOC, codLeyenda, desLeyenda
    ) VALUES (
        '$TIPO_DOC', '$ID_TIPO_DOC', '$codLeyenda', '" . $conexion->real_escape_string($desLeyenda) . "'
    )";
    $conexion->query($sql_LEY);

    // ENVÍO DIRECTO A SUNAT
    $comprobanteSunat = [
        'tipo_doc'             => '08',
        'serie'                => $SERIE,
        'correlativo'          => (int)$COMPROBANTE,
        'fecha_emision'        => $fecEmision,
        'hora_emision'         => $horEmision,
        'moneda'               => $tipMoneda,
        'total'                => round($sumPrecioVenta, 2),
        'subtotal_gravado'     => round($sumTotValVenta, 2),
        'igv_total'            => round($sumTotTributos, 2),
        'subtotal_exonerado'   => 0.00,
        'subtotal_inafecto'    => 0.00,
        'subtotal_gratuito'    => 0.00,
        'documento_referencia' => $NUM,
        'nota_motivo'          => $codTipoNota,
        'nota_sustento'        => $descMotivo,
    ];

    $clienteSunat = [
        'tipo_doc'     => $tipDocUsuario ?: '6',
        'numero_doc'   => $numDocUsuario,
        'razon_social' => $rznSocialUsuario,
        'direccion'    => '-',
    ];

    try {
        $sunatService = new SunatService();
        $resSunat = $sunatService->enviarNota($comprobanteSunat, $detallesSunat, $clienteSunat);
        $estadoSunat = $resSunat['estado_sunat'] ?? 'pendiente';
        $codigoSunat = $conexion->real_escape_string((string)($resSunat['codigo'] ?? ''));
        $descripcionSunat = $conexion->real_escape_string((string)($resSunat['descripcion'] ?? ''));
        $hashSunat = $conexion->real_escape_string((string)($resSunat['codigo_hash'] ?? ''));
        $conexion->query("UPDATE nota SET estado_sunat='$estadoSunat', cdr_codigo='$codigoSunat', cdr_descripcion='$descripcionSunat', codigo_hash='$hashSunat', fecha_envio_sunat=NOW() WHERE TIPO_DOC='08' AND ID_TIPO_DOC='$ID_TIPO_DOC'");
    } catch (Exception $e) {
        error_log("Error al enviar Nota de Débito: " . $e->getMessage());
        $resSunat = ['exito' => false, 'estado_sunat' => 'pendiente', 'descripcion' => $e->getMessage()];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => !empty($resSunat['exito']) ? 'success' : 'pending', 'estado_sunat' => $resSunat['estado_sunat'] ?? 'pendiente', 'message' => $resSunat['descripcion'] ?? 'Nota de débito procesada.']);
    return true;
}
?>
