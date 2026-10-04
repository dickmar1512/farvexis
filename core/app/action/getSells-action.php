<?php
header('Content-Type: application/json');
$listaSituacion = [
		"ListaSituacion" => [
			["id" => "01", "nombre" => "Por Generar XML"],
			["id" => "02", "nombre" => "XML Generado"],
			["id" => "03", "nombre" => "Enviado y Aceptado SUNAT"],
			["id" => "04", "nombre" => "Enviado y Aceptado SUNAT con Obs."],
			["id" => "05", "nombre" => "Rechazado por SUNAT"],
			["id" => "06", "nombre" => "Con Errores"],
			["id" => "07", "nombre" => "Por Validar XML"],
			["id" => "08", "nombre" => "Enviado a SUNAT Por Procesar"],
			["id" => "09", "nombre" => "Enviado a SUNAT Procesando"],
			["id" => "10", "nombre" => "Rechazado por SUNAT"],
			["id" => "11", "nombre" => "Enviado y Aceptado SUNAT"],
			["id" => "12", "nombre" => "Enviado y Aceptado SUNAT con Obs."]
		]
	];

	$dbPath = '../efact1.3.4/bd/BDFacturador.db';	
	$rutaXML = '../efact1.3.4/sunat_archivos/sfs/FIRMA';
	$rutaCDR = '../efact1.3.4/sunat_archivos/sfs/RPTA';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $response = [
        'data' => [],
        'total_ventas' => '0.00',
        'total_capital' => '0.00',
        'total_ganancia' => '0.00',
        'success' => false
    ];

    $admin = UserData::getById($_SESSION["user_id"])->is_admin;
    $tv = 0;
    $tc = 0;

    // Procesar parámetros de fecha
    $fechaSd = date('Y-m-d');
    $fechaEd = date('Y-m-d');
    
    if (isset($_GET["sd"]) && isset($_GET["ed"]) && $_GET["sd"] != "" && $_GET["ed"] != "") { 
        $fechaini = DateTime::createFromFormat('d/m/Y', $_GET['sd']);
        $fechafin = DateTime::createFromFormat('d/m/Y', $_GET['ed']);
        
        if ($fechaini && $fechafin) {
            $fechaSd = $fechaini->format('Y-m-d');
            $fechaEd = $fechafin->format('Y-m-d');
        } else {
            // Reintentar con formato Y-m-d si d/m/Y falla
            $fechaini = DateTime::createFromFormat('Y-m-d', $_GET['sd']);
            $fechafin = DateTime::createFromFormat('Y-m-d', $_GET['ed']);
            if($fechaini && $fechafin){
                $fechaSd = $fechaini->format('Y-m-d');
                $fechaEd = $fechafin->format('Y-m-d');
            }
        }
    }
    
    // Obtener datos según filtros
    $user_id = isset($_GET["user_id"]) ? intval($_GET["user_id"]) : 0;
    
    $products = SellData::getSells($fechaSd, $fechaEd, $user_id);
    
    $plin = 0;
    $yape = 0;
    $tdebito = 0;
    $tcredito = 0;
    $efectivo = 0;
    
    // Conexión SQLite única fuera del loop
    $sqliteDb = null;
    if (file_exists($dbPath) && class_exists('SQLite3')) {
        try {
            $sqliteDb = new SQLite3($dbPath, SQLITE3_OPEN_READONLY);
        } catch (Exception $e) {
            // Ignorar error de conexión a SQLite para no romper todo el reporte
            error_log("Error al conectar a SQLite: " . $e->getMessage());
        }
    }

    // Procesar resultados
    $data = [];
    foreach ($products as $sell) {
        $usuario = UserData::getById($sell->user_id);
        
        $cliente = null;
        if (!empty($sell->person_id)) {
            $cliente = PersonData::getById($sell->person_id);
        }

        $notacomprobar = $sell->serie . "-" . $sell->comprobante; 
		$probar = NotData::getByIdComprobado($notacomprobar);

        switch ($sell->tipo_pago){
            case 1: $medioPago = "EFECTIVO"; break;
			case 2: $medioPago = "PLIN"; break;
			case 3: $medioPago = "YAPE"; break;
			case 4: $medioPago = "TARJETA DEBITO"; break;
			case 5: $medioPago = "TARJETA CREDITO"; break;	
			default: $medioPago = "OTRO MEDIO DE PAGO"; break;				
		}

        $pago_parcial_data = SellData::getImportePagoParcial($sell->id);
        $importe_pp = 0;
        if (!empty($pago_parcial_data)) {
            $importe_pp = floatval($pago_parcial_data[0]->importepp);
        }

        if ($importe_pp > 0 && $sell->tipo_pago != 1) {
            $medioPago = $medioPago . " / EFECTIVO";
        }
								
        // Datos empresa para archivo SUNAT
        $empresa = EmpresaData::getDatos();
        $ruc = $empresa ? $empresa->Emp_Ruc : '20000000001';
        $tipoDocSunat = ($sell->tipo_comprobante == 1) ? '01' : '03';
        $corrPad = str_pad($sell->comprobante, 8, '0', STR_PAD_LEFT);
        $nomArch = "{$ruc}-{$tipoDocSunat}-{$sell->serie}-{$corrPad}";

        // Búsqueda flexible de archivos en disco (soporta cualquier RUC previo o actual)
        $foundXml = glob("storage/FIRMA/*-{$tipoDocSunat}-{$sell->serie}-{$corrPad}.xml");
        $foundCdr = glob("storage/RPTA/R-*-{$tipoDocSunat}-{$sell->serie}-{$corrPad}.zip");

        $estadoSituacion = '-';
        $fechaEnvio = '-';

        if (!empty($sell->estado_sunat)) {
            $fechaEnvio = !empty($sell->fecha_envio_sunat) ? $sell->fecha_envio_sunat : '-';
            if ($sell->estado_sunat === 'aceptado') {
                $nombreSituacion = 'Aceptado';
                $estadoSituacion = '03';
            } elseif ($sell->estado_sunat === 'rechazado') {
                $nombreSituacion = 'Rechazado';
                $estadoSituacion = '05';
            } else {
                $nombreSituacion = 'Pendiente';
                $estadoSituacion = '01';
            }
        } else {
            $documento = false;
            if ($sqliteDb) {
                $query = "SELECT * FROM DOCUMENTO WHERE NUM_DOCU = '" . $sell->serie . "-" . $sell->comprobante . "'";
                $results = $sqliteDb->query($query);
                if ($results) {
                    $documento = $results->fetchArray(SQLITE3_ASSOC);
                }
            }
                
            if ($documento === false) {
                $documento = [
                    'FEC_GENE' => null, 'FEC_ENVI' => null, 'FEC_CARG' => null,
                    'TIP_DOCU' => null, 'NUM_DOCU' => null, 'NUM_RUC' => null,
                    'NOM_ARCH' => null, 'TIP_ARCH' => null, 'DES_OBSE' => null,
                    'FIRM_DIGITAL' => null, 'IND_SITU' => null
                ];
            }
            
            $fechaEnvio = $documento['FEC_ENVI'] ?? '-';
            $estadoSituacion = $documento['IND_SITU'] ?? '-';
            $nombreArchivo = $documento['NOM_ARCH'] ?? '-';
            $comprobanteXML = $nombreArchivo . ".xml";
            $comprobanteCDR = "R" . $nombreArchivo . ".zip";

            if (empty($foundXml) && isset($comprobanteXML) && file_exists($rutaXML.'/'.$comprobanteXML)) {
                $foundXml = [$rutaXML.'/'.$comprobanteXML];
            }
            if (empty($foundCdr) && isset($comprobanteCDR) && file_exists($rutaCDR.'/'.$comprobanteCDR)) {
                $foundCdr = [$rutaCDR.'/'.$comprobanteCDR];
            }

            $situacion = array_filter($listaSituacion['ListaSituacion'], function($item) use ($estadoSituacion) {
                return $item['id'] == $estadoSituacion;
            });
            $nombreSituacion = !empty($situacion) ? current($situacion)['nombre'] : 'Pendiente';
        }

        $isCreditNote = (isset($probar->TIPO_DOC) && $probar->TIPO_DOC == 7);
        $isRejected   = in_array($estadoSituacion, ["05", "10", "06"]) || ($sell->estado_sunat === 'rechazado');
        $isAnnulled   = ($sell->estado == 0 || stripos($nombreSituacion, 'anulad') !== false || stripos($nombreSituacion, 'baja') !== false);
        $isInvalid    = ($isCreditNote || $isRejected || $isAnnulled);

        // Formatear Badge de Estado SUNAT
        $estadoSunatRaw = strtolower(trim($sell->estado_sunat ?? ''));
        if ($sell->estado == 0) {
            $estadoBadge = '<span class="badge badge-secondary px-2 py-1 shadow-sm" style="font-size:0.82rem;"><i class="fas fa-ban mr-1"></i> Anulado</span>';
        } elseif ($isCreditNote) {
            $estadoBadge = '<a href="./notacreditoboletat/'.$probar->SERIE.'-'.$probar->COMPROBANTE.'" class="badge badge-warning px-2 py-1 shadow-sm text-white" style="font-size:0.82rem;"><i class="fas fa-file-invoice mr-1"></i> N.CRE: '.$probar->SERIE.'-'.$probar->COMPROBANTE.'</a>';
        } elseif ($estadoSunatRaw === 'aceptado' || in_array($estadoSituacion, ["03", "04", "11", "12"])) {
            $estadoBadge = '<span class="badge badge-success px-2 py-1 shadow-sm" style="font-size:0.82rem; background-color:#28a745 !important; color:#fff;"><i class="fas fa-check-circle mr-1"></i> Aceptado</span>';
        } elseif ($estadoSunatRaw === 'rechazado' || in_array($estadoSituacion, ["05", "06", "10"])) {
            $estadoBadge = '<span class="badge badge-danger px-2 py-1 shadow-sm" style="font-size:0.82rem; background-color:#dc3545 !important; color:#fff;"><i class="fas fa-times-circle mr-1"></i> Rechazado</span>';
        } else {
            $estadoBadge = '<span class="badge badge-info px-2 py-1 shadow-sm" style="font-size:0.82rem; background-color:#17a2b8 !important; color:#fff;"><i class="fas fa-clock mr-1"></i> Pendiente</span>';
        }

        if ($isInvalid) {
            $background = "#FFC4C4";
        } else {
            if (isset($probar->TIPO_DOC) && $probar->TIPO_DOC == 8) { 
                $background = "#C2FCCF"; 
            } else {
                $background = "#FFFFFF";
            }
        }

        $verComprobanteLink = '<a href="./onesell/'.$sell->id.'/'.$sell->tipo_comprobante.'" class="btn btn-xs btn-outline-dark font-weight-bold" title="Ver Comprobante"><i class="fas fa-eye"></i></a>';

        // Botones N.C. y N.D. (estilo outline red / green)
        $notaCreditoLink = '';
        $notaDebitoLink  = '';

        if ($isCreditNote) {
            $notaCreditoLink = '<a href="./notacreditoboletat/'.$probar->SERIE.'-'.$probar->COMPROBANTE.'" class="btn btn-outline-danger btn-xs px-2" title="Ver Nota de Crédito '.$probar->SERIE.'-'.$probar->COMPROBANTE.'"><i class="fas fa-file-invoice mr-1"></i> N.Cred</a>';
        } elseif ($sell->estado != 0) {
            if ((int)$sell->tipo_comprobante === 3) {
                $notaCreditoLink = '<a href="./?view=nocboleta&id='.$sell->id.'" class="btn btn-outline-danger btn-xs px-2" title="Generar Nota de Crédito"><i class="fas fa-file-invoice mr-1"></i> N.Cred</a>';
                $notaDebitoLink  = '<a href="./?view=nodboleta&id='.$sell->id.'" class="btn btn-outline-success btn-xs px-2" title="Generar Nota de Débito"><i class="fas fa-file-invoice mr-1"></i> N.Deb</a>';
            } else {
                $notaCreditoLink = '<a href="./?view=nocfactura&id='.$sell->id.'" class="btn btn-outline-danger btn-xs px-2" title="Generar Nota de Crédito"><i class="fas fa-file-invoice mr-1"></i> N.Cred</a>';
                $notaDebitoLink  = '<a href="./?view=nodfactura&id='.$sell->id.'" class="btn btn-outline-success btn-xs px-2" title="Generar Nota de Débito"><i class="fas fa-file-invoice mr-1"></i> N.Deb</a>';
            }
        }
        
        if (!empty($foundXml)) {
            $xmlFile = $foundXml[0];
            $xmlBase = basename($xmlFile);
            $descargarXMLLink = '<a href="'.$xmlFile.'" class="btn btn-xs btn-outline-xml" download="'.$xmlBase.'" title="Descargar XML">XML</a>';
        } else {
            $descargarXMLLink = '';
        }

        if (!empty($foundCdr)) {
            $cdrFile = $foundCdr[0];
            $cdrBase = basename($cdrFile);
            $descargarCDRLink = '<a href="'.$cdrFile.'" class="btn btn-xs btn-outline-cdr" download="'.$cdrBase.'" title="Descargar CDR">CDR</a>';
        } else {
            $descargarCDRLink = '';
        }

        $enviarSunatLink = ($sell->estado_sunat === 'pendiente' || empty($sell->estado_sunat))
            && in_array((int)$sell->tipo_comprobante, [1, 3], true)
            && $sell->estado != 0
            ? '<button type="button" class="btn btn-xs btn-outline-sunat" onclick="enviarSunatVenta(' . (int)$sell->id . ')" title="Enviar a SUNAT">SUNAT</button>'
            : '';

        $fechaObj = new DateTime($sell->created_at);
        $fechaFormateada = $fechaObj->format('d/m/Y H:i:s');

        $capital = 0;
        if ($admin == 1 && !$isInvalid) {
            $objOper = OperationData::getAllProductsBySellId($sell->id);
            foreach ($objOper as $oper) {
                $objProd = ProductData::getById($oper->product_id);
                if($objProd){
                    $capital += $oper->q * $objProd->price_in;
                }
            }
            $tc += $capital;
        }
        
        $total = $isInvalid ? 0 : $sell->total;
        $tv += $total;
        
        if (!$isInvalid && in_array($sell->tipo_comprobante, [1, 3])) {
            $monto_principal = $total - $importe_pp;
            
            switch ($sell->tipo_pago) {
                case 1: $efectivo += $total; break;
                case 2: $plin += $monto_principal; $efectivo += $importe_pp; break;
                case 3: $yape += $monto_principal; $efectivo += $importe_pp; break;
                case 4: $tdebito += $monto_principal; $efectivo += $importe_pp; break;
                case 5: $tcredito += $monto_principal; $efectivo += $importe_pp; break;
            }
        }
        
        $data[] = [
            'background' => $background,
            'verComprobante' => $verComprobanteLink,
            'verNotaCredito' => $notaCreditoLink,
            'notaDebito' => $notaDebitoLink,
            'comprobante' => $sell->serie.'-'.$sell->comprobante,
            'cliente' => $cliente ? $cliente->name . ' ' . $cliente->lastname : 'PÚBLICO GENERAL',
            'importe' => $total,
            'medioPago' => $medioPago,
            'fecha' => $fechaFormateada,
            'fechaEnvio' => $fechaEnvio,
            'estado' => $estadoBadge,
            'descargarXML' => $descargarXMLLink,
            'descargarCDR' => $descargarCDRLink,
            'enviarSunat' => $enviarSunatLink,
            'usuario' => $usuario ? $usuario->username : 'SISTEMA'
        ];
    }

    if ($sqliteDb) $sqliteDb->close();

    $response['data'] = $data;
    $response['total_ventas'] = number_format($tv, 2, '.', '');
    $response['total_capital'] = number_format($tc, 2, '.', '');
    $response['total_ganancia'] = number_format($tv - $tc, 2, '.', '');
    $response['total_plin'] = number_format($plin, 2, '.', '');
    $response['total_yape'] = number_format($yape, 2, '.', '');
    $response['total_tdebito'] = number_format($tdebito, 2, '.', '');
    $response['total_tcredito'] = number_format($tcredito, 2, '.', '');
    $response['success'] = true;

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
    $response['success'] = false;
}

echo json_encode($response);
exit;
?>
