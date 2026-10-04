<?php
/**
 * send_sunat_ajax-action.php
 * Envía o reenvía un comprobante (Boleta, Factura o Nota de Crédito) directamente a SUNAT vía AJAX.
 */

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'descripcion' => 'No autorizado']);
    exit;
}

$numParam = isset($_POST['num']) ? trim($_POST['num']) : (isset($_GET['num']) ? trim($_GET['num']) : '');
$sell_id  = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

// ── MANEJO DE NOTA DE CRÉDITO ────────────────────────────────────────────────
if (!empty($numParam)) {
    $product = Factura2Data::getByNumDoc($numParam) ?: BoletaData::getByNumDoc($numParam);
    $comp_cab = null;
    if ($product) {
        $comp_cab = NotData::getById($product->id, 7);
    }
    if (!$comp_cab) {
        $comp_cab = NotData::getByIdComprobado($numParam);
    }

    if (!$comp_cab) {
        echo json_encode(['exito' => false, 'descripcion' => 'Nota de crédito no encontrada']);
        exit;
    }

    $detalles = DetData::getById($comp_cab->id, 7) ?: [];
    $empresa  = EmpresaData::getDatos();

    $fecParts   = explode(' ', $comp_cab->fecEmision ?? date('Y-m-d H:i:s'));
    $fecEmision = $fecParts[0] ?? date('Y-m-d');
    $horEmision = $comp_cab->horEmision ?? ($fecParts[1] ?? date('H:i:s'));

    $serieNota = $product->SERIE ?? ($comp_cab->NOTA_SERIE ?? '');
    $compNota  = $product->COMPROBANTE ?? ($comp_cab->NOTA_COMPROBANTE ?? '');

    $op_gravada   = 0.0;
    $op_exonerada = 0.0;
    $op_inafecta  = 0.0;
    $igv_total    = 0.0;

    $comp_tri = TriData::getById($comp_cab->id, 7);
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

    $comprobanteSunat = [
        'tipo_doc'             => '07',
        'serie'                => $serieNota,
        'correlativo'          => (int)$compNota,
        'fecha_emision'        => $fecEmision,
        'hora_emision'         => $horEmision,
        'moneda'               => $comp_cab->tipMoneda ?? 'PEN',
        'total'                => round($montoTotal, 2),
        'subtotal_gravado'     => round($op_gravada, 2),
        'igv_total'            => round($igv_total, 2),
        'subtotal_exonerado'   => round($op_exonerada, 2),
        'subtotal_inafecto'    => round($op_inafecta, 2),
        'subtotal_gratuito'    => 0.00,
        'documento_referencia' => $comp_cab->serieDocModifica ?? '',
        'nota_motivo'          => str_pad((string)($comp_cab->codTipoNota ?? '01'), 2, '0', STR_PAD_LEFT),
        'nota_sustento'        => $comp_cab->descMotivo ?? 'Anulación de la operación',
    ];

    $detallesSunat = [];
    $itemIndex = 1;
    foreach ($detalles as $det) {
        $q = floatval($det->ctdUnidadItem ?? 1);
        $pu = floatval($det->mtoValorUnitario ?? 0);
        $vv = floatval($det->mtoValorVentaItem ?? 0);
        $igvItem = floatval($det->mtoIgvItem ?? 0);

        $detallesSunat[] = [
            'item'            => $itemIndex++,
            'codigo'          => $det->codProducto ?? 'PROD',
            'descripcion'     => $det->desItem ?? 'PRODUCTO',
            'unidad_medida'   => $det->codUnidadMedida ?? 'NIU',
            'cantidad'        => $q,
            'precio_unitario' => $pu,
            'precio_con_igv'  => ($igvItem > 0 && $q > 0) ? round(($vv + $igvItem) / $q, 4) : $pu,
            'valor_venta'     => $vv,
            'igv_monto'       => $igvItem,
            'igv_tipo'        => ($igvItem > 0) ? '10' : '20'
        ];
    }

    $clienteSunat = [
        'tipo_doc'     => $comp_cab->tipDocUsuario ?: '1',
        'numero_doc'   => $comp_cab->numDocUsuario ?? '00000000',
        'razon_social' => $comp_cab->rznSocialUsuario ?? 'CLIENTE GENERAL',
        'direccion'    => '-',
    ];

    try {
        $sunatService = new SunatService();
        $resSunat = $sunatService->enviarNota($comprobanteSunat, $detallesSunat, $clienteSunat);
        $estadoSunat = $resSunat['estado_sunat'] ?? 'pendiente';
        $codigoSunat = addslashes((string)($resSunat['codigo'] ?? ''));
        $descripcionSunat = addslashes((string)($resSunat['descripcion'] ?? ''));
        $hashSunat = addslashes((string)($resSunat['codigo_hash'] ?? ''));

        $idNota = intval($comp_cab->id);
        Executor::doit("UPDATE nota SET estado_sunat='$estadoSunat', cdr_codigo='$codigoSunat', cdr_descripcion='$descripcionSunat', codigo_hash='$hashSunat', fecha_envio_sunat=NOW() WHERE id='$idNota'");

        echo json_encode([
            'exito'        => !empty($resSunat['exito']),
            'estado_sunat' => $estadoSunat,
            'codigo'       => $resSunat['codigo'] ?? '',
            'descripcion'  => $resSunat['descripcion'] ?? 'Procesado por SUNAT',
            'codigo_hash'  => $resSunat['codigo_hash'] ?? '',
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'exito'       => false,
            'descripcion' => 'Error al enviar a SUNAT: ' . $e->getMessage()
        ]);
    }
    exit;
}

