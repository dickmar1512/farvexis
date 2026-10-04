<?php
/**
 * addboleta-action.php
 * Genera la venta tipo Boleta de Venta (03), guarda operaciones con igv_tipo,
 * registra en tablas legacy vía Database::getCon(), envía directamente a SUNAT
 * y responde en formato JSON para consumo vía AJAX.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    $conexion = Database::getCon();
    if (!$conexion) {
        throw new Exception("No se pudo conectar con la base de datos.");
    }

    if (count($_POST) === 0) {
        throw new Exception("No se recibieron datos del formulario.");
    }

    if (!isset($_SESSION["cart"]) || count($_SESSION["cart"]) == 0) {
        throw new Exception("El carrito de compras está vacío.");
    }

    $cart = $_SESSION["cart"];

    foreach ($cart as $cartItem) {
        $cartProduct = ProductData::getById($cartItem["product_id"]);
        if ($cartProduct && (int)$cartProduct->is_stock === 1 &&
            !LoteData::canAllocate((int)$cartItem["product_id"], (float)$cartItem["q"])) {
            throw new Exception("No hay suficiente stock disponible en lotes para el producto: " . $cartProduct->name);
        }
    }

    // Datos del comprobante
    $RUC              = $_POST["RUC"] ?? "";
    $TIPO             = "03";
    $SERIE            = $_POST["SERIE"] ?? "B001";
    $COMPROBANTE      = $_POST["COMPROBANTE"] ?? "1";
    $tipOperacion     = $_POST["tipOperacion"] ?? "0101";
    $fecEmision       = $_POST["fecEmision"] ?? date("Y-m-d");
    $horEmision       = $_POST["horEmision"] ?? date("H:i:s");
    $formaPago        = isset($_POST["formaPago"]) ? (int)$_POST["formaPago"] : 1; // 1: Contado, 2: Crédito
    $fecVencimiento   = (!empty($_POST["fecVencimiento"]) && $_POST["fecVencimiento"] !== '-') ? $_POST["fecVencimiento"] : "-";
    $codLocalEmisor   = $_POST["codLocalEmisor"] ?? "0000";
    $tipDocUsuario    = $_POST["tipDocUsuario"] ?? "1";
    $numDocUsuario    = trim($_POST["numDocUsuario"] ?? "");
    $rznSocialUsuario = trim($_POST["rznSocialUsuario"] ?? "Cliente General");
    $codUbigeoCliente = $_POST['codUbigeoCliente'] ?? "000000";
    $desDireccionCliente = trim($_POST['desDireccionCliente'] ?? "");
    $tipMoneda        = $_POST["tipMoneda"] ?? "PEN";

    $sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
    $COMPROBANTE = ComprobanteCorrelativoData::siguiente($conexion, $TIPO, $SERIE, $codLocalEmisor, $sucursalId);

    $discount         = floatval($_POST["discount"] ?? 0);
    $cash             = floatval($_POST["money"] ?? 0);
    $selTipoPago      = $_POST["selTipoPago"] ?? 1;
    $pagoParcial      = floatval($_POST["pagoParcial"] ?? 0);
    $envioSunat       = $_POST["envio_sunat"] ?? "manual";

    // Determinar tipo documento cliente para SUNAT
    $sunatTipDoc = "1"; // DNI por defecto
    if ($numDocUsuario === "" || $numDocUsuario === "00000000") {
        $sunatTipDoc = "0"; // Sin documento / Varios
        $numDocUsuario = "00000000";
    } elseif (strlen($numDocUsuario) === 11) {
        $sunatTipDoc = "6"; // RUC
    } elseif (strlen($numDocUsuario) === 8) {
        $sunatTipDoc = "1"; // DNI
    } else {
        $sunatTipDoc = "4"; // Carnet Extranjeria u otro
    }

    // 1. Gestión del cliente en base de datos
    $person_id = null;
    if (trim($numDocUsuario) != '' && $numDocUsuario != '00000000') {
        $a = PersonData::verificar_persona($numDocUsuario, 1);
        if (is_null($a)) {
            $person = new PersonData();
            $person->tipo_persona = 3;
            $person->numero_documento = $numDocUsuario;
            $person->name = $rznSocialUsuario;
            $person->lastname = "";
            $person->address1 = $desDireccionCliente;
            $person->ubigeo = $codUbigeoCliente;
            $person->email1 = "";
            $per = $person->add_client();
            $person_id = (int)$per[1];
        } else {
            $person_id = (int)$a->id;
        }
    }

    // 2. Procesar ítems del carrito y calcular impuestos SUNAT
    $subtotal_gravado   = 0.00;
    $igv_total          = 0.00;
    $subtotal_exonerado = 0.00;
    $subtotal_inafecto  = 0.00;
    $subtotal_gratuito  = 0.00;
    $total_general      = 0.00;

    $detallesSunat      = [];
    $itemIndex          = 1;

    foreach ($cart as $c) {
        $q               = round((float)$c["q"], 2);
        $precioUnitBruto = (float)$c["precio_unitario"]; // Precio final al público
        $igvTipo         = $c["igv_tipo"] ?? '20';
        $productSUnat    = ProductData::getById($c["product_id"]);
        $productName     = $productSUnat->name ?? '';

        if ($igvTipo === '10') {
            // Gravado (18% IGV incluido en precio_unitario)
            $precioSinIgv = round($precioUnitBruto / 1.18, 4);
            $valorVenta   = round($q * ($precioUnitBruto / 1.18), 2);
            $totalLinea   = round($q * $precioUnitBruto, 2);
            $igvLinea     = round($totalLinea - $valorVenta, 2);

            $subtotal_gravado += $valorVenta;
            $igv_total        += $igvLinea;
            $total_general    += $totalLinea;

            $detallesSunat[] = [
                'item'            => $itemIndex++,
                'codigo'          => $c["product_id"],
                'descripcion'     => $productName ?: 'PRODUCTO',
                'unidad_medida'   => 'NIU',
                'cantidad'        => $q,
                'precio_unitario' => $precioSinIgv,
                'precio_con_igv'  => $precioUnitBruto,
                'valor_venta'     => $valorVenta,
                'igv_monto'       => $igvLinea,
                'igv_tipo'        => '10'
            ];
        } elseif ($igvTipo === '20') {
            // Exonerado (IGV = 0)
            $valorVenta   = round($q * $precioUnitBruto, 2);
            $totalLinea   = $valorVenta;

            $subtotal_exonerado += $valorVenta;
            $total_general      += $totalLinea;

            $detallesSunat[] = [
                'item'            => $itemIndex++,
                'codigo'          => $c["product_id"],
                'descripcion'     => $productName ?: 'PRODUCTO',
                'unidad_medida'   => 'NIU',
                'cantidad'        => $q,
                'precio_unitario' => $precioUnitBruto,
                'precio_con_igv'  => $precioUnitBruto,
                'valor_venta'     => $valorVenta,
                'igv_monto'       => 0.00,
                'igv_tipo'        => '20'
            ];
        } elseif ($igvTipo === '30') {
            // Inafecto (IGV = 0)
            $valorVenta   = round($q * $precioUnitBruto, 2);
            $totalLinea   = $valorVenta;

            $subtotal_inafecto += $valorVenta;
            $total_general     += $totalLinea;

            $detallesSunat[] = [
                'item'            => $itemIndex++,
                'codigo'          => $c["product_id"],
                'descripcion'     => $productName ?: 'PRODUCTO',
                'unidad_medida'   => 'NIU',
                'cantidad'        => $q,
                'precio_unitario' => $precioUnitBruto,
                'precio_con_igv'  => $precioUnitBruto,
                'valor_venta'     => $valorVenta,
                'igv_monto'       => 0.00,
                'igv_tipo'        => '30'
            ];
        } else {
            // Gratuito (11, 21, etc.)
            $valorReferencial = round($q * $precioUnitBruto, 2);
            $subtotal_gratuito += $valorReferencial;

            $detallesSunat[] = [
                'item'            => $itemIndex++,
                'codigo'          => $c["product_id"],
                'descripcion'     => $productName ?: 'PRODUCTO',
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

    $totalFinal = round($total_general - $discount, 2);
    if ($totalFinal < 0) $totalFinal = 0.00;

    // 3. Crear registro en SellData
    $sell = new SellData();
    $sell->user_id          = $_SESSION["user_id"];
    $sell->tipo_comprobante = 3; // Boleta
    $sell->serie            = $SERIE;
    $sell->comprobante      = $COMPROBANTE;
    $sell->total            = $totalFinal;
    $sell->discount         = $discount;
    $sell->cash             = $cash;
    $sell->tipo_pago        = $selTipoPago;
    $sell->forma_pago       = $formaPago;
    $sell->fec_vencimiento  = ($formaPago == 2 && $fecVencimiento !== '-') ? $fecVencimiento : null;
    $sell->person_id        = $person_id;
    $sell->created_at       = $fecEmision . ' ' . $horEmision;

    $s = $sell->add2();
    $sell_id = $s[1];

    // Pago parcial si aplica
    if ($pagoParcial > 0) {
        $pagpar = new SellData();
        $pagpar->id = $sell_id;
        $pagpar->importepp = $pagoParcial;
        $pagpar->addPagoParcial();
    }

    // 4. Guardar operaciones (ítems vendidos con su tipo de igv)
    foreach ($cart as $c) {
        $op = new OperationData();
        $op->product_id        = $c["product_id"];
        $product               = ProductData::getById($c["product_id"]);
        $op->operation_type_id = OperationTypeData::getByName("salida")->id;
        $op->sell_id           = $sell_id;
        $op->descripcion       = $c["descripcion"] ?? '';
        $op->cu                = $product->price_in ?? 0;
        $op->prec_alt          = $c["precio_unitario"];
        $op->descuento         = $c["descuento"] ?? 0;
        $op->idpaquete         = $c["idpaquete"] ?? "X";
        $op->q                 = round((float)$c["q"], 2);
        $op->igv_tipo          = $c["igv_tipo"] ?? '20';
        $op->created_at        = date('Y-m-d H:i:s');

        if (isset($_POST["is_oficial"])) {
            $op->is_oficial = 1;
        }

        $operationResult = $op->add();
        if (!$operationResult[0]) {
            throw new Exception("No se pudo registrar un producto de la boleta.");
        }
        if ($product && (int)$product->is_stock === 1) {
            LoteData::allocateForOperation(
                (int)$operationResult[1],
                (int)$c["product_id"],
                (float)$op->q,
                (int)$_SESSION["user_id"]
            );
        }
    }

    // 5. Guardar en tablas legacy (boleta, det, cab, tri, aca, ley) para compatibilidad interna
    $sql_DOC = "INSERT INTO boleta (RUC, TIPO, SERIE, COMPROBANTE, EXTRA1) VALUES ('$RUC', '$TIPO', '$SERIE', '$COMPROBANTE', '$sell_id')";
    $conexion->query($sql_DOC);
    $id_boleta_impresa = $conexion->insert_id;

    $sumTotValVenta = round($subtotal_gravado + $subtotal_exonerado + $subtotal_inafecto, 2);
    $sumTotTributos = round($igv_total, 2);
    $sumPrecioVenta = round($total_general, 2);
    $sumImpVenta    = round($totalFinal, 2);

    foreach ($detallesSunat as $det) {
        $codUnidad = $det['unidad_medida'];
        $cant      = $det['cantidad'];
        $codP      = $det['codigo'];
        $descP     = $conexion->real_escape_string($det['descripcion']);
        $pUnit     = $det['precio_unitario'];
        $pVenta    = $det['valor_venta'];
        $pConIgv   = $det['precio_con_igv'];
        $mIgv      = $det['igv_monto'];
        $afeIgv    = $det['igv_tipo'];

        $nomTri = ($afeIgv === '10') ? 'IGV' : (($afeIgv === '20') ? 'EXO' : (($afeIgv === '30') ? 'INA' : 'GRA'));
        $codTri = ($afeIgv === '10') ? '1000' : (($afeIgv === '20') ? '9997' : (($afeIgv === '30') ? '9998' : '9996'));
        $porIgv = ($afeIgv === '10') ? 18 : 0;

        $sql_DET = "INSERT INTO det (
            TIPO_DOC, ID_TIPO_DOC, codUnidadMedida, ctdUnidadItem, codProducto, codProductoSUNAT,
            desItem, mtoValorUnitario, sumTotTributosItem, codTriIGV, mtoIgvItem, mtoBaseIgvItem,
            nomTributoIgvItem, codTipTributoIgvItem, tipAfeIGV, porIgvItem, mtoPrecioVentaUnitario,
            mtoValorVentaItem, mtoValorReferencialUnitario
        ) VALUES (
            '$TIPO', '$id_boleta_impresa', '$codUnidad', '$cant', '$codP', '-',
            '$descP', '$pUnit', '$mIgv', '$codTri', '$mIgv', '$pVenta',
            '$nomTri', 'VAT', '$afeIgv', '$porIgv', '$pConIgv',
            '$pVenta', '0.00'
        )";
        $conexion->query($sql_DET);
    }

    $numLetra = NumeroLetras::convertir(number_format($sumImpVenta, 2, '.', ''));
    $desLeyenda = $numLetra;

    $sql_CAB = "INSERT INTO cab (
        TIPO_DOC, ID_TIPO_DOC, tipOperacion, fecEmision, horEmision, fecVencimiento,
        codLocalEmisor, tipDocUsuario, numDocUsuario, rznSocialUsuario, tipMoneda,
        sumTotTributos, sumTotValVenta, sumPrecioVenta, sumDescTotal, sumOtrosCargos,
        sumTotalAnticipos, sumImpVenta, ublVersionId, customizationId
    ) VALUES (
        '$TIPO', '$id_boleta_impresa', '$tipOperacion', '$fecEmision', '$horEmision', '$fecVencimiento',
        '$codLocalEmisor', '$sunatTipDoc', '$numDocUsuario', '" . $conexion->real_escape_string($rznSocialUsuario) . "', '$tipMoneda',
        '$sumTotTributos', '$sumTotValVenta', '$sumPrecioVenta', '$discount', '0.00',
        '0.00', '$sumImpVenta', '2.1', '2.0'
    )";
    $conexion->query($sql_CAB);

    // Registro TRI (Tributos cabecera)
    if ($subtotal_gravado > 0) {
        $conexion->query("INSERT INTO tri (TIPO_DOC, ID_TIPO_DOC, ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo)
            VALUES ('$TIPO', '$id_boleta_impresa', '1000', 'IGV', 'VAT', '$subtotal_gravado', '$igv_total')");
    }
    if ($subtotal_exonerado > 0) {
        $conexion->query("INSERT INTO tri (TIPO_DOC, ID_TIPO_DOC, ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo)
            VALUES ('$TIPO', '$id_boleta_impresa', '9997', 'EXO', 'VAT', '$subtotal_exonerado', '0.00')");
    }
    if ($subtotal_inafecto > 0) {
        $conexion->query("INSERT INTO tri (TIPO_DOC, ID_TIPO_DOC, ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo)
            VALUES ('$TIPO', '$id_boleta_impresa', '9998', 'INA', 'FRE', '$subtotal_inafecto', '0.00')");
    }
    if ($subtotal_gratuito > 0) {
        $conexion->query("INSERT INTO tri (TIPO_DOC, ID_TIPO_DOC, ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo)
            VALUES ('$TIPO', '$id_boleta_impresa', '9996', 'GRA', 'FRE', '$subtotal_gratuito', '0.00')");
    }

    // Registro ACA (Datos cliente / dirección)
    $conexion->query("INSERT INTO aca (TIPO_DOC, ID_TIPO_DOC, codPaisCliente, codUbigeoCliente, desDireccionCliente)
        VALUES ('$TIPO', '$id_boleta_impresa', 'PE', '$codUbigeoCliente', '" . $conexion->real_escape_string($desDireccionCliente) . "')");

    // Registro LEY (Leyenda)
    $conexion->query("INSERT INTO ley (TIPO_DOC, ID_TIPO_DOC, codLeyenda, desLeyenda)
        VALUES ('$TIPO', '$id_boleta_impresa', '1000', '" . $conexion->real_escape_string($desLeyenda) . "')");

    // 6. ENVÍO DIRECTO A SUNAT VÍA SunatService (Independiente)
    $comprobanteSunat = [
        'tipo_doc'           => '03',
        'serie'              => $SERIE,
        'correlativo'        => (int)$COMPROBANTE,
        'fecha_emision'      => $fecEmision,
        'hora_emision'       => $horEmision,
        'moneda'             => $tipMoneda,
        'condicion_pago'     => ($formaPago == 2) ? 'CREDITO' : 'CONTADO',
        'total'              => $totalFinal,
        'subtotal_gravado'   => $subtotal_gravado,
        'igv_total'          => $igv_total,
        'subtotal_exonerado' => $subtotal_exonerado,
        'subtotal_inafecto'  => $subtotal_inafecto,
        'subtotal_gratuito'  => $subtotal_gratuito,
    ];

    if ($formaPago == 2) {
        $comprobanteSunat['cuotas'] = [
            [
                'monto'      => $totalFinal,
                'fecha_pago' => ($fecVencimiento !== '-') ? $fecVencimiento : date('Y-m-d', strtotime('+30 days')),
            ]
        ];
    }

    $clienteSunat = [
        'tipo_doc'     => $sunatTipDoc,
        'numero_doc'   => $numDocUsuario,
        'razon_social' => $rznSocialUsuario,
        'direccion'    => $desDireccionCliente ?: '-',
    ];

    $sunatMensaje = "Comprobante emitido correctamente.";
    try {
        if ($envioSunat !== 'automatico') {
            SellData::updateSunat($sell_id, [
                'estado_sunat' => 'pendiente',
                'cdr_descripcion' => 'Envío a SUNAT pendiente de envío manual.'
            ]);
            $sunatMensaje = "Comprobante generado localmente. Envío SUNAT pendiente (manual).";
        } else {
            $sunatService = new SunatService();
            $resSunat = $sunatService->enviarComprobante($comprobanteSunat, $detallesSunat, $clienteSunat);

            SellData::updateSunat($sell_id, [
                'estado_sunat'      => $resSunat['estado_sunat'] ?? 'pendiente',
                'cdr_codigo'        => $resSunat['codigo'] ?? null,
                'cdr_descripcion'   => $resSunat['descripcion'] ?? null,
                'codigo_hash'       => $resSunat['codigo_hash'] ?? null,
                'fecha_envio_sunat' => date('Y-m-d H:i:s'),
            ]);
            if (!empty($resSunat['descripcion'])) {
                $sunatMensaje = $resSunat['descripcion'];
            }
        }
    } catch (Exception $e) {
        error_log("Error al procesar envío SUNAT: " . $e->getMessage());
        SellData::updateSunat($sell_id, [
            'estado_sunat'      => 'pendiente',
            'cdr_descripcion'   => 'Error en generación/envío: ' . $e->getMessage(),
            'fecha_envio_sunat' => date('Y-m-d H:i:s'),
        ]);
        $sunatMensaje = "Comprobante generado localmente. Envío SUNAT pendiente: " . $e->getMessage();
    }

    // Vaciar carrito
    unset($_SESSION["cart"]);
    setcookie("selled", "selled");

    echo json_encode([
        'status'   => 'success',
        'sell_id'  => $sell_id,
        'tipodoc'  => 3,
        'serie'    => $SERIE,
        'numero'   => $COMPROBANTE,
        'message'  => $sunatMensaje,
        'redirect' => "./?view=onesell&id={$sell_id}&tipodoc=3"
    ]);
    exit();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
    exit();
}
