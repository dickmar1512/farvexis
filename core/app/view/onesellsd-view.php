<?php
if(isset($_GET["id"])):
$sell = SellData::getById($_GET["id"]);
$operations = OperationData::getAllProductsBySellId($_GET["id"]);
$user = $sell->getUser();
?>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-file-alt text-warning mr-2"></i> Detalle de Salida Diversa</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="./?view=salidasdiversas" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold uppercase">Información General</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Documento:</b> <span class="float-right font-weight-bold text-dark"><?php echo $sell->serie . "-" . $sell->comprobante; ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Fecha/Hora:</b> <span class="float-right"><?php echo date("d/m/Y H:i", strtotime($sell->created_at)); ?></span>
                            </li>
                            <li class="list-group-item border-0">
                                <b>Responsable:</b> <span class="float-right text-muted"><?php echo $user->name . " " . $user->lastname; ?></span>
                            </li>
                        </ul>
                        <hr>
                        <div class="bg-light p-3 rounded border">
                            <label class="text-xs font-weight-bold uppercase text-muted mb-1 d-block">Motivo de la Baja:</label>
                            <p class="mb-0 text-sm italic"><?php echo $sell->observacion; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm font-weight-bold uppercase">Productos Dados de Baja</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm table-valign-middle mb-0">
                                <thead class="bg-light text-xs uppercase text-muted border-top-0">
                                    <tr>
                                        <th class="pl-4">Cod. Barra</th>
                                        <th>Producto</th>
                                        <th>Laboratorio</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-right pr-4">Costo Ref.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_ref = 0;
                                    foreach($operations as $op): 
                                        $product = $op->getProduct();
                                        $subtotal = $op->q * $op->cu;
                                        $total_ref += $subtotal;
                                    ?>
                                    <tr class="text-sm">
                                        <td class="pl-4 text-xs"><?php echo $product->barcode; ?></td>
                                        <td class="font-weight-bold"><?php echo $product->name; ?></td>
                                        <td><span class="text-xs text-muted text-uppercase"><?php echo $product->laboratorio; ?></span></td>
                                        <td class="text-center">
                                            <span class="badge badge-warning"><?php echo $op->q; ?></span>
                                        </td>
                                        <td class="text-right pr-4 text-muted">
                                            S/ <?php echo number_format($subtotal, 2); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right font-weight-bold text-xs uppercase">Costo Total Referencial de la Baja:</td>
                                        <td class="text-right pr-4 font-weight-bold text-danger">S/ <?php echo number_format($total_ref, 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
