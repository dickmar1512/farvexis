<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$total = 0;
?>

<?php if (isset($_SESSION["reabastecer"]) && count($_SESSION["reabastecer"]) > 0): ?>
    <!-- Lista de Productos -->
    <div class="card card-outline card-primary shadow-sm mt-3">
        <div class="card-header py-2 bg-primary">
            <h3 class="card-title text-white"><i class="fas fa-shopping-basket mr-2"></i> PRODUCTOS A INGRESAR</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-striped mb-0">
                    <thead class="bg-light text-uppercase" style="font-size: 0.8rem;">
                        <tr>
                            <th>Descripción del Producto</th>
                            <th>Laboratorio</th>
                            <th class="text-center">Reg. Sanitario</th>
                            <th class="text-center">F. Fabr.</th>
                            <th class="text-center">Vencimiento</th>
                            <th class="text-center" style="width: 60px;">Cant.</th>
                            <th class="text-center">Lote</th>
                            <th class="text-right">P. Unit (S/)</th>
                            <th class="text-right">Total (S/)</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION["reabastecer"] as $p): 
                            $product = ProductData::getById($p["product_id"]);
                            $pt = $p["price_in"] * $p["q"];
                            $total += $pt;
                        ?>
                            <tr style="font-size: 0.9rem;">
                                <td><?php echo $product->name; ?></td>
                                <td><?php echo $p["labo"]; ?></td>
                                <td class="text-center"><span class="badge badge-secondary"><?= $p["rs"] ?></span></td>
                                <td class="text-center"><?= $p["fec_fab"] ?? '-' ?></td>
                                <td class="text-center"><?php echo $p["fec_venc"] ?></td>
                                <td class="text-center"><b><?php echo $p["q"]; ?></b></td>
                                <td class="text-center"><span class="badge badge-info"><?= $p["nl"] ?></span></td>
                                <td class="text-right font-weight-bold"><?php echo number_format($p["price_in"], 2, '.', ','); ?></td>
                                <td class="text-right text-primary font-weight-bold"><?php echo number_format($pt, 2, '.', ','); ?></td>
                                <td class="text-center">
                                    <button type="button" onclick="removeItemRe(<?= $product->id ?>)" class="btn btn-xs btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
            <div>
                <a href="#" onclick="limpiarCartRe()" class="btn btn-danger btn-sm shadow-sm">
                    <i class="fas fa-times mr-1"></i> VACIAR CARRITO
                </a>
            </div>
            <h4 class="mb-0">TOTAL COMPRA: <span class="text-success font-weight-bold">S/ <?php echo number_format($total, 2, '.', ','); ?></span></h4>
        </div>
    </div>
    
    <input type="hidden" name="total" value="<?php echo $total; ?>">
    <script>
        $(document).ready(function() {
            // Sincronizar el total pagado con el total
            $('input[name="money"]').val('<?php echo $total; ?>');
        });
    </script>
<?php else: ?>
    <div class="card shadow-sm mt-3 border-0 bg-light-info">
        <div class="card-body text-center py-5">
            <div class="text-muted mb-3"><i class="fas fa-shopping-basket fa-4x opacity-2 text-info"></i></div>
            <h5 class="text-info font-weight-bold">LISTA DE REABASTECIMIENTO VACÍA</h5>
            <p class="text-muted">Haga clic en el botón verde arriba para buscar y agregar productos al almacén.</p>
        </div>
    </div>
    <input type="hidden" name="total" value="0">
<?php endif; ?>
