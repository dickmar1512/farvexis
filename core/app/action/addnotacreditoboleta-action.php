<?php
    $conexion = Database::getCon();

    if(count($_POST) > 0)
    {
        $TIPO     = '07';
        $TIPO_NOTA = $_POST["tipo"];
        $MOTIVO    = $_POST["motivo"];
        $SERIE     = $_POST["serie"];
        $COMPROBANTE = $_POST["comp"];
        $NUM       = $_POST["numDoc"];

        $RUC    = '';
        $SELLID = 0;

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
        // OBTENER DATOS DEL COMPROBANTE ORIGINAL (tipos 01‒07)
        // ─────────────────────────────────────────────────────────────
        if ($TIPO_NOTA=='01' || $TIPO_NOTA=='02' || $TIPO_NOTA=='03' ||
            $TIPO_NOTA=='04' || $TIPO_NOTA=='05' || $TIPO_NOTA=='06' ||
            $TIPO_NOTA=='07')
        {
            $product = BoletaData::getByNumDoc($NUM);
            if (!$product) {
                $sell = SellData::getByNumDoc($NUM);
                if ($sell) {
                    $product = new BoletaData();
                    $product->id = 0;
                    $product->SERIE = $sell->serie;
                    $product->COMPROBANTE = str_pad((string)$sell->comprobante, 8, '0', STR_PAD_LEFT);
                    $product->EXTRA1 = $sell->id;
                    $product->RUC = EmpresaData::getDatos()->Emp_Ruc ?? '';
                } else {
                    throw new RuntimeException('No se encontró la boleta de referencia.');
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

            $comp_cab = ($product->id > 0) ? CabData::getById($product->id, 3) : null;
            $detalles = ($product->id > 0) ? DetData::getByIdNota($product->id, 3) : [];
            $comp_tri = ($product->id > 0) ? TriData::getById($product->id, 3) : null;
            $comp_ley = ($product->id > 0) ? LeyData::getById($product->id, 3) : null;

            $sellObj = SellData::getById($SELLID);
            $personObj = ($sellObj && $sellObj->person_id) ? PersonData::getById($sellObj->person_id) : null;

            // ── CABECERA ──
            $tipOperacion     = $comp_cab->tipOperacion ?? '0101';
            $fecEmision       = date("Y-m-d");
            $horEmision       = date('H:i:s');
            $fecVencimiento   = $comp_cab->fecVencimiento ?? '-';
            $codLocalEmisor   = $comp_cab->codLocalEmisor ?? '0000';
            $tipDocUsuario    = $comp_cab->tipDocUsuario ?? ($personObj ? ($personObj->kind == 1 ? '1' : '6') : '1');
            $numDocUsuario    = $comp_cab->numDocUsuario ?? ($personObj ? $personObj->pin : '00000000');
            $rznSocialUsuario = $comp_cab->rznSocialUsuario ?? ($personObj ? trim($personObj->name . ' ' . $personObj->lastname) : 'PUBLICO GENERAL');
            $tipMoneda        = $comp_cab->tipMoneda ?? 'PEN';
            $codTipoNota      = $TIPO_NOTA;
            $descMotivo       = $MOTIVO;
            $tipDocModifica   = '03';
            $serieDocModifica = $NUM;
            $sumTotTributos   = $comp_cab->sumTotTributos ?? 0;
            $sumTotValVenta   = $comp_cab->sumTotValVenta ?? ($sellObj ? $sellObj->total : 0);
            $sumPrecioVenta   = $comp_cab->sumPrecioVenta ?? ($sellObj ? $sellObj->total : 0);
            $sumDescTotal     = $comp_cab->sumDescTotal ?? 0;
            $sumOtrosCargos   = $comp_cab->sumOtrosCargos ?? 0;
            $sumTotalAnticipos = $comp_cab->sumTotalAnticipos ?? 0;
            $sumImpVenta      = $comp_cab->sumImpVenta ?? ($sellObj ? $sellObj->total : 0);
            $ublVersionId     = $comp_cab->ublVersionId ?? '2.1';
            $customizationId  = $comp_cab->customizationId ?? '2.0';

            // ── DETALLE (primer ítem como referencia de tributos) ──
            $firstDet = !empty($detalles) ? $detalles[0] : null;
            $codUnidadMedida           = $firstDet->codUnidadMedida ?? 'NIU';
            $codProducto               = $firstDet->codProducto ?? '0';
            $codProductoSUNAT          = $firstDet->codProductoSUNAT ?? '-';
            $codTriIGV                 = $firstDet->codTriIGV ?? '9997';
            $mtoIgvItem                = $firstDet->mtoIgvItem ?? 0;
            $mtoBaseIgvItem            = $firstDet->mtoBaseIgvItem ?? 0;
            $nomTributoIgvItem         = $firstDet->nomTributoIgvItem ?? 'EXO';
            $codTipTributoIgvItem      = $firstDet->codTipTributoIgvItem ?? 'VAT';
            $tipAfeIGV                 = $firstDet->tipAfeIGV ?? '20';
            $porIgvItem                = $firstDet->porIgvItem ?? 0;
            $codTriISC                 = "-";
            $mtoIscItem                = $firstDet->mtoIscItem ?? 0;
            $mtoBaseIscItem            = 0;
            $nomTributoIscItem         = $firstDet->nomTributoIscItem ?? '';
            $codTipTributoIscItem      = $firstDet->codTipTributoIscItem ?? '';
            $tipSisISC                 = $firstDet->tipSisISC ?? '';
            $porIscItem                = $firstDet->porIscItem ?? '';
            $codTriOtroItem            = "-";
            $sumTotTributosItem        = $firstDet->sumTotTributosItem ?? 0;
            $mtoTriOtroItem            = '';
            $mtoBaseTriOtroItem        = 0;
            $nomTributoIOtroItem       = '';
            $codTipTributoIOtroItem    = '';
            $porTriOtroItem            = '';
            $mtoValorReferencialUnitario = $firstDet->mtoValorReferencialUnitario ?? 0;

            // ── TRIBUTOS ──
            $ideTributo      = $comp_tri->ideTributo ?? '9997';
            $nomTributo      = $comp_tri->nomTributo ?? 'EXO';
            $codTipTributo   = $comp_tri->codTipTributo ?? 'VAT';
            $mtoBaseImponible = $comp_tri->mtoBaseImponible ?? 0;
            $mtoTributo      = $comp_tri->mtoTributo ?? 0;

            // ── LEYENDA ──
            $codLeyenda = $comp_ley->codLeyenda ?? '1000';
            $desLeyenda = $comp_ley->desLeyenda ?? '';

        } else {
            // Ruta manual (POST completo) — se mantiene igual que el original
            $tipOperacion     = $_POST["tipOperacion"];
            $fecEmision       = $_POST["fecEmision"];
            $horEmision       = $_POST["horEmision"];
            $fecVencimiento   = $_POST["fecVencimiento"];
            $codLocalEmisor   = $_POST["codLocalEmisor"];
            $tipDocUsuario    = $_POST["tipDocUsuario"];
            $numDocUsuario    = $_POST["numDocUsuario"];
            $rznSocialUsuario = $_POST["rznSocialUsuario"];
            $tipMoneda        = $_POST["tipMoneda"];
            $codTipoNota      = $_POST["codTipoNota"];
            $descMotivo       = $_POST["descMotivo"];
            $tipDocModifica   = $_POST["tipDocModifica"];
            $serieDocModifica = $_POST["serieDocModifica"];
            $sumTotTributos   = $_POST["sumTotTributos"];
            $sumTotValVenta   = 0;
            $sumPrecioVenta   = 0;

            $codUnidadMedida        = $_POST["codUnidadMedida"];
            $codProducto            = $_POST["codProducto"];
            $codProductoSUNAT       = $_POST["codProductoSUNAT"];
            $codTriIGV              = $_POST["codTriIGV"];
            $mtoIgvItem             = $_POST["mtoIgvItem"];
            $mtoBaseIgvItem         = ($_POST['mtoIscItem'] == '') ? 0.00 : $_POST["mtoIscItem"];
            $nomTributoIgvItem      = $_POST["nomTributoIgvItem"];
            $codTipTributoIgvItem   = $_POST["codTipTributoIgvItem"];
            $tipAfeIGV              = $_POST["tipAfeIGV"];
            $porIgvItem             = $_POST["porIgvItem"];
            $codTriISC              = "-";
            $mtoIscItem             = $_POST["mtoIscItem"];
            $mtoBaseIscItem         = 0;
            $nomTributoIscItem      = $_POST["nomTributoIscItem"];
            $codTipTributoIscItem   = $_POST["codTipTributoIscItem"];
            $tipSisISC              = $_POST["tipSisISC"];
            $porIscItem             = $_POST["porIscItem"];
            $codTriOtroItem         = "-";
            $sumTotTributosItem     = $_POST["sumTotTributosItem"];
            $mtoTriOtroItem         = '';
            $mtoBaseTriOtroItem     = 0;
            $nomTributoIOtroItem    = '';
            $codTipTributoIOtroItem = '';
            $porTriOtroItem         = '';
            $mtoValorReferencialUnitario = $_POST["mtoValorReferencialUnitario"];

            $ideTributo       = $_POST["ideTributo"];
            $nomTributo       = $_POST["nomTributo"];
            $codTipTributo    = $_POST["codTipTributo"];
            $mtoBaseImponible = 0;
            $mtoTributo       = $_POST["mtoTributo"];

            $codLeyenda             = "1000";
            $ctaBancoNacionDetraccion = "-";
            $codBienDetraccion      = "-";
            $porDetraccion          = "-";
            $mtoDetraccion          = "-";
            $codPaisCliente         = 'PE';
            $codUbigeoCliente       = $_POST['codUbigeoCliente'];
            $desDireccionCliente    = $_POST['desDireccionCliente'];
            $codPaisEntrega         = "-";
            $codUbigeoEntrega       = "-";
            $desDireccionEntrega    = "-";
        }

        // ─────────────────────────────────────────────────────────────
        $sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
        $COMPROBANTE = ComprobanteCorrelativoData::siguiente($conexion, $TIPO, $SERIE, $codLocalEmisor, $sucursalId);

        // INSERTAR REGISTRO EN TABLA boleta Y OBTENER ID
        // ─────────────────────────────────────────────────────────────
        $sql_DOC = "INSERT INTO boleta (RUC, TIPO, SERIE, COMPROBANTE)
                    VALUES ('$RUC', '$TIPO', '$SERIE', '$COMPROBANTE')";

        $conexion->query($sql_DOC);
        $id_factura_impresa = $conexion->insert_id;
        $TIPO_DOC           = $TIPO;
        $ID_TIPO_DOC        = $id_factura_impresa;

        $downloadfile2 = "../efact1.3.4/sunat_archivos/sfs/DATA/{$RUC}-{$TIPO}-{$SERIE}-{$COMPROBANTE}.det";
        $filecontent2  = "";

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

        // ─────────────────────────────────────────────────────────────
        // HELPER: construye línea .det e inserta en tabla det
        // ─────────────────────────────────────────────────────────────
        $insertarDetalle = function(
            $cantidad, $precio_unitario, $descripcion_producto,
            $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem
        ) use (
            &$filecontent2, &$detallesSunat, $conexion,
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
            $filecontent2 .=
                $codUnidadMedida.'|'.
                $cantidad.'|'.
                $codProducto.'|'.
                $codProductoSUNAT.'|'.
                $descripcion_producto.'|'.
                $precio_unitario.'|'.
                $sumTotTributosItem.'|'.
                $codTriIGV.'|'.
                $mtoIgvItem.'|'.
                $mtoBaseIgvItem.'|'.
                $nomTributoIgvItem.'|'.
                $codTipTributoIgvItem.'|'.
                $tipAfeIGV.'|'.
                $porIgvItem.'|'.
                $codTriISC.'|'.
                $mtoIscItem.'|'.
                $mtoBaseIscItem.'|'.
                $nomTributoIscItem.'|'.
                $codTipTributoIscItem.'|'.
                $tipSisISC.'|'.
                $porIscItem.'|'.
                $codTriOtroItem.'|'.
                $mtoTriOtroItem.'|'.
                $mtoBaseTriOtroItem.'|'.
                $nomTributoIOtroItem.'|'.
                $codTipTributoIOtroItem.'|'.
                $porTriOtroItem.'|-||||||'.
                $mtoPrecioVentaUnitario.'|'.
                $mtoValorVentaItem.'|'.
                $mtoValorReferencialUnitario.'|'.PHP_EOL;

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
                '$descripcion_producto', '$precio_unitario', '$sumTotTributosItem',
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
                'item' => count($detallesSunat) + 1,
                'codigo' => $codProducto,
                'descripcion' => $descripcion_producto,
                'unidad_medida' => $codUnidadMedida,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio_unitario,
                'precio_con_igv' => $mtoPrecioVentaUnitario,
                'valor_venta' => $mtoValorVentaItem,
                'igv_monto' => $mtoIgvItem,
                'igv_tipo' => $tipAfeIGV
            ];
        };

        // ─────────────────────────────────────────────────────────────
        // HELPER: devuelve stock actualizado y registra operación
        // ─────────────────────────────────────────────────────────────
        $devolverStock = function($product, $cantidad) use ($SELLID) {
            if ($product->is_stock == 1) {
                $p_sumar = new ProductData();
                $p_sumar->id    = $product->id;
                $p_sumar->stock = $product->stock + $cantidad;
                $p_sumar->update_stock();

                $op = new OperationData();
                $op->product_id       = $product->id;
                $op->operation_type_id = 1;
                $op->sell_id          = $SELLID;
                $op->q                = $cantidad;
                $op->add();
            }
        };

        // ─────────────────────────────────────────────────────────────
        // PROCESAMIENTO POR TIPO DE NOTA  ← CORRECCIÓN PRINCIPAL
        // Todos los casos al mismo nivel (elseif encadenado)
        // ─────────────────────────────────────────────────────────────

        // TIPOS 01, 02, 06 — Anulación / devolución total
        if ($TIPO_NOTA == '01' || $TIPO_NOTA == '02' || $TIPO_NOTA == '06') {

            $sumTotValVenta = 0;
            $sumPrecioVenta = 0;

            foreach ($operations as $item) {
                $product  = $item->getProduct();
                $cantidad = ($product->is_stock == 0) ? 1 : $item->q;
                $precio_unitario        = $item->prec_alt;
                $descripcion_producto   = $product->name;

                $devolverStock($product, $cantidad);

                $mtoValorVentaItem      = $cantidad * $precio_unitario;
                $mtoPrecioVentaUnitario = $precio_unitario;
                $mtoBaseIgvItem         = $mtoValorVentaItem;

                $insertarDetalle(
                    $cantidad, $precio_unitario, $descripcion_producto,
                    $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem
                );

                $sumTotValVenta += $mtoValorVentaItem;
                $sumPrecioVenta  = $sumTotValVenta + $sumTotTributos;
            }

        // TIPO 03 — Corrección de descripción
        } elseif ($TIPO_NOTA == '03') {

            $data = $_POST['arraydet'];
            $sumTotValVenta = 0;
            $sumPrecioVenta = 0;

            foreach ($operations as $item) {
                $product = $item->getProduct();

                for ($i = 0; $i < count($data); $i++) {
                    if ($data[$i][0] == $product->name) {

                        $cantidad               = $item->q;
                        $precio_unitario        = $item->prec_alt;
                        $descripcion_producto   = $data[$i][1]; // nueva descripción

                        $devolverStock($product, $cantidad);

                        $mtoValorVentaItem      = $cantidad * $precio_unitario;
                        $mtoPrecioVentaUnitario = $precio_unitario;
                        $mtoBaseIgvItem         = $mtoValorVentaItem;

                        $insertarDetalle(
                            $cantidad, $precio_unitario, $descripcion_producto,
                            $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem
                        );

                        $sumTotValVenta += $mtoValorVentaItem;
                        $sumPrecioVenta  = $sumTotValVenta + $sumTotTributos;
                    }
                }
            }

        // TIPO 04 — Descuento global
        } elseif ($TIPO_NOTA == '04') {

            $cantidad               = 1;
            $precio_unitario        = $_POST['dscto'];
            $descripcion_producto   = "DESCUENTO GLOBAL | " . $MOTIVO;
            $mtoValorVentaItem      = $_POST['dscto'];
            $mtoPrecioVentaUnitario = $precio_unitario;
            $mtoBaseIgvItem         = $mtoValorVentaItem;

            $insertarDetalle(
                $cantidad, $precio_unitario, $descripcion_producto,
                $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem
            );

            $sumTotValVenta = $cantidad * $precio_unitario;
            $sumPrecioVenta = $sumTotValVenta + $sumTotTributos;

        // TIPO 05 — Corrección de precio
        } elseif ($TIPO_NOTA == '05') {

            $data = $_POST['arraydet'];
            $sumTotValVenta = 0;
            $sumPrecioVenta = 0;

            foreach ($operations as $item) {
                $product = $item->getProduct();

                for ($i = 0; $i < count($data); $i++) {
                    if ($data[$i][0] == $product->name) {

                        $cantidad               = $item->q;
                        $precio_unitario        = $data[$i][1]; // nuevo precio
                        $descripcion_producto   = $data[$i][0];
                        $mtoValorVentaItem      = $data[$i][1];
                        $mtoPrecioVentaUnitario = $precio_unitario;
                        $mtoBaseIgvItem         = $mtoValorVentaItem;

                        $insertarDetalle(
                            $cantidad, $precio_unitario, $descripcion_producto,
                            $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem
                        );

                        $sumTotValVenta += $cantidad * $precio_unitario;
                        $sumPrecioVenta  = $sumTotValVenta + $sumTotTributos;
                    }
                }
            }

        // TIPO 07 — Devolución parcial  ← ESTE CASO ERA EL QUE NO SE EJECUTABA
        } elseif ($TIPO_NOTA == '07') {

            $data = $_POST['arraydet'];
            $sumTotValVenta = 0;
            $sumPrecioVenta = 0;

            foreach ($operations as $item) {
                $product = $item->getProduct();

                for ($i = 0; $i < count($data); $i++) {
                    if ($data[$i][0] == $product->name) {

                        $cantidad               = $data[$i][1]; // cantidad a devolver
                        $precio_unitario        = $item->prec_alt;
                        $descripcion_producto   = $data[$i][0];

                        $devolverStock($product, $cantidad);

                        $mtoValorVentaItem      = $cantidad * $precio_unitario;
                        $mtoPrecioVentaUnitario = $precio_unitario;
                        $mtoBaseIgvItem         = $mtoValorVentaItem;

                        $insertarDetalle(
                            $cantidad, $precio_unitario, $descripcion_producto,
                            $mtoValorVentaItem, $mtoPrecioVentaUnitario, $mtoBaseIgvItem
                        );

                        $sumTotValVenta += $mtoValorVentaItem;
                        $sumPrecioVenta  = $sumTotValVenta + $sumTotTributos;
                    }
                }
            }
        }

        // ─────────────────────────────────────────────────────────────
        // LEYENDA — monto en letras
        // ─────────────────────────────────────────────────────────────
        $numLetra   = NumeroLetras::convertir(number_format($sumPrecioVenta, 2, '.', ','));
        $desLeyenda = $numLetra;

        // ─────────────────────────────────────────────────────────────
        // ESCRIBIR ARCHIVO .det
        // ─────────────────────────────────────────────────────────────
        $ar = fopen($downloadfile2, "a") or die("Error al crear .det");
        fwrite($ar, $filecontent2);
        fclose($ar);

        // ─────────────────────────────────────────────────────────────
        // INSERT tabla nota (.not / CAB)
        // ─────────────────────────────────────────────────────────────
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
            '$codLocalEmisor', '$tipDocUsuario', '$numDocUsuario', '$rznSocialUsuario',
            '$tipMoneda', '$codTipoNota', '$descMotivo', '$tipDocModifica', '$serieDocModifica',
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

        $envio_sunat = $_POST['envio_sunat'] ?? $_POST['envio_sunatb'] ?? 'automatico';
        if ($envio_sunat === 'manual') {
            $conexion->query("UPDATE nota SET estado_sunat='pendiente' WHERE TIPO_DOC='07' AND ID_TIPO_DOC='$ID_TIPO_DOC'");
            $resSunat = [
                'exito' => false,
                'estado_sunat' => 'pendiente',
                'descripcion' => 'Nota de crédito registrada en modo Manual. Pendiente de envío a SUNAT.'
            ];
        } else {
            // Envío directo a SUNAT (Modo Automático)
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
                'tipo_doc'     => $tipDocUsuario ?: '1',
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
                error_log("Error al enviar Nota de Crédito Boleta: " . $e->getMessage());
                $resSunat = ['exito' => false, 'estado_sunat' => 'pendiente', 'descripcion' => $e->getMessage()];
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => !empty($resSunat['exito']) ? 'success' : 'pending',
            'estado_sunat' => $resSunat['estado_sunat'] ?? 'pendiente',
            'message' => $resSunat['descripcion'] ?? 'Nota de crédito procesada.',
            'redirect' => BASE_URL . '/notacreditoboleta/' . rawurlencode($SERIE . '-' . $COMPROBANTE)
        ]);
        return true;
    }
?>

