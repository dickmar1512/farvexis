<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$subtotal_gravado = 0;
$igv_total = 0;
$subtotal_exonerado = 0;
$subtotal_inafecto = 0;
$subtotal_gratuito = 0;
$total = 0;
$dsctotal = 0;
$contador = 0;

$freeCodes = ['11','12','13','14','15','16','21','31','32','33','34','35','36','37'];
?>
<?php if (isset($_SESSION["cart"]) && count($_SESSION["cart"]) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-2">
            <thead class="thead-dark">
                <tr>
                    <th width="30" class="text-center">Nº</th>
                    <th width="60" class="text-center">CANT.</th>
                    <th>DESCRIPCIÓN</th>
                    <th width="85" class="text-center">AFECTACIÓN</th>
                    <th width="80" class="text-center">ANAQUEL</th>
                    <th width="80" class="text-right">P. UNIT.</th>
                    <th width="80" class="text-right">TOTAL</th>
                    <th width="40" class="text-center"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($_SESSION["cart"] as $index => $p):
                    $contador++;
                    $product = ProductData::getById($p["product_id"]);
                    $precio = ($product->is_may == 1) ? $product->price_may : (float)$p["precio_unitario"];
                    $q = (float)$p["q"];
                    $descuento_item = (float)($p["descuento"] ?? 0) * $q;
                    $dsctotal += $descuento_item;
                    
                    $igv_tipo = $p["igv_tipo"] ?? "20";
                    $isFree = in_array($igv_tipo, $freeCodes);

                    if ($isFree) {
                        $pt = 0.00;
                        $subtotal_gratuito += ($precio * $q);
                        $badge_imp = '<span class="badge badge-secondary" title="Transferencia Gratuita">GRATUITO</span>';
                    } elseif ($igv_tipo === '10') {
                        // Gravado 18% (Precio incluye IGV)
                        $valor_unit = $precio / 1.18;
                        $valor_venta = ($valor_unit * $q) - ($descuento_item / 1.18);
                        $igv_item = $valor_venta * 0.18;
                        $pt = $valor_venta + $igv_item;
                        $subtotal_gravado += $valor_venta;
                        $igv_total += $igv_item;
                        $badge_imp = '<span class="badge badge-primary" title="Gravado (18% IGV)">GRAVADO 18%</span>';
                    } elseif ($igv_tipo === '30') {
                        // Inafecto
                        $pt = ($precio * $q) - $descuento_item;
                        $subtotal_inafecto += $pt;
                        $badge_imp = '<span class="badge badge-info" title="Inafecto">INAFECTO</span>';
                    } else {
                        // Exonerado (20 u otros)
                        $pt = ($precio * $q) - $descuento_item;
                        $subtotal_exonerado += $pt;
                        $badge_imp = '<span class="badge badge-success" title="Exonerado">EXONERADO</span>';
                    }
                    ?>
                    <tr>
                        <td class="text-center bg-dark text-white"><?php echo $contador; ?></td>
                        <td class="text-center font-weight-bold"><?php echo round($q, 3); ?></td>
                        <td>
                            <div class="font-weight-bold text-dark"><?php echo $product->name . ' X ' . $product->presentation; ?></div>
                            <?php
                            $desc = ($product->description != "" && $product->description != "-") 
                                    ? $product->description . '-' . $product->laboratorio
                                    : (($p["descripcion"] != "" && $p["descripcion"] != "-") 
                                       ? $p["descripcion"] . '-' . $product->laboratorio : '');
                            if ($desc) echo "<small class=\"text-muted\">(" . htmlspecialchars($desc) . ")</small>";
                            ?>
                        </td>
                        <td class="text-center"><?php echo $badge_imp; ?></td>
                        <td class="text-center"><?= $product->anaquel ?></td>
                        <td class="text-right"><?php echo number_format($precio, 2, '.', ','); ?></td>
                        <td class="text-right font-weight-bold">
                            <?php echo number_format($pt, 2, '.', ','); ?>
                            <?php if ($isFree): ?><br><small class="text-muted">(Ref: <?= number_format($precio * $q, 2) ?>)</small><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button type="button" onclick="removeItem(<?= $product->id ?>)" class="btn btn-danger btn-sm" title="Eliminar">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php 
        $subtotal_gravado = round($subtotal_gravado, 2);
        $igv_total = round($igv_total, 2);
        $subtotal_exonerado = round($subtotal_exonerado, 2);
        $subtotal_inafecto = round($subtotal_inafecto, 2);
        $subtotal_gratuito = round($subtotal_gratuito, 2);
        $total = round($subtotal_gravado + $igv_total + $subtotal_exonerado + $subtotal_inafecto, 2); 
    ?>
    
    <!-- Desglose de Totales y Acciones -->
    <div class="total-section p-2 bg-light border rounded">
        <div class="row align-items-center">
            <div class="col-md-5">
                <div class="d-flex flex-wrap gap-2 text-muted small">
                    <?php if ($subtotal_gravado > 0): ?>
                        <span class="mr-3"><b>Gravado:</b> S/ <?= number_format($subtotal_gravado, 2) ?></span>
                        <span class="mr-3"><b>IGV (18%):</b> S/ <?= number_format($igv_total, 2) ?></span>
                    <?php endif; ?>
                    <?php if ($subtotal_exonerado > 0): ?>
                        <span class="mr-3"><b>Exonerado:</b> S/ <?= number_format($subtotal_exonerado, 2) ?></span>
                    <?php endif; ?>
                    <?php if ($subtotal_inafecto > 0): ?>
                        <span class="mr-3"><b>Inafecto:</b> S/ <?= number_format($subtotal_inafecto, 2) ?></span>
                    <?php endif; ?>
                    <?php if ($subtotal_gratuito > 0): ?>
                        <span class="mr-3 text-info"><b>Gratuito:</b> S/ <?= number_format($subtotal_gratuito, 2) ?></span>
                    <?php endif; ?>
                    <?php if ($dsctotal > 0): ?>
                        <span class="mr-3 text-danger"><b>Descuento:</b> S/ <?= number_format($dsctotal, 2) ?></span>
                    <?php endif; ?>
                </div>
                <div class="total-amount mt-1 text-success h5 font-weight-bold mb-0">
                    TOTAL A PAGAR: S/ <span id="display_total"><?php echo number_format($total, 2, '.', ','); ?></span>
                </div>
            </div>
            <div class="col-md-3 text-center mt-2 mt-md-0">
                <div class="form-group mb-2">
                    <label class="d-block mb-1">ENVÍO A SUNAT:</label>
                    <?php $envioSunatId = isset($TIPO) ? $TIPO : 'carrito'; ?>
                    <div>
                        <div class="icheck-primary d-inline mr-3"><input type="radio" id="envioSunatManual<?php echo $envioSunatId; ?>" name="envio_sunat" value="manual" checked><label for="envioSunatManual<?php echo $envioSunatId; ?>">Manual</label></div>
                        <div class="icheck-primary d-inline"><input type="radio" id="envioSunatAutomatico<?php echo $envioSunatId; ?>" name="envio_sunat" value="automatico"><label for="envioSunatAutomatico<?php echo $envioSunatId; ?>">Automático</label></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-right mt-2 mt-md-0">
                <button type="button" onclick="clearCart()" class="btn btn-danger btn-sm">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary btn-sm ml-2 btn-submit-form shadow-sm">
                    <i class="fa fa-check"></i> <span class="text-btn-submit">Emitir Comprobante</span> (F3)
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden values enviados a los formularios de venta -->
    <input type="hidden" name="total" class="input_total" value="<?php echo $total; ?>">
    <input type="hidden" class="js_total_val" value="<?php echo $total; ?>">
    <input type="hidden" name="subtotal_gravado" value="<?php echo $subtotal_gravado; ?>">
    <input type="hidden" name="igv_total" value="<?php echo $igv_total; ?>">
    <input type="hidden" name="subtotal_exonerado" value="<?php echo $subtotal_exonerado; ?>">
    <input type="hidden" name="subtotal_inafecto" value="<?php echo $subtotal_inafecto; ?>">
    <input type="hidden" name="subtotal_gratuito" value="<?php echo $subtotal_gratuito; ?>">

<?php else: ?>
    <div class="text-center">
        <div class="icon-container">
            <i class="fa fa-inbox icon-inbox"></i>
            <p class="text-muted">Carrito vacío - Agregue productos para continuar</p>
        </div>
    </div>
    <input type="hidden" name="total" class="input_total" value="0">
    <input type="hidden" class="js_total_val" value="0">
    <input type="hidden" name="subtotal_gravado" value="0">
    <input type="hidden" name="igv_total" value="0">
    <input type="hidden" name="subtotal_exonerado" value="0">
    <input type="hidden" name="subtotal_inafecto" value="0">
    <input type="hidden" name="subtotal_gratuito" value="0">
<?php endif; ?>
