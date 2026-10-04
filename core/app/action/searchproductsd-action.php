<?php
/**
 * Buscador de productos para Salidas Diversas
 */
if(isset($_GET["product"]) && $_GET["product"] != ""):
    $products = ProductData::getLike(htmlspecialchars($_GET["product"], ENT_NOQUOTES, "UTF-8"));
    if(count($products) > 0):
?>
<div class="table-responsive">
    <table class="table table-hover table-sm table-valign-middle mb-0">
        <thead class="bg-light text-xs uppercase text-muted">
            <tr>
                <th class="pl-3">Producto</th>
                <th>Laboratorio</th>
                <th class="text-center">Stock Act.</th>
                <th class="text-right">Cantidad a Salir</th>
                <th class="text-right pr-3">Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($products as $product): ?>
            <tr class="text-sm">
                <td class="pl-3">
                    <div class="font-weight-bold"><?php echo $product->name; ?></div>
                    <div class="text-xs text-muted italic"><?php echo $product->principio_activo; ?></div>
                </td>
                <td><span class="badge badge-light border text-uppercase text-xs" style="font-weight: 500;"><?php echo $product->laboratorio; ?></span></td>
                <td class="text-center">
                    <span class="badge <?php echo ($product->stock <= $product->inventary_min) ? 'badge-danger' : 'badge-info'; ?>">
                        <?php echo $product->stock; ?>
                    </span>
                </td>
                <td class="text-right">
                    <input type="number" id="qty_<?php echo $product->id; ?>" class="form-control form-control-sm ml-auto" style="width: 80px;" value="1" min="1" max="<?php echo $product->stock; ?>">
                </td>
                <td class="text-right pr-3">
                    <button type="button" class="btn btn-xs btn-warning shadow-sm btn-add-sd" data-id="<?php echo $product->id; ?>" data-name="<?php echo $product->name; ?>">
                        <i class="fas fa-plus"></i> AGREGAR
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-light text-center py-3 m-0 border">
        <i class="fas fa-exclamation-triangle text-warning mr-2"></i> No se encontraron productos.
    </div>
<?php endif; endif; ?>
