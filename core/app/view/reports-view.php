<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fa fa-shopping-cart'></i> Inventarios</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Inventario</a></li>
					<li class="breadcrumb-item active">Reportes de Inventario</li>
				</ol>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<div class="card card-default">
			<div class="card-header">
				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-10">
						<form>
							<input type="hidden" name="view" value="reports">
							<div class="row">
								<div class="col-md-4">
									<select name="product_id" id="product_id" class="form-control select2bs4 col-md-12">
										<option value="">Cargando productos...</option>
									</select>
									<div id="loading" class="mt-2" style="display: none;">
										<small class="text-muted">Cargando...</small>
									</div>
								</div>
								<div class="col-md-3">
									<input type="date" name="sd" value="<?php if (isset($_GET["sd"])) {
										echo $_GET["sd"];
									} else {
										echo date("Y-m-d");
									} ?>" class="form-control">
								</div>
								<div class="col-md-3">
									<input type="date" name="ed" value="<?php if (isset($_GET["ed"])) {
										echo $_GET["ed"];
									} else {
										echo date("Y-m-d");
									} ?>" class="form-control">
								</div>

								<div class="col-md-2">
									<input type="submit" class="btn btn-success btn-block" value="Procesar">
								</div>

							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-10">
						<?php if (isset($_GET["sd"]) && isset($_GET["ed"])): ?>
							<?php if ($_GET["sd"] != "" && $_GET["ed"] != ""): ?>
								<?php
								$operations = array();
								$cont = 1;

								$operations = OperationData::getAllMovByDateProductId($_GET["product_id"], $_GET["sd"], $_GET["ed"]);
								?>

								<?php if (count($operations) > 0): ?>
									<table class="table table-bordered datatable">
										<thead class="thead-dark">
											<tr>
												<th rowspan="2">#</th>
												<th rowspan="2">Producto</th>
												<th colspan="2">Entrada</th>
												<th colspan="2">Salida</th>
												<th colspan="2">Inventario</th>
											</tr>
											<tr>
												<th>Stock</th>
												<th>Importe</th>
												<th>Stock</th>
												<th>Importe</th>
												<th>Stock</th>
												<th>Importe</th>	
											</tr>
										</thead>
										<?php foreach ($operations as $operation): ?>
											<tr>
												<td><?=$cont++ ?></td>
												<td><?=$operation->getProduct()->name ?></td>
												<td style="text-align: center;"><?=$operation->stock_in ?></td>
												<td style="text-align: right;"><?=number_format($operation->importe_in,2,'.',',') ?></td>
												<td style="text-align: center;"><?=$operation->stock_out; ?></td>
												<td style="text-align: right;"><?=number_format($operation->importe_out,2,'.',',') ?></td>
												<td style="text-align: center;"><?=$operation->getProduct()->stock; ?></td>
												<td style="text-align: right;"><?=number_format(($operation->getProduct()->price_in*$operation->getProduct()->stock),2,'.',',') ?></td>
											</tr>
										<?php endforeach; ?>

									</table>

								<?php else:
									// si no hay operaciones
									?>
									<script>
										$("#wellcome").hide();
									</script>
									<div class="jumbotron">
										<h2>No hay operaciones</h2>
										<p>El rango de fechas seleccionado no proporciono ningun resultado de operaciones.</p>
									</div>

								<?php endif; ?>
							<?php else: ?>
								<script>
									$("#wellcome").hide();
								</script>
								<div class="jumbotron">
									<h2>Fecha Incorrectas</h2>
									<p>Puede ser que no selecciono un rango de fechas, o el rango seleccionado es
										incorrecto.</p>
								</div>
							<?php endif; ?>

						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>