// ── MANEJO DE VENTA (BOLETA O FACTURA) ───────────────────────────────────────
if ($sell_id <= 0) {
    echo json_encode(['exito' => false, 'descripcion' => 'ID de venta o número de comprobante inválido']);
    exit;
}

$sell = SellData::getById($sell_id);
if (!$sell) {
    echo json_encode(['exito' => false, 'descripcion' => 'Venta no encontrada']);
    exit;
}

$empresa = EmpresaData::getDatos();
$tipoDoc = ($sell->tipo_comprobante == 1) ? '01' : '03';

// Obtener cliente
$cliente = null;
if (!empty($sell->person_id)) {
    $cliente = PersonData::getById($sell->person_id);
}

// Obtener operaciones (detalles)
$operations = OperationData::getAllProductsBySellId($sell->id);
if (empty($operations)) {
    echo json_encode(['exito' => false, 'descripcion' => 'No se encontraron productos en la venta']);
    exit;
}

// Calcular subtotales por impuesto
$subtotal_gravado   = 0.00;
$igv_total          = 0.00;
$subtotal_exonerado = 0.00;
$subtotal_inafecto  = 0.00;
$subtotal_gratuito  = 0.00;
$total_general      = 0.00;

$detallesSunat = [];
$itemIndex = 1;

foreach ($operations as $ope) {
    $q               = round((float)$ope->q, 2);
    $precioUnitBruto = (float)$ope->prec_alt;
    $igvTipo         = $ope->igv_tipo ?? '20';
    $product         = ProductData::getById($ope->product_id);
    $desc            = !empty($ope->descripcion) ? $ope->descripcion : ($product ? $product->name : 'PRODUCTO');

    if ($igvTipo === '10') {
        $precioSinIgv = round($precioUnitBruto / 1.18, 4);
        $valorVenta   = round($q * ($precioUnitBruto / 1.18), 2);
        $totalLinea   = round($q * $precioUnitBruto, 2);
        $igvLinea     = round($totalLinea - $valorVenta, 2);

        $subtotal_gravado += $valorVenta;
        $igv_total        += $igvLinea;
        $total_general    += $totalLinea;

        $detallesSunat[] = [
            'item'            => $itemIndex++,
            'codigo'          => $ope->product_id,
            'descripcion'     => $desc,
            'unidad_medida'   => 'NIU',
            'cantidad'        => $q,
            'precio_unitario' => $precioSinIgv,
            'precio_con_igv'  => $precioUnitBruto,
            'valor_venta'     => $valorVenta,
            'igv_monto'       => $igvLinea,
            'igv_tipo'        => '10'
        ];
    } elseif ($igvTipo === '20') {
        $valorVenta   = round($q * $precioUnitBruto, 2);
        $subtotal_exonerado += $valorVenta;
        $total_general      += $valorVenta;

        $detallesSunat[] = [
            'item'            => $itemIndex++,
            'codigo'          => $ope->product_id,
            'descripcion'     => $desc,
            'unidad_medida'   => 'NIU',
            'cantidad'        => $q,
            'precio_unitario' => $precioUnitBruto,
            'precio_con_igv'  => $precioUnitBruto,
            'valor_venta'     => $valorVenta,
            'igv_monto'       => 0.00,
            'igv_tipo'        => '20'
        ];
    } elseif ($igvTipo === '30') {
        $valorVenta   = round($q * $precioUnitBruto, 2);
        $subtotal_inafecto += $valorVenta;
        $total_general     += $valorVenta;

        $detallesSunat[] = [
            'item'            => $itemIndex++,
            'codigo'          => $ope->product_id,
            'descripcion'     => $desc,
            'unidad_medida'   => 'NIU',
            'cantidad'        => $q,
            'precio_unitario' => $precioUnitBruto,
            'precio_con_igv'  => $precioUnitBruto,
            'valor_venta'     => $valorVenta,
            'igv_monto'       => 0.00,
            'igv_tipo'        => '30'
        ];
    } else {
        $valorReferencial = round($q * $precioUnitBruto, 2);
        $subtotal_gratuito += $valorReferencial;

        $detallesSunat[] = [
            'item'            => $itemIndex++,
            'codigo'          => $ope->product_id,
            'descripcion'     => $desc,
            'unidad_medida'   => 'NIU',
            'cantidad'        => $q,
            'precio_unitario' => 0.00,
            'precio_con_igv'  => $precioUnitBruto,
            'valor_venta'     => $valorReferencial,
            'igv_monto'       => 0.00,
            'igv_tipo'        => $igvTipo
        ];
    }
}

