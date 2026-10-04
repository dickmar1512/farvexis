<?php
/**
 * Action para obtener la lista de productos de un kit en formato HTML para AJAX
 */
if(isset($_GET["id"])):
    $id = $_GET["id"];
    $detkit = Det_kit::getById($id);
?>
<div class="table-responsive">
    <table class="table table-hover table-sm table-valign-middle mb-0">
        <thead class="bg-light text-xs uppercase text-muted">
            <tr>
                <th class="pl-3" style="width: 50px;">#</th>
                <th>Producto</th>
                <th class="text-center">Cant.</th>
                <th class="text-right">Precio</th>
                <th class="text-right">Desc.</th>
                <th class="text-right pr-3">Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($detkit) > 0): ?>
                <?php 
                $i = 1;
                foreach ($detkit as $detalle): ?>
                    <tr class="text-sm">
                        <td class="pl-3 font-weight-bold text-muted"><?php echo $i++; ?></td>
                        <td><?php echo $detalle->name; ?></td>
                        <td class="text-center font-weight-bold"><?php echo $detalle->cantidad; ?></td>
                        <td class="text-right font-weight-bold text-primary">S/ <?php echo number_format($detalle->precio, 2); ?></td>
                        <td class="text-right text-danger">S/ <?php echo number_format($detalle->descuento, 2); ?></td>
                        <td class="text-right pr-3">
                            <button type="button" class="btn btn-xs btn-outline-danger shadow-sm btn-del-det" data-id="<?php echo $detalle->iddetalle; ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted italic">
                        <i class="fas fa-info-circle mr-1"></i> No hay productos agregados a este kit.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
