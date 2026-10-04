<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='bx bxs-trash-alt'></i> Reporte de Compras Anuladas</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Reportes</a></li>
					<li class="breadcrumb-item active">Compras Anuladas</li>
				</ol>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<!-- Main content -->
<section class="content">
	<div class="container-fluid col-md-8">
		<div class="card card-danger card-outline">
			<div class="card-header">
				<h3 class="card-title">Historial de Reabastecimientos Anulados</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<form class="form-horizontal" role="form">
					<input type="hidden" name="view" value="resanuladas">
					<div class="row mb-3">
						<div class="col-md-3">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="fas fa-calendar"></i></span>
								</div>
								<input type="date" name="sd" value="<?php echo isset($_GET["sd"]) && $_GET["sd"] != "" ? $_GET["sd"] : date("Y-m-01"); ?>" class="form-control" placeholder="Fecha Inicio">
							</div>
						</div>
						<div class="col-md-3">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="fas fa-calendar"></i></span>
								</div>
								<input type="date" name="ed" value="<?php echo isset($_GET["ed"]) && $_GET["ed"] != "" ? $_GET["ed"] : date("Y-m-d"); ?>" class="form-control" placeholder="Fecha Fin">
							</div>
						</div>
						<div class="col-md-3">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="fas fa-user"></i></span>
								</div>
								<?php
								$users = UserData::getAll();
								?>
								<select name="user_id" class="form-control">
									<option value="0">-- TODOS LOS USUARIOS --</option>
									<?php foreach ($users as $u): ?>
										<option value="<?php echo $u->id; ?>" <?php if (isset($_GET["user_id"]) && $_GET["user_id"] == $u->id) { echo "selected"; } ?>><?php echo $u->name . " " . $u->lastname; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
							<a href="./?view=resanuladas" class="btn btn-secondary"><i class="fas fa-sync"></i> Limpiar</a>
						</div>
					</div>
				</form>

				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-11">
						<?php
						$start = isset($_GET["sd"]) && $_GET["sd"] != "" ? $_GET["sd"] : date("Y-m-01");
						$end = isset($_GET["ed"]) && $_GET["ed"] != "" ? $_GET["ed"] : date("Y-m-d");						
						$user_id = isset($_GET["user_id"]) && $_GET["user_id"] != "" ? $_GET["user_id"] : 0;

						$products = SellData::getResAnuladas($start, $end, $user_id);
						$admin = UserData::getById($_SESSION["user_id"])->is_admin;

						if (count($products) > 0) {
							?>
							<br>
							<table class="table table-bordered table-hover datatable table-sm">
								<thead class="thead-dark">
									<th>Ver</th>
									<th>Comprobante</th>
									<th>Items</th>
									<th>Imp. Total</th>
									<th>Usuario</th>
									<th>Fecha Anulación</th>
								</thead>
								<?php foreach ($products as $sell): 
										$fechaObj = new DateTime($sell->created_at);
										$fechaFormateada = $fechaObj->format('d/m/Y H:i:s');
										$total = 0;
									?>
									<tr class="text-muted">
										<td style="width:30px;"><a href="<?= url('onere', ['id' => $sell->id]) ?>"
												class="btn btn-xs btn-default"><i class="fas fa-eye"></i></a>
										</td>
										<td><span class="badge badge-secondary"><?= $sell->serie . "-" . $sell->comprobante ?></span></td>
										<td style="text-align: center;">
											<?php
											$operations = OperationData::getAllProductsBySellIdAll($sell->id);
											echo count($operations);
											?>
										</td>	
										<td style="text-align: right;">
											<?php
											foreach ($operations as $operation) {
												$product = $operation->getProduct();
												$total += $operation->q * $product->price_in;
											}
											echo "<b> " . number_format($total,2,'.',',') . "</b>";
											?>
										</td>
										<td>
											<?php 
											if($sell->user_id != ""){
												$u = UserData::getById($sell->user_id);
												echo $u->name." ".$u->lastname;
											}
											?>
										</td>
										<td><?=$fechaFormateada?></td>
									</tr>
								<?php endforeach; ?>
							</table>
							<?php
						} else {
							?>
							<div class="alert alert-info">
								<h5><i class="icon fas fa-info"></i> Sin registros</h5>
								No se han encontrado compras anuladas en el sistema.
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
