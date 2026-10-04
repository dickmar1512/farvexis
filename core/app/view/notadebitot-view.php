<script type="text/javascript">
    function imprimir()
    {
        $('#imprimir').hide();
        $('#div_opciones').hide();
        window.print();
        $('#imprimir').show();
        $('#div_opciones').show();
    }
</script>
<?php
    if (isset($_GET['im']))
    {
        ?>
        <script type="text/javascript">
            imprimir();
        </script>
        <?php
    }

    $product = Factura2Data::getByNumDoc($_GET["num"]);
    $comp_cab = null;
    $detalles = [];
    $sell = null;

    if ($product) {
        $comp_cab = NotData::getById($product->id, 8);
        $detalles = DetData::getById($product->id, 8);
        if (!$detalles) {
            $detalles = [];
        }
        if ($comp_cab && !empty($comp_cab->serieDocModifica)) {
            $sell = SellData::getByNroDoc($comp_cab->serieDocModifica);
        }
    }

    $empresa = EmpresaData::getDatos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.onesell.css">
    <style>
        body { background: linear-gradient(135deg, #dcfce7, #f8fafc); }
        .badge-nota { display: inline-block; padding: 0.3em 0.8em; font-size: 85%; font-weight: 700; border-radius: 999px; background: #22c55e; color: #fff; }
        .modifica-label { font-size: 0.8rem; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: block; }
        @media print { .btn, .btn-back, .btn-primary { display: none !important; } }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-section fade-in">
            <div class="header-title">
                <div class="title-group">
                    <i class="fas fa-file-invoice"></i>
                    <h1>Nota de Débito</h1>
                </div>
                <div class="breadcrumb">
                    <i class="fas fa-home"></i> Reportes > Nota de Débito
                </div>
            </div>

            <div class="controls-section">
                <button class="btn btn-back" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
                <button class="btn btn-primary" id="imprimir">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>

        <div class="receipt-container fade-in" id="receiptArea">
            <div class="company-info">
                <div class="company-logo">
                    <i class="fas fa-building" style="font-size: 1.5rem;"></i>
                </div>
                <div class="company-details">
                    <h3><?php echo $empresa->Emp_RazonSocial ?></h3>
                    <p><?php echo $empresa->Emp_Direccion ?></p>
                    <p>📞 <?php echo $empresa->Emp_Telefono ?></p>
                    <p>✉ <?php echo $empresa->Emp_Celular ?></p>
                </div>
                <div class="document-info text-center">
                    <div style="font-size: 0.85rem;"><strong>RUC: <?php echo $empresa->Emp_Ruc ?></strong></div>
                    <div style="margin: 6px 0;">
                        <label style="font-size: 0.9rem; font-weight: bold; color: #16a34a;">NOTA DE DÉBITO ELECTRÓNICA</label>
                    </div>
                    <div class="document-number"><?php echo $product ? ($product->SERIE . "-" . $product->COMPROBANTE) : ""; ?></div>
                    <div class="badge-nota mt-2"><?php echo ($comp_cab && $comp_cab->codTipoNota == 1) ? "Intereses por mora" : "Nota de débito"; ?></div>
                </div>
            </div>

            <div class="customer-info border-bottom pb-3 mb-3">
                <span class="modifica-label">Documento que modifica</span>
                <div class="info-row">
                    <span class="info-label">FACTURA</span>
                    <span class="info-value"><?php echo ": " . ($comp_cab ? $comp_cab->serieDocModifica : ""); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">DOC. CLIENTE</span>
                    <span class="info-value"><?php echo ": " . ($comp_cab ? $comp_cab->numDocUsuario : ""); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">CLIENTE</span>
                    <span class="info-value"><?php echo ": " . ($comp_cab ? $comp_cab->rznSocialUsuario : ""); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">MOTIVO</span>
                    <span class="info-value text-bold"><?php echo ": " . ($comp_cab ? $comp_cab->descMotivo : ""); ?></span>
                </div>
            </div>

            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">CANT</th>
                        <th>DESCRIPCIÓN</th>
                        <th style="width: 80px;" class="text-right">P.U.</th>
                        <th style="width: 100px;" class="text-right">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $total = 0;
                        foreach ($detalles as $det) {
                            $total += $det->mtoValorVentaItem;
                    ?>
                    <tr>
                        <td class="quantity-cell text-center"><?php echo $det->ctdUnidadItem; ?></td>
                        <td class="description-cell"><?php echo $det->desItem; ?></td>
                        <td class="price-cell text-right"><?php echo number_format($det->mtoValorUnitario, 2); ?></td>
                        <td class="price-cell text-right"><?php echo number_format($det->mtoValorVentaItem, 2); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="totals-section">
                <div class="user-info">
                    <i class="fas fa-user-tie" style="color: #16a34a;"></i>
                    <div>
                        <div><strong>Cajero:</strong> <?php echo ($sell && isset($sell->user_id)) ? UserData::getById($sell->user_id)->username : ""; ?></div>
                        <small style="color: #666;">Fecha: <?php echo ($comp_cab && isset($comp_cab->fecEmision)) ? date("d/m/Y H:i", strtotime($comp_cab->fecEmision)) : ""; ?></small>
                    </div>
                </div>

                <div class="totals-table">
                    <table>
                        <tr><td>Op. Gratuita</td><td class="text-right">0.00</td></tr>
                        <tr><td>Op. Exonerada</td><td class="text-right"><?php echo number_format($total, 2); ?></td></tr>
                        <tr><td>Op. Inafecta</td><td class="text-right">0.00</td></tr>
                        <tr><td>Op. Gravada</td><td class="text-right">0.00</td></tr>
                        <tr><td>IGV (18%)</td><td class="text-right">0.00</td></tr>
                        <tr class="total-row" style="border-top: 2px solid #333;">
                            <td>TOTAL</td>
                            <td class="text-right">S/ <?php echo number_format($total, 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="text-center mt-4 pt-3 border-top small text-muted">
                <p>Representación impresa de la Nota de Débito Electrónica.<br>Consulte su validez en el portal de la SUNAT.</p>
            </div>
        </div>
    </div>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script>
        $('#imprimir').click(function() {
            window.print();
        });
    </script>
</body>
</html>


