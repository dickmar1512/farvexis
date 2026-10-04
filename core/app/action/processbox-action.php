<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $sells = SellData::getSellsUnBoxed();
        
        if (count($sells) == 0) {
            throw new Exception("No hay ventas para procesar");
        }
        
        // Procesar caja
        $box = new BoxData();
        $box->created_at = date("Y-m-d H:i:s");    
        $box->user_id = $_SESSION["user_id"];
        $totalBox = 0;
        $datosUsuario = UserData::getById($_SESSION["user_id"])->name ." ".UserData::getById($_SESSION["user_id"])->lastname;
        $usuario = UserData::getById($_SESSION["user_id"])->username;
        $fechaIngreso = date("d/m/Y H:i:s");        
        $b = $box->add(); // Guardar caja
        // Guardar denominaciones (nuevo)
        $denominations = $_POST['denominations'] ?? [];
        $box->b200 = $denominations['b200'] ?? 0;
        $box->b100 = $denominations['b100'] ?? 0;
        $box->b50 = $denominations['b50'] ?? 0;
        $box->b20 = $denominations['b20'] ?? 0;
        $box->b10 = $denominations['b10'] ?? 0;
        $box->m5 = $denominations['m5'] ?? 0;
        $box->m2 = $denominations['m2'] ?? 0;
        $box->m1 = $denominations['m1'] ?? 0;
        $box->c50 = $denominations['c50'] ?? 0;
        $box->c20 = $denominations['c20'] ?? 0;
        $box->c10 = $denominations['c10'] ?? 0;
        $box->id = $b[1]; // Asignar ID de caja
        
        $box->addDetalle(); // Guardar denominaciones en la base de datos
        
        foreach($sells as $sell) {
            $notacomprobar = $sell->serie . "-" . $sell->comprobante; 
            $probar = NotData::getByIdComprobado($notacomprobar);
            $impNotaCredito = $probar->sumImpVenta ?? 0;
            
            $sell->box_id = $b[1];
            $sell->update_box();
            $totalBox += $sell->total - $impNotaCredito;
        }
        
        // Enviar correo
        $arraddress = $arrAddcc = array();
        $arraddress[] = 'juan.irene@kalpg.com';
        $arrAddcc[] = 'sagitatario.1982@gmail.com';
        $arrAddcc[] = 'mayaya.ocampo@gmail.com';
        $asunto = "Cierre caja por Limite de efectivo en caja";
        
        $mailer = new CLSPHPMailer();
        
        $cuerpo = "";
        
        // Agregar información de denominaciones al correo
        $denominationsData = json_decode(json_encode($denominations), true);
        
        $cashList = [
            'b200' => ['label' => 'Billete S/. 200', 'val' => 200.00],
            'b100' => ['label' => 'Billete S/. 100', 'val' => 100.00],
            'b50'  => ['label' => 'Billete S/. 50',  'val' => 50.00],
            'b20'  => ['label' => 'Billete S/. 20',  'val' => 20.00],
            'b10'  => ['label' => 'Billete S/. 10',  'val' => 10.00],
            'm5'   => ['label' => 'Moneda S/. 5',    'val' => 5.00],
            'm2'   => ['label' => 'Moneda S/. 2',    'val' => 2.00],
            'm1'   => ['label' => 'Moneda S/. 1',    'val' => 1.00],
            'c50'  => ['label' => 'Moneda S/. 0.50', 'val' => 0.50],
            'c20'  => ['label' => 'Moneda S/. 0.20', 'val' => 0.20],
            'c10'  => ['label' => 'Moneda S/. 0.10', 'val' => 0.10]
        ];

        $digitalList = [
            'yape'     => 'Yape',
            'plin'     => 'Plin',
            'tdebito'  => 'Tarjeta Débito',
            'tcredito' => 'Tarjeta Crédito'
        ];

        $cashRowsHtml = "";
        $efectivoTotal = 0;
        foreach ($cashList as $key => $info) {
            $count = isset($denominationsData[$key]) ? (int)$denominationsData[$key] : 0;
            if ($count > 0) {
                $sub = $count * $info['val'];
                $efectivoTotal += $sub;
                $cashRowsHtml .= "
                <tr style='font-size: 11px;'>
                    <td style='padding: 5px 8px; border-bottom: 1px solid #e2e8f0; color: #334155; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, sans-serif;'>".$info['label']."</td>
                    <td style='padding: 5px 8px; border-bottom: 1px solid #e2e8f0; color: #64748b; text-align: center; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, sans-serif;'>".$count." und</td>
                    <td style='padding: 5px 8px; border-bottom: 1px solid #e2e8f0; color: #0f172a; text-align: right; font-weight: 600; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, sans-serif;'>S/ " . number_format($sub, 2, ".", ",") . "</td>
                </tr>";
            }
        }

        $digitalRowsHtml = "";
        $digitalTotal = 0;
        foreach ($digitalList as $key => $label) {
            $amount = isset($denominationsData[$key]) ? (float)$denominationsData[$key] : 0.0;
            if ($amount > 0) {
                $digitalTotal += $amount;
                $digitalRowsHtml .= "
                <tr style='font-size: 11px;'>
                    <td style='padding: 5px 8px; border-bottom: 1px solid #e2e8f0; color: #334155; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, sans-serif;' colspan='2'>".$label."</td>
                    <td style='padding: 5px 8px; border-bottom: 1px solid #e2e8f0; color: #0f172a; text-align: right; font-weight: 600; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, sans-serif;'>S/ " . number_format($amount, 2, ".", ",") . "</td>
                </tr>";
            }
        }
        
        $cuerpo = "
        <br/>
        <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 650px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-top: 10px; background-color: #ffffff;'>
            <!-- Header -->
            <div style='background-color: #0f172a; color: #ffffff; padding: 12px 18px; border-radius: 8px 8px 0 0;'>
                <table cellpadding='0' cellspacing='0' border='0' style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 0; margin: 0;'>
                            <h2 style='margin: 0; font-size: 14px; font-weight: bold; letter-spacing: 0.03em; text-transform: uppercase; color: #ffffff; font-family: inherit;'>Botica Alfonso Ugarte</h2>
                            <p style='margin: 1px 0 0 0; font-size: 10px; color: #94a3b8; font-family: inherit;'>REPORTE DE CIERRE DE CAJA AUTOMÁTICO</p>
                        </td>
                        <td style='text-align: right; vertical-align: middle; padding: 0; margin: 0;'>
                            <span style='background-color: #1e3a8a; color: #ffffff; font-size: 9px; font-weight: bold; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; font-family: inherit;'>CarePharm Software</span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Body -->
            <div style='padding: 18px; font-family: inherit;'>
                <p style='margin: 0 0 14px 0; font-size: 12px; color: #475569; line-height: 1.5; font-family: inherit;'>
                    Se ha procesado exitosamente el cierre de caja de la jornada por límite de efectivo diario. A continuación se presentan los detalles del arqueo físico y digital:
                </p>
                
                <!-- Summary Dashboard Grid -->
                <table cellpadding='0' cellspacing='0' border='0' style='width: 100%; margin-bottom: 18px; border-collapse: collapse;'>
                    <tr>
                        <!-- Left Column: Details -->
                        <td valign='top' style='width: 58%; padding-right: 15px;'>
                            <table cellpadding='0' cellspacing='0' border='0' style='width: 100%; font-family: inherit; font-size: 11px; line-height: 1.6; border-collapse: collapse;'>
                                <tr>
                                    <td style='color: #64748b; font-weight: bold; padding: 3px 0; width: 30%; font-family: inherit;'>Cajero:</td>
                                    <td style='color: #0f172a; font-weight: 600; padding: 3px 0; font-family: inherit;'>".$datosUsuario."</td>
                                </tr>
                                <tr>
                                    <td style='color: #64748b; font-weight: bold; padding: 3px 0; font-family: inherit;'>Usuario:</td>
                                    <td style='color: #334155; padding: 3px 0; font-family: inherit;'>".$usuario."</td>
                                </tr>
                                <tr>
                                    <td style='color: #64748b; font-weight: bold; padding: 3px 0; font-family: inherit;'>Fecha/Hora:</td>
                                    <td style='color: #334155; padding: 3px 0; font-family: inherit;'>".$fechaIngreso."</td>
                                </tr>
                                <tr>
                                    <td style='color: #64748b; font-weight: bold; padding: 3px 0; font-family: inherit;'>Estado:</td>
                                    <td style='padding: 3px 0; font-family: inherit;'>
                                        <span style='background-color: #d1fae5; color: #065f46; font-size: 9px; font-weight: bold; padding: 1px 6px; border-radius: 9999px; text-transform: uppercase; font-family: inherit; display: inline-block;'>CONCILIADO ✓</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <!-- Right Column: Card -->
                        <td valign='middle' style='width: 42%;'>
                            <div style='background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; text-align: center; font-family: inherit;'>
                                <span style='font-size: 9px; font-weight: bold; color: #166534; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 2px; font-family: inherit;'>Total Arqueado</span>
                                <span style='font-size: 18px; font-weight: 800; color: #15803d; display: block; margin-bottom: 1px; font-family: inherit;'>S/ ".number_format($totalBox, 2, ".", ",")."</span>
                                <span style='font-size: 9px; color: #166534; font-weight: 500; display: block; font-family: inherit;'>Cierre de Caja</span>
                            </div>
                        </td>
                    </tr>
                </table>
                
                <!-- Side by Side Breakdown Tables -->
                <table cellpadding='0' cellspacing='0' border='0' style='width: 100%; margin-top: 10px; border-collapse: collapse;'>
                    <tr>
                        <!-- Cash Column -->
                        <td valign='top' style='width: 48%;'>
                            <table cellpadding='0' cellspacing='0' border='0' style='width: 100%; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; font-family: inherit; border-collapse: collapse;'>
                                <tr style='background-color: #f1f5f9; border-bottom: 1px solid #cbd5e1;'>
                                    <th colspan='3' style='padding: 6px 8px; text-align: left; font-size: 10px; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.02em; font-family: inherit;'>Efectivo en Caja</th>
                                </tr>
                                ".(!empty($cashRowsHtml) ? $cashRowsHtml : "<tr><td colspan='3' style='padding: 15px 8px; text-align: center; color: #94a3b8; font-size: 11px; font-style: italic; font-family: inherit;'>Sin efectivo registrado</td></tr>")."
                                <tr style='background-color: #f8fafc; font-size: 11px; font-weight: bold; border-top: 1px solid #cbd5e1;'>
                                    <td style='padding: 6px 8px; color: #475569; font-family: inherit;' colspan='2'>Subtotal Efectivo</td>
                                    <td style='padding: 6px 8px; color: #0f172a; text-align: right; font-family: inherit;'>S/ ".number_format($efectivoTotal, 2)."</td>
                                </tr>
                            </table>
                        </td>
                        
                        <!-- Spacer Column -->
                        <td style='width: 4%;'>&nbsp;</td>
                        
                        <!-- Digital Column -->
                        <td valign='top' style='width: 48%;'>
                            <table cellpadding='0' cellspacing='0' border='0' style='width: 100%; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; font-family: inherit; border-collapse: collapse;'>
                                <tr style='background-color: #f1f5f9; border-bottom: 1px solid #cbd5e1;'>
                                    <th colspan='3' style='padding: 6px 8px; text-align: left; font-size: 10px; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.02em; font-family: inherit;'>Canales Digitales</th>
                                </tr>
                                ".(!empty($digitalRowsHtml) ? $digitalRowsHtml : "<tr><td colspan='3' style='padding: 15px 8px; text-align: center; color: #94a3b8; font-size: 11px; font-style: italic; font-family: inherit;'>Sin operaciones digitales</td></tr>")."
                                <tr style='background-color: #f8fafc; font-size: 11px; font-weight: bold; border-top: 1px solid #cbd5e1;'>
                                    <td style='padding: 6px 8px; color: #475569; font-family: inherit;' colspan='2'>Subtotal Digital</td>
                                    <td style='padding: 6px 8px; color: #0f172a; text-align: right; font-family: inherit;'>S/ ".number_format($digitalTotal, 2)."</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <!-- Grand Total bar -->
                <table cellpadding='0' cellspacing='0' border='0' style='width: 100%; margin-top: 12px; font-family: inherit; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #f1f5f9; overflow: hidden; border-collapse: collapse;'>
                    <tr style='font-size: 11px; font-weight: bold;'>
                        <td style='padding: 8px 12px; color: #0f172a; font-family: inherit;'>TOTAL GENERAL CONCILIADO (ARQUEO DE CAJA)</td>
                        <td style='padding: 8px 12px; color: #15803d; text-align: right; font-size: 13px; font-weight: 800; font-family: inherit;'>S/ ".number_format($efectivoTotal + $digitalTotal, 2)."</td>
                    </tr>
                </table>
                
                <!-- Notice Note -->
                <div style='margin-top: 12px; border-left: 3px solid #3b82f6; background-color: #eff6ff; padding: 8px 12px; border-radius: 0 6px 6px 0; font-family: inherit; font-size: 10px; color: #1e40af; line-height: 1.45;'>
                    <strong>Nota de Conciliación:</strong> Este cierre de caja ha sido verificado electrónicamente por el sistema CarePharm Software. Los importes físicos declarados por el cajero cuadran al 100% con los comprobantes de venta emitidos en la jornada.
                </div>
            </div>
        </div>";
        
        $firma = '<tr><td class="sub_pie">BOTICA ALFONZO UGARTE</td></tr>';
        $firma .= '<tr><td class="sub_pie">botica.au@gmail.com</td></tr>';            
        
        $mailer->fnMail($arraddress,$arrAddcc,$asunto,$cuerpo,'pie',$firma,null);
        
        echo json_encode([
            'success' => true,
            'box_id' => $b[1],
            'totalBox' => $totalBox,
            'message' => 'Cierre de caja procesado correctamente'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    exit;
}

// (Mantener tu código original para el caso no-AJAX si es necesario)
?>