$totalFinal = round($total_general - (float)$sell->discount, 2);
if ($totalFinal < 0) $totalFinal = 0.00;

// Determinar tipo documento cliente
$numDoc = $cliente ? $cliente->numero_documento : '00000000';
$rzn    = $cliente ? trim($cliente->name . ' ' . $cliente->lastname) : 'CLIENTES VARIOS';
$dir    = ($cliente && !empty($cliente->address1)) ? $cliente->address1 : '-';

if ($tipoDoc === '01') {
    $sunatTipDoc = '6';
} elseif (strlen($numDoc) === 8) {
    $sunatTipDoc = '1';
} elseif (strlen($numDoc) === 11) {
    $sunatTipDoc = '6';
} else {
    $sunatTipDoc = '0';
}

$fecParts = explode(' ', $sell->created_at);
$fecEmision = $fecParts[0];
$horEmision = $fecParts[1] ?? date('H:i:s');

$formaPago = (int)($sell->forma_pago ?? 1);
$fecVenc   = (!empty($sell->fec_vencimiento) && $sell->fec_vencimiento != '-') ? $sell->fec_vencimiento : date('Y-m-d', strtotime('+30 days'));

$comprobanteSunat = [
    'tipo_doc'           => $tipoDoc,
    'serie'              => $sell->serie,
    'correlativo'        => (int)$sell->comprobante,
    'fecha_emision'      => $fecEmision,
    'hora_emision'       => $horEmision,
    'moneda'             => 'PEN',
    'condicion_pago'     => ($formaPago === 2) ? 'CREDITO' : 'CONTADO',
    'total'              => $totalFinal,
    'subtotal_gravado'   => $subtotal_gravado,
    'igv_total'          => $igv_total,
    'subtotal_exonerado' => $subtotal_exonerado,
    'subtotal_inafecto'  => $subtotal_inafecto,
    'subtotal_gratuito'  => $subtotal_gratuito,
];

if ($formaPago === 2) {
    $comprobanteSunat['cuotas'] = [
        [
            'monto'      => $totalFinal,
            'fecha_pago' => $fecVenc,
        ]
    ];
}

$clienteSunat = [
    'tipo_doc'     => $sunatTipDoc,
    'numero_doc'   => $numDoc,
    'razon_social' => $rzn,
    'direccion'    => $dir,
];

try {
    $sunatService = new SunatService();
    $resSunat = $sunatService->enviarComprobante($comprobanteSunat, $detallesSunat, $clienteSunat);

    SellData::updateSunat($sell->id, [
        'estado_sunat'      => $resSunat['estado_sunat'] ?? 'pendiente',
        'cdr_codigo'        => $resSunat['codigo'] ?? null,
        'cdr_descripcion'   => $resSunat['descripcion'] ?? null,
        'codigo_hash'       => $resSunat['codigo_hash'] ?? null,
        'fecha_envio_sunat' => date('Y-m-d H:i:s'),
    ]);

    echo json_encode([
        'exito'        => $resSunat['exito'],
        'estado_sunat' => $resSunat['estado_sunat'],
        'codigo'       => $resSunat['codigo'],
        'descripcion'  => $resSunat['descripcion'],
        'codigo_hash'  => $resSunat['codigo_hash'] ?? '',
    ]);

} catch (Exception $e) {
    echo json_encode([
        'exito'       => false,
        'descripcion' => 'Error: ' . $e->getMessage()
    ]);
}
exit;


