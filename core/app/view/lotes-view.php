<?php
$statusFilter = trim($_GET['estado'] ?? '');
$productFilter = (int)($_GET['product_id'] ?? 0);
$lotes = LoteData::getAll($statusFilter, $productFilter);
$products = ProductData::getAll();
$successMessage = $_SESSION['lote_success'] ?? null;
$errorMessage = $_SESSION['lote_error'] ?? null;
unset($_SESSION['lote_success'], $_SESSION['lote_error']);

function loteEsc($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-layer-group text-primary mr-2"></i>Administración de lotes</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="./?view=inventary">Inventario</a></li>
                    <li class="breadcrumb-item active">Lotes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if ($successMessage): ?><div class="alert alert-success"><?= loteEsc($successMessage) ?></div><?php endif; ?>
        <?php if ($errorMessage): ?><div class="alert alert-danger"><?= loteEsc($errorMessage) ?></div><?php endif; ?>

        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filtros</h3></div>
            <div class="card-body">
                <form method="get" class="row align-items-end">
                    <input type="hidden" name="view" value="lotes">
                    <div class="col-md-4 form-group mb-md-0">
                        <label>Producto</label>
                        <select name="product_id" class="form-control select2bs4">
                            <option value="0">Todos</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= (int)$product->id ?>" <?= $productFilter === (int)$product->id ? 'selected' : '' ?>><?= loteEsc($product->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-md-0">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach (['cuarentena', 'disponible', 'bloqueado', 'vencido', 'agotado', 'retirado'] as $status): ?>
                                <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 form-group mb-md-0"><button class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar</button></div>
                    <div class="col-md-2 form-group mb-md-0"><a href="./?view=lotes" class="btn btn-outline-secondary btn-block">Limpiar</a></div>
                </form>
            </div>
        </div>

        <div class="card card-success card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-boxes mr-1"></i><?= count($lotes) ?> lotes</h3></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover datatable" style="width:100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>Producto</th><th>Lote</th><th>F. Fabr.</th><th>Vencimiento</th><th>Ingreso</th>
                            <th class="text-right">Inicial</th><th class="text-right">Disponible</th>
                            <th>Estado</th><th>Ubicación</th><th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lotes as $lote):
                        $expiry = $lote['fecha_vencimiento'] ?? null;
                        $expired = $expiry && $expiry < date('Y-m-d') && !in_array($lote['estado'], ['agotado', 'retirado'], true);
                        $badge = $expired ? 'danger' : ($lote['estado'] === 'disponible' ? 'success' : 'secondary');
                    ?>
                        <tr class="<?= $expired ? 'table-danger' : '' ?>">
                            <td><strong><?= loteEsc($lote['product_name']) ?></strong><br><small class="text-muted"><?= loteEsc($lote['barcode']) ?></small></td>
                            <td><?= loteEsc($lote['num_lot']) ?></td>
                            <td><?= !empty($lote['fecha_fabricacion']) ? loteEsc($lote['fecha_fabricacion']) : 'Sin fecha' ?></td>
                            <td><?= $expiry ? loteEsc($expiry) : 'Sin fecha' ?></td>
                            <td><?= loteEsc($lote['fech_ing']) ?></td>
                            <td class="text-right"><?= number_format((float)$lote['cantidad_inicial'], 3) ?></td>
                            <td class="text-right font-weight-bold"><?= number_format((float)$lote['cantidad_disponible'], 3) ?></td>
                            <td><span class="badge badge-<?= $badge ?>"><?= loteEsc($expired ? 'vencido' : $lote['estado']) ?></span></td>
                            <td><?= loteEsc($lote['ubicacion']) ?></td>
                            <td class="text-center">
                                <?php if ($lote['estado'] === 'disponible'): ?>
                                    <form method="post" action="./?action=update_lote_status" class="d-inline">
                                        <input type="hidden" name="id" value="<?= (int)$lote['id'] ?>"><input type="hidden" name="estado" value="bloqueado">
                                        <button class="btn btn-xs btn-outline-warning" title="Bloquear lote"><i class="fas fa-lock"></i></button>
                                    </form>
                                <?php elseif (in_array($lote['estado'], ['bloqueado', 'cuarentena'], true)): ?>
                                    <form method="post" action="./?action=update_lote_status" class="d-inline">
                                        <input type="hidden" name="id" value="<?= (int)$lote['id'] ?>"><input type="hidden" name="estado" value="disponible">
                                        <button class="btn btn-xs btn-outline-success" title="Liberar lote"><i class="fas fa-unlock"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
