<?php
$cart = $_SESSION["cart_sd"] ?? array();
?>
<div class="table-responsive">
    <table class="table table-hover table-sm table-valign-middle mb-0">
        <thead class="bg-light text-xs uppercase text-muted">
            <tr>
                <th class="pl-4">Producto</th>
                <th>Laboratorio</th>
                <th class="text-center" style="width: 120px;">Cantidad</th>
                <th class="text-right pr-4" style="width: 100px;">Quitar</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($cart) > 0): ?>
                <?php foreach($cart as $item): ?>
                <tr class="text-sm">
                    <td class="pl-4 font-weight-bold"><?php echo $item["name"]; ?></td>
                    <td><span class="text-xs text-muted text-uppercase"><?php echo $item["laboratorio"] ?? '-'; ?></span></td>
                    <td class="text-center">
                        <span class="badge badge-warning py-1 px-3" style="font-size: 14px;">
                            <?php echo $item["q"]; ?>
                        </span>
                    </td>
                    <td class="text-right pr-4">
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-sd" data-id="<?php echo $item["product_id"]; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted italic">
                        No hay productos en la lista
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
