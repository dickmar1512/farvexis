<?php
/**
 * addnotacredito-view.php
 * Genera la Nota de Crédito (07), revierte inventario si aplica,
 * guarda en base de datos y envía directamente a SUNAT mediante SunatService.
 */

$conexion = Database::getCon();

if (count($_POST) > 0) {

    $TIPO        = '07';
    $TIPO_NOTA   = $_POST["tipo"] ?? "01";
    $MOTIVO      = $_POST["motivo"] ?? "Anulación de la operación";
    $SERIE       = $_POST["serie"] ?? "FC01";
    $COMPROBANTE = $_POST["comp"] ?? "1";
    $NUM         = $_POST["numDoc"] ?? "";
    $RUC         = '';
    $SELLID      = 0;

    // ─────────────────────────────────────────────────────────────
    // COMPROBAR DUPLICADO DE NOTA DE CRÉDITO
    // ─────────────────────────────────────────────────────────────
    $existeNota = NotData::getByIdComprobado($NUM);
    if ($existeNota && $existeNota->id > 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => 'Ya existe una nota generada para el comprobante ' . $NUM . '. No se puede duplicar.'
        ]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────
    // OBTENER DATOS DEL COMPROBANTE ORIGINAL
    // ─────────────────────────────────────────────────────────────
    if ($TIPO_NOTA == '01' || $TIPO_NOTA == '02' || $TIPO_NOTA == '03' ||
        $TIPO_NOTA == '04' || $TIPO_NOTA == '05' || $TIPO_NOTA == '06' ||
        $TIPO_NOTA == '07')
    {
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
        $RUC     = $product->RUC ?: (EmpresaData::getDatos()->Emp_Ruc ?? '');
        $SELLID  = $product->EXTRA1;
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

        if (!$comp_cab && $sellObj) {
            $tipDocUsuario = $personObj ? ($personObj->kind == 1 ? '1' : '6') : '6';
            $numDocUsuario = $personObj ? $personObj->pin : '00000000';
            $rznSocialUsuario = $personObj ? trim($personObj->name . ' ' . $personObj->lastname) : 'CLIENTE GENERAL';
        }

        // ── CABECERA ──
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
        $sumTotTributos    = $comp_cab->sumTotTributos ?? 0;
        $sumTotValVenta    = $comp_cab->sumTotValVenta ?? 0;
        $sumPrecioVenta    = $comp_cab->sumPrecioVenta ?? 0;
        $sumDescTotal      = $comp_cab->sumDescTotal ?? 0;
        $sumOtrosCargos    = $comp_cab->sumOtrosCargos ?? 0;
        $sumTotalAnticipos = $comp_cab->sumTotalAnticipos ?? 0;
        $sumImpVenta       = $comp_cab->sumImpVenta ?? 0;
        $ublVersionId      = '2.1';
        $customizationId   = '2.0';

        // ── DETALLE (primer ítem como referencia de tributos) ──
        $codUnidadMedida           = $detalles[0]->codUnidadMedida ?? 'NIU';
        $codProducto               = $detalles[0]->codProducto ?? '0';
        $codProductoSUNAT          = $detalles[0]->codProductoSUNAT ?? '-';
        $codTriIGV                 = $detalles[0]->codTriIGV ?? '9997';
        $mtoIgvItem                = $detalles[0]->mtoIgvItem ?? 0;
        $mtoBaseIgvItem            = $detalles[0]->mtoBaseIgvItem ?? 0;
        $nomTributoIgvItem         = $detalles[0]->nomTributoIgvItem ?? 'EXO';
        $codTipTributoIgvItem      = $detalles[0]->codTipTributoIgvItem ?? 'VAT';
        $tipAfeIGV                 = $detalles[0]->tipAfeIGV ?? '20';
        $porIgvItem                = $detalles[0]->porIgvItem ?? 0;
        $codTriISC                 = "-";
        $mtoIscItem                = 0;
        $mtoBaseIscItem            = 0;
        $nomTributoIscItem         = "";
        $codTipTributoIscItem      = "";
        $tipSisISC                 = "";
        $porIscItem                = "";
        $codTriOtroItem            = "-";
        $sumTotTributosItem        = $mtoIgvItem;
        $mtoTriOtroItem            = 0;
        $mtoBaseTriOtroItem        = 0;
        $nomTributoIOtroItem       = '';
        $codTipTributoIOtroItem    = '';
        $porTriOtroItem            = '';
        $mtoValorReferencialUnitario = 0;

        // ── TRIBUTOS ──
        $ideTributo       = $comp_tri->ideTributo ?? '9997';
        $nomTributo       = $comp_tri->nomTributo ?? 'EXO';
        $codTipTributo    = $comp_tri->codTipTributo ?? 'VAT';
        $mtoBaseImponible = $comp_tri->mtoBaseImponible ?? 0;
        $mtoTributo       = $comp_tri->mtoTributo ?? 0;

        // ── LEYENDA ──
        $codLeyenda = $comp_ley->codLeyenda ?? '1000';
        $desLeyenda = $comp_ley->desLeyenda ?? '';

    } else {
        // Ruta manual (POST completo)
        $tipOperacion      = $_POST["tipOperacion"] ?? "0101";
        $fecEmision        = $_POST["fecEmision"] ?? date("Y-m-d");
        $horEmision        = $_POST["horEmision"] ?? date("H:i:s");
        $fecVencimiento    = $_POST["fecVencimiento"] ?? "-";
        $codLocalEmisor    = $_POST["codLocalEmisor"] ?? "0000";
        $tipDocUsuario     = $_POST["tipDocUsuario"] ?? "6";
        $numDocUsuario     = $_POST["numDocUsuario"] ?? "";
        $rznSocialUsuario  = $_POST["rznSocialUsuario"] ?? "";
        $tipMoneda         = $_POST["tipMoneda"] ?? "PEN";
        $codTipoNota       = $_POST["codTipoNota"] ?? $TIPO_NOTA;
        $descMotivo        = $_POST["descMotivo"] ?? $MOTIVO;
        $tipDocModifica    = $_POST["tipDocModifica"] ?? "01";
        $serieDocModifica  = $_POST["serieDocModifica"] ?? $NUM;
        $sumTotTributos    = $_POST["sumTotTributos"] ?? 0;
        $sumTotValVenta    = 0;
        $sumPrecioVenta    = 0;

        $codUnidadMedida        = $_POST["codUnidadMedida"] ?? "NIU";
        $codProducto            = $_POST["codProducto"] ?? "0";
        $codProductoSUNAT       = $_POST["codProductoSUNAT"] ?? "-";
        $codTriIGV              = $_POST["codTriIGV"] ?? "9997";
        $mtoIgvItem             = $_POST["mtoIgvItem"] ?? 0;
        $mtoBaseIgvItem         = 0;
        $nomTributoIgvItem      = $_POST["nomTributoIgvItem"] ?? "EXO";
        $codTipTributoIgvItem   = $_POST["codTipTributoIgvItem"] ?? "VAT";
        $tipAfeIGV              = $_POST["tipAfeIGV"] ?? "20";
        $porIgvItem             = $_POST["porIgvItem"] ?? 0;
        $codTriISC              = "-";
        $mtoIscItem             = 0;
        $mtoBaseIscItem         = 0;
        $nomTributoIscItem      = "";
        $codTipTributoIscItem   = "";
        $tipSisISC              = "";
        $porIscItem             = "";
        $codTriOtroItem         = "-";
        $sumTotTributosItem     = 0;
        $mtoTriOtroItem         = '';
        $mtoBaseTriOtroItem     = '';
        $nomTributoIOtroItem    = '';
        $codTipTributoIOtroItem = '';
        $porTriOtroItem         = '';
        $mtoValorReferencialUnitario = 0;

        $ideTributo       = $_POST["ideTributo"] ?? "9997";
        $nomTributo       = $_POST["nomTributo"] ?? "EXO";
        $codTipTributo    = $_POST["codTipTributo"] ?? "VAT";
        $mtoBaseImponible = 0;
        $mtoTributo       = 0;

        $codLeyenda = "1000";
        $desLeyenda = "";
    }

    $sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
    $COMPROBANTE = ComprobanteCorrelativoData::siguiente($conexion, $TIPO, $SERIE, $codLocalEmisor, $sucursalId);

    // INSERTAR REGISTRO EN TABLA factura Y OBTENER ID
    $sql_DOC = "INSERT INTO factura (RUC, TIPO, SERIE, COMPROBANTE, EXTRA1)
                VALUES ('$RUC', '$TIPO', '$SERIE', '$COMPROBANTE', '$SELLID')";

    $conexion->query($sql_DOC);
    $id_factura_impresa = $conexion->insert_id;
    $TIPO_DOC           = $TIPO;
    $ID_TIPO_DOC        = $id_factura_impresa;

    $operations = OperationData::getAllProductsBySellId($SELLID);

    if (empty($operations) && !empty($detalles)) {
        $operations = [];
        foreach ($detalles as $det) {
            $operations[] = new class($det) {
                private $cloneDet;
                public $q;
                public $prec_alt;
                public $igv_tipo;
                public function __construct($det) {
                    $this->cloneDet = $det;
                    $this->q = $det->ctdUnidadItem;
                    $this->prec_alt = $det->mtoValorUnitario;
                    $this->igv_tipo = $det->tipAfeIGV ?? '20';
                }
                public function getProduct() {
                    return new class($this->cloneDet) {
                        public $name;
                        public $is_stock = 0;
                        public $id = 0;
                        public function __construct($det) {
                            $this->name = $det->desItem;
                        }
                    };
                }
            };
        }
    }

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

    // HELPER: devuelve stock
    $devolverStock = function ($product, $cantidad) use ($SELLID) {
        if ($product && $product->is_stock != 0) {
            $p_sumar        = new ProductData();
            $p_sumar->id    = $product->id;
            $p_sumar->stock = $product->stock + $cantidad;
            $p_sumar->update_stock();

            $op                    = new OperationData();
            $op->product_id        = $product->id;
            $op->operation_type_id = 1;
            $op->sell_id           = $SELLID;
            $op->q                 = $cantidad;
            $op->add();
        }
    };

    // PROCESAMIENTO POR TIPO DE NOTA
    if ($TIPO_NOTA == '01' || $TIPO_NOTA == '02' || $TIPO_NOTA == '06') {
        $sumTotValVenta = 0;
        $sumPrecioVenta = 0;

        foreach ($operations as $item) {
            $product  = $item->getProduct();
            $cantidad = ($product && $product->is_stock == 0) ? 1 : $item->q;
            $precio_unitario      = $item->prec_alt;
            $descripcion_producto = $product ? $product->name : 'PRODUCTO';

            $devolverStock($product, $cantidad);

            $mtoValorVentaItem      = round($cantidad * $precio_unitario, 2);
            $mtoPrecioVentaUnitario = $precio_unitario;
            $mtoBaseIgvItem         = $mtoValorVentaItem;

            $insertarDetalle(
                $cantidad, $precio_unitario, $descripcion_producto,
                $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem, $item->igv_tipo ?? '20'
            );

            $sumTotValVenta += $mtoValorVentaItem;
            $sumPrecioVenta  = $sumTotValVenta + $sumTotTributos;
        }

    } elseif ($TIPO_NOTA == '03') {
        $data           = $_POST['arraydet'] ?? [];
        $sumTotValVenta = 0;
        $sumPrecioVenta = 0;

        foreach ($operations as $item) {
            $product = $item->getProduct();
            for ($i = 0; $i < count($data); $i++) {
                if ($product && $data[$i][0] == $product->name) {
                    $cantidad             = $item->q;
                    $precio_unitario      = $item->prec_alt;
                    $descripcion_producto = $data[$i][1];

                    $devolverStock($product, $cantidad);

                    $mtoValorVentaItem      = round($cantidad * $precio_unitario, 2);
                    $mtoPrecioVentaUnitario = $precio_unitario;
                    $mtoBaseIgvItem         = $mtoValorVentaItem;

                    $insertarDetalle(
                        $cantidad, $precio_unitario, $descripcion_producto,
                        $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem, $item->igv_tipo ?? '20'
                    );

                    $sumTotValVenta += $mtoValorVentaItem;
                    $sumPrecioVenta  = $sumTotValVenta + $sumTotTributos;
                }
            }
        }

    } elseif ($TIPO_NOTA == '04') {
        $cantidad             = 1;
        $precio_unitario      = floatval($_POST['dscto'] ?? 0);
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

    } elseif ($TIPO_NOTA == '05') {
        $data           = $_POST['arraydet'] ?? [];
        $sumTotValVenta = 0;
        $sumPrecioVenta = 0;

        foreach ($operations as $item) {
            $product = $item->getProduct();
            for ($i = 0; $i < count($data); $i++) {
                if ($product && $data[$i][0] == $product->name) {
                    $cantidad             = $item->q;
                    $precio_unitario      = floatval($data[$i][1]);
                    $descripcion_producto = $data[$i][0];
                    $mtoValorVentaItem    = $precio_unitario;
                    $mtoPrecioVentaUnitario = $precio_unitario;
                    $mtoBaseIgvItem       = $mtoValorVentaItem;

                    $insertarDetalle(
                        $cantidad, $precio_unitario, $descripcion_producto,
                        $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem, $item->igv_tipo ?? '20'
                    );

                    $sumTotValVenta += round($cantidad * $precio_unitario, 2);
                    $sumPrecioVenta  = $sumTotValVenta + $sumTotTributos;
                }
            }
        }

    } elseif ($TIPO_NOTA == '07') {
        $data           = $_POST['arraydet'] ?? [];
        $sumTotValVenta = 0;
        $sumPrecioVenta = 0;

        foreach ($operations as $item) {
            $product = $item->getProduct();
            for ($i = 0; $i < count($data); $i++) {
                if ($product && $data[$i][0] == $product->name) {
                    $cantidad             = floatval($data[$i][1]);
                    $precio_unitario      = $item->prec_alt;
                    $descripcion_producto = $data[$i][0];

                    $devolverStock($product, $cantidad);

                    $mtoValorVentaItem      = round($cantidad * $precio_unitario, 2);
                    $mtoPrecioVentaUnitario = $precio_unitario;
                    $mtoBaseIgvItem         = $mtoValorVentaItem;

                    $insertarDetalle(
                        $cantidad, $precio_unitario, $descripcion_producto,
                        $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem, $item->igv_tipo ?? '20'
                    );

                    $sumTotValVenta += $mtoValorVentaItem;
                    $sumPrecioVenta  = $sumTotValVenta + $sumTotTributos;
                }
            }
        }
    }

    $numLetra   = NumeroLetras::convertir(number_format($sumPrecioVenta, 2, '.', ''));
    $desLeyenda = $numLetra;

    // INSERT tabla nota (CAB)
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

    // INSERT tabla tri
    $sql_TRI = "INSERT INTO tri (
        TIPO_DOC, ID_TIPO_DOC,
        ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo
    ) VALUES (
        '$TIPO_DOC', '$ID_TIPO_DOC',
        '$ideTributo', '$nomTributo', '$codTipTributo',
        '$mtoBaseImponible', '$mtoTributo'
    )";
    $conexion->query($sql_TRI);

    // INSERT tabla ley
    $sql_LEY = "INSERT INTO ley (
        TIPO_DOC, ID_TIPO_DOC, codLeyenda, desLeyenda
    ) VALUES (
        '$TIPO_DOC', '$ID_TIPO_DOC', '$codLeyenda', '" . $conexion->real_escape_string($desLeyenda) . "'
    )";
    $conexion->query($sql_LEY);

    $envio_sunat = $_POST['envio_sunat'] ?? $_POST['envio_sunatb'] ?? 'automatico';
    if ($envio_sunat === 'manual') {
        $conexion->query("UPDATE nota SET estado_sunat='pendiente' WHERE TIPO_DOC='07' AND ID_TIPO_DOC='$ID_TIPO_DOC'");
        $resSunat = [
            'exito' => false,
            'estado_sunat' => 'pendiente',
            'descripcion' => 'Nota de crédito registrada en modo Manual. Pendiente de envío a SUNAT.'
        ];
    } else {
        // ENVÍO DIRECTO A SUNAT (Modo Automático)
        $comprobanteSunat = [
            'tipo_doc'             => '07',
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
            'nota_motivo'          => str_pad((string)$codTipoNota, 2, '0', STR_PAD_LEFT),
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
            $conexion->query("UPDATE nota SET estado_sunat='$estadoSunat', cdr_codigo='$codigoSunat', cdr_descripcion='$descripcionSunat', codigo_hash='$hashSunat', fecha_envio_sunat=NOW() WHERE TIPO_DOC='07' AND ID_TIPO_DOC='$ID_TIPO_DOC'");
        } catch (Exception $e) {
            error_log("Error al enviar Nota de Crédito: " . $e->getMessage());
            $resSunat = ['exito' => false, 'estado_sunat' => 'pendiente', 'descripcion' => $e->getMessage()];
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => !empty($resSunat['exito']) ? 'success' : 'pending',
        'estado_sunat' => $resSunat['estado_sunat'] ?? 'pendiente',
        'message' => $resSunat['descripcion'] ?? 'Nota de crédito procesada.',
        'redirect' => BASE_URL . '/notacredito/' . rawurlencode($SERIE . '-' . $COMPROBANTE)
    ]);
    return true;
}
?>

