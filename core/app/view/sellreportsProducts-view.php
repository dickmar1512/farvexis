<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fa fa-cart-plus'></i> Reporte de venta  x producto </h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Ventas</a></li>
					<li class="breadcrumb-item active">Reportes de Ventas x Producto</li>
				</ol>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<section class="content">
	<div class="container-fluid">
		<div class="card card-default">
			<div class="card-header">			
				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-8">
						<form>
							<input type="hidden" name="view" value="sellreportsProducts">
							<div class="row">
								<div class="col-md-3">
									<input type="date" name="sd"
										value="<?php if (isset($_GET["sd"])) {
											echo $_GET["sd"];
										} else {
											echo date("Y-m-d");
										} ?>"
										class="form-control">
								</div>
								<div class="col-md-3">
									<input type="date" name="ed"
										value="<?php if (isset($_GET["ed"])) {
											echo $_GET["ed"];
										} else {
											echo date("Y-m-d");
										} ?>"
										class="form-control">
								</div>
								<?php
								$usuario = UserData::getAll();
								?>
								<div class="col-md-2">
									<select name="userId" class="form-control">
										<option value="0">:: TODOS ::</option>
										<?php
										foreach ($usuario as $user) {
											?>
											<option value="<?php echo $user->id ?>"><?php echo $user->username ?></option>
											<?php
										}
										?>
									</select>
								</div>
								<div class="col-md-3">
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
								$total = 0;
								$tv = 0;
								$tc = 0;
								$admin = UserData::getById($_SESSION["user_id"])->is_admin;

								$operations = SellData::getAllByDateOpProductos($_GET["sd"], $_GET["ed"], 2, $_GET["userId"]);
								?>

								<?php if (count($operations) > 0): ?>
									<?php $supertotal = 0; ?>
									<table class="table table-bordered datatable" id="sellrepro">
										<thead class="thead-dark">
											<th>#</th>
											<th>Cantidad</th>
											<th>Producto</th>
											<th>P.Unit.</th>
											<th>Importe</th>
											<th>Fecha</th>
											<th>Comprobante</th>
											<th>Usuario</th>
										</thead>
										<?php $i = 1;
										foreach ($operations as $operation): 
											$usuario = UserData::getById($operation->user_id);
											$objProd = ProductData::getById($operation->product_id);
											$tc += $operation->q * $objProd->price_in;
										?>
											<tr>
												<td><?=$i++?></td>
												<td><?=$operation->q?></td>
												<td><?=$operation->prod?></td>
												<td><?=number_format($operation->prec_alt, 2, '.', ',')?></td>
												<td><?=number_format(($operation->q *$operation->prec_alt), 2, '.', ',')?></td>
												<td><?=$operation->created_at?></td>
												<td><?=$operation->serie . "-" . $operation->comprobante?>
												<td><?=$usuario->username?></td>
											</tr>
											<?php
											$total += number_format(($operation->q * $operation->prec_alt), 2, '.', ',');
										endforeach;										
										$tv += $total;
										?>
									</table>
									<table class="table table-bordered table-hover">
										<tr>
											<th colspan="3" style="text-align: right;">Total de ventas :</th>
											<th><?= number_format($total, 2, '.', ',') ?></th>
										</tr>
										<?php if ($admin == 1) { ?>
											<tr>
												<th colspan="3" style="text-align: right;">Total de capital :</th>
												<th><?=number_format($tc, 2, '.', ',') ?></th>
											</tr>
											<tr>
												<th colspan="3" style="text-align: right;">Total de Ganancia :</th>
												<th><?= number_format(($tv - $tc), 2, '.', ',') ?></th>
											</tr>
										<?php }
										?>
									</table>
									<div class="clearfix"></div>
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
									<p>Puede ser que no selecciono un rango de fechas, o el rango seleccionado es incorrecto.</p>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>					
	<br><br>
</section>