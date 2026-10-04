<?php
if (isset($_GET["product"]) && $_GET["product"] != ""):
    $products = ProductData::getLikeSinStock($_GET["product"]);
    if (count($products) > 0): ?>
        <style>
            .res-card { border-radius: 8px; overflow: hidden; transition: transform 0.2s; }
            .res-card:hover { transform: translateY(-2px); }
            .input-group-custom .form-control { border-radius: 0; }
            .badge-stock { font-size: 0.85rem; padding: 5px 10px; }
        </style>
        <div class="card shadow res-card mb-4">
            <div class="card-header bg-gradient-info py-2">
                <h3 class="card-title text-sm font-weight-bold text-white text-uppercase">
                    <i class="fas fa-list-ul mr-2"></i> Resultados Encontrados (<?= count($products) ?>)
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr class="text-xs text-uppercase text-muted">
                                <th class="text-center" style="width: 40px;">#</th>
                                <th style="width: 20%;">Nombre / Presentación</th>
                                <th class="text-center">Stock Act.</th>
                                <th style="width: 110px;">Costo (S/)</th>
                                <th style="width: 70px;">Cant.</th>
                                <th style="width: 120px;">R.S.</th>
                                <th style="width: 100px;">Lote</th>
                                <th style="width: 130px;">Vencimiento</th>
                                <th>Laboratorio</th>
                                <th class="text-center" style="width: 110px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; foreach ($products as $product): $i++; $q = $product->stock; ?>
                                <tr class="row-re-product <?php echo ($q <= $product->inventary_min) ? 'bg-light-danger' : ''; ?>" data-id="<?php echo $product->id; ?>">
                                    <td class="text-center text-muted text-xs"><?= $i ?></td>
                                    <td>
                                        <div class="font-weight-bold text-primary"><?php echo $product->name; ?></div>
                                        <div class="text-xs text-muted"><?php echo $product->presentation ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php if($product->is_stock == 1): ?>
                                            <span class="badge badge-stock <?php echo ($q <= $product->inventary_min) ? 'badge-danger' : 'badge-light border'; ?>">
                                                <?php echo $q; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary badge-stock">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">S/</span></div>
                                            <input type="number" step="any" class="form-control font-weight-bold re-price-in" value="<?php echo number_format($product->price_in, 2, '.', ''); ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" step="any" class="form-control form-control-sm border-primary text-center font-weight-bold re-q" required placeholder="0">
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm text-xs re-rs" value="<?php echo $product->reg_san ?>" placeholder="R.S."></td>
                                    <td><input type="text" class="form-control form-control-sm text-xs re-nl" placeholder="Lote"></td>
                                    <td><input type="date" class="form-control form-control-sm text-xs re-fec-venc" value="<?= date('Y-m-d', strtotime('+1 year')) ?>"></td>
                                    <td><input type="text" class="form-control form-control-sm text-xs re-labo" value="<?php echo $product->laboratorio ?>" placeholder="Laborat."></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-success px-3 shadow-sm font-weight-bold btn-add-to-re">
                                            <i class="fas fa-plus mr-1"></i> AGREGAR
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="fas fa-exclamation-circle mr-2"></i> No se encontraron productos que coincidan con la búsqueda.
        </div>
    <?php endif;
endif; ?>
