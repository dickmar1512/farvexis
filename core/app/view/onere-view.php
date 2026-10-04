<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='bx bxs-shopping-bag'></i> Reporte de Compras</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Reportes</a></li>
					<li class="breadcrumb-item active">Registro Compra</li>
				</ol>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<!-- Main content -->
<section class="content">
	<div class="container-fluid col-md-8">
		<div class="card card-default">
			<div class="card-header">
				<div class="row">
					<div class="col-md-2">
						<a class="nav-link" onclick="goBack()" data-widget="pushmenu" href="#" role="button">
							<i class="fas fa-arrow-left"></i> Volver Atrás
						</a>
					</div>
					<div class="col-md-6">
						<h4> Resumen de Reabastecimiento</h4>
					</div>
				</div>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-12">
						<?php if (isset($_GET["id"]) && $_GET["id"] != ""): ?>
							<?php
							$sell = SellData::getById($_GET["id"]);
							$operations = OperationData::getAllProductsBySellId($_GET["id"]);
							$total = 0;
							?>
							<?php
							if (isset($_COOKIE["selled"])) {
								foreach ($operations as $operation) {
									$qx = OperationData::getQYesF($operation->product_id);
									$p = $operation->getProduct();
									if ($qx == 0) {
										echo "<p class='alert alert-danger'>El producto <b style='text-transform:uppercase;'> $p->name</b> no tiene existencias en inventario.</p>";
									} else if ($qx <= $p->inventary_min / 2) {
										echo "<p class='alert alert-danger'>El producto <b style='text-transform:uppercase;'> $p->name</b> tiene muy pocas existencias en inventario.</p>";
									} else if ($qx <= $p->inventary_min) {
										echo "<p class='alert alert-warning'>El producto <b style='text-transform:uppercase;'> $p->name</b> tiene pocas existencias en inventario.</p>";
									}
								}
								setcookie("selled", "", time() - 18600);
							}

							?>
							<table class="table table-bordered">
								<?php if ($sell->person_id != ""):
									$client = $sell->getPerson();
									?>
									<tr>
										<td style="width:150px;">Proveedor</td>
										<td><?php echo $client->name . " " . $client->lastname; ?></td>
									</tr>

								<?php endif; ?>
								<tr>
									<td>Comprobante</td>
									<td><?= $sell->serie . "-" . $sell->comprobante ?></td>
								</tr>
								<?php if ($sell->user_id != ""):
									$user = $sell->getUser();
									?>
									<tr>
										<td>Atendido por</td>
										<td><?php echo $user->name . " " . $user->lastname; ?></td>
									</tr>
								<?php endif; ?>
							</table>
							<table class="table table-bordered table-hover">
								<thead class="thead-dark">
									<th>Codigo</th>
									<th>Cantidad</th>
									<th>Nombre del Producto</th>
									<th>Precio Unitario</th>
									<th>Total</th>
								</thead>
								<?php
								foreach ($operations as $operation) {
									$product = $operation->getProduct();
									?>
									<tr>
										<td><?php echo $product->barcode; ?></td>
										<td style="text-align: center;"><?php echo $operation->q; ?></td>
										<td><?php echo $product->name; ?></td>
										<td style="text-align: right;">
											<?php echo number_format($product->price_in, 2, ".", ","); ?>
										</td>
										<td style="text-align: right;"><b>
												<?php echo number_format($operation->q * $product->price_in, 2, ".", ",");
												$total += $operation->q * $product->price_in; ?></b>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<th colspan="4" style="text-align: right;">
										<h4>Total:</h4>
									</th>
									<th>
										<h4><?php echo number_format($total, 2, '.', ','); ?></h4>
									</th>
								</tr>
							</table>

							<?php

							?>
						<?php else: ?>
							501 Internal Error
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>