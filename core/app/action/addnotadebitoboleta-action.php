<?php
header('Content-Type: application/json; charset=utf-8');

try {
    if (count($_POST) === 0) {
        throw new RuntimeException('No se recibieron datos de la nota de débito.');
    }

    $conexion = Database::getCon();
    $numeroReferencia = trim($_POST['numDoc'] ?? '');
    $original = BoletaData::getByNumDoc($numeroReferencia);
    if (!$original) {
        $sell = SellData::getByNumDoc($numeroReferencia);
        if ($sell) {
            $original = new BoletaData();
            $original->id = 0;
            $original->SERIE = $sell->serie;
            $original->COMPROBANTE = str_pad((string)$sell->comprobante, 8, '0', STR_PAD_LEFT);
            $original->EXTRA1 = $sell->id;
            $original->RUC = EmpresaData::getDatos()->Emp_Ruc ?? '';
        } else {
            throw new RuntimeException('No se encontró la boleta de referencia.');
        }
    }

    $cabecera = ($original->id > 0) ? CabData::getById($original->id, 3) : null;
    $detalles = ($original->id > 0) ? DetData::getByIdNota($original->id, 3) : [];
    $tributos = ($original->id > 0) ? TriData::getById($original->id, 3) : null;
    $leyenda = ($original->id > 0) ? LeyData::getById($original->id, 3) : null;
    $detalle = $detalles[0] ?? null;

    $serie = trim($_POST['serie'] ?? 'BND1');
    $comprobante = (int)($_POST['comp'] ?? 1);
    $motivo = trim($_POST['motivo'] ?? 'Interés por mora');
    $monto = round((float)($_POST['interes'] ?? 0), 2);
    if ($monto <= 0) {
        throw new RuntimeException('El importe de la nota de débito debe ser mayor que cero.');
    }

    $tipoDoc = '08';
    $ruc = $conexion->real_escape_string((string)$original->RUC);
    $serieSql = $conexion->real_escape_string($serie);
    $motivoSql = $conexion->real_escape_string($motivo);
    $docSql = $conexion->real_escape_string($numeroReferencia);
    $cliente = [
        'tipo_doc' => $cabecera->tipDocUsuario ?? '1',
        'numero_doc' => $cabecera->numDocUsuario ?? '',
        'razon_social' => $cabecera->rznSocialUsuario ?? '',
        'direccion' => '-'
    ];

    $sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
    $comprobante = (int)ComprobanteCorrelativoData::siguiente(
        $conexion,
        $tipoDoc,
        $serie,
        $cabecera->codLocalEmisor ?? '0000',
        $sucursalId
    );
    $conexion->query("INSERT INTO boleta (RUC, TIPO, SERIE, COMPROBANTE) VALUES ('$ruc', '$tipoDoc', '$serieSql', '$comprobante')");
    $documentId = $conexion->insert_id;
    $igvTipo = $detalle->tipAfeIGV ?? '20';
    $igvMonto = $igvTipo === '10' ? round($monto * 0.18, 2) : 0.00;
    $base = $igvTipo === '10' ? round($monto - $igvMonto, 2) : $monto;
    $codTri = $igvTipo === '10' ? '1000' : ($igvTipo === '30' ? '9998' : '9997');
    $nomTri = $igvTipo === '10' ? 'IGV' : ($igvTipo === '30' ? 'INA' : 'EXO');
    $nombre = $conexion->real_escape_string($motivo);

    $conexion->query("INSERT INTO det (TIPO_DOC, ID_TIPO_DOC, codUnidadMedida, ctdUnidadItem, codProducto, codProductoSUNAT, desItem, mtoValorUnitario, sumTotTributosItem, codTriIGV, mtoIgvItem, mtoBaseIgvItem, nomTributoIgvItem, codTipTributoIgvItem, tipAfeIGV, porIgvItem, mtoPrecioVentaUnitario, mtoValorVentaItem, mtoValorReferencialUnitario) VALUES ('$tipoDoc', '$documentId', 'NIU', 1, '0', '-', '$nombre', '$monto', '$igvMonto', '$codTri', '$igvMonto', '$base', '$nomTri', 'VAT', '$igvTipo', '" . ($igvTipo === '10' ? '18' : '0') . "', '$monto', '$monto', '0')");
    $conexion->query("INSERT INTO nota (TIPO_DOC, ID_TIPO_DOC, tipOperacion, fecEmision, horEmision, codLocalEmisor, tipDocUsuario, numDocUsuario, rznSocialUsuario, tipMoneda, codTipoNota, descMotivo, tipDocModifica, serieDocModifica, sumTotTributos, sumTotValVenta, sumPrecioVenta, sumDescTotal, sumOtrosCargos, sumTotalAnticipos, sumImpVenta, ublVersionId, customizationId, estado_sunat) VALUES ('$tipoDoc', '$documentId', '0101', CURDATE(), CURTIME(), '0000', '" . $conexion->real_escape_string((string)($cabecera->tipDocUsuario ?? '1')) . "', '" . $conexion->real_escape_string((string)($cabecera->numDocUsuario ?? '')) . "', '" . $conexion->real_escape_string((string)($cabecera->rznSocialUsuario ?? '')) . "', 'PEN', '01', '$motivoSql', '03', '$docSql', '$igvMonto', '$base', '$monto', '0', '0', '0', '$monto', '2.1', '2.0', 'pendiente')");
    $conexion->query("INSERT INTO tri (TIPO_DOC, ID_TIPO_DOC, ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo) VALUES ('$tipoDoc', '$documentId', '$codTri', '$nomTri', 'VAT', '$base', '$igvMonto')");
    $conexion->query("INSERT INTO ley (TIPO_DOC, ID_TIPO_DOC, codLeyenda, desLeyenda) VALUES ('$tipoDoc', '$documentId', '1000', '" . $conexion->real_escape_string($motivo) . "')");

    $comprobanteSunat = [
        'tipo_doc' => '08',
        'serie' => $serie,
        'correlativo' => $comprobante,
        'fecha_emision' => date('Y-m-d'),
        'hora_emision' => date('H:i:s'),
        'moneda' => 'PEN',
        'total' => $monto,
        'subtotal_gravado' => $igvTipo === '10' ? $base : 0,
        'igv_total' => $igvMonto,
        'subtotal_exonerado' => $igvTipo === '20' ? $monto : 0,
        'subtotal_inafecto' => $igvTipo === '30' ? $monto : 0,
        'subtotal_gratuito' => 0,
        'documento_referencia' => $numeroReferencia,
        'nota_motivo' => '01',
        'nota_sustento' => $motivo
    ];
    $detallesSunat = [[
        'item' => 1,
        'codigo' => '0',
        'descripcion' => $motivo,
        'unidad_medida' => 'NIU',
        'cantidad' => 1,
        'precio_unitario' => $monto,
        'precio_con_igv' => $monto,
        'valor_venta' => $base,
        'igv_monto' => $igvMonto,
        'igv_tipo' => $igvTipo
    ]];

    $resultado = (new SunatService())->enviarNota($comprobanteSunat, $detallesSunat, $cliente);
    $estado = $conexion->real_escape_string($resultado['estado_sunat'] ?? 'pendiente');
    $codigo = $conexion->real_escape_string((string)($resultado['codigo'] ?? ''));
    $descripcion = $conexion->real_escape_string((string)($resultado['descripcion'] ?? ''));
    $hash = $conexion->real_escape_string((string)($resultado['codigo_hash'] ?? ''));
    $conexion->query("UPDATE nota SET estado_sunat='$estado', cdr_codigo='$codigo', cdr_descripcion='$descripcion', codigo_hash='$hash', fecha_envio_sunat=NOW() WHERE TIPO_DOC='08' AND ID_TIPO_DOC='$documentId'");

    echo json_encode(['status' => !empty($resultado['exito']) ? 'success' : 'pending', 'estado_sunat' => $resultado['estado_sunat'] ?? 'pendiente', 'message' => $resultado['descripcion'] ?? 'Nota de débito procesada.']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

