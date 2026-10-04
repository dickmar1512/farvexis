<style>
	#status {
		padding: 10px;
		background: #88C4FF;
		color: #000;
		font-weight: bold;
		font-size: 12px;
		margin-bottom: 10px;
		display: none;
		width: 90%;
	}
</style>
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
	<div class="container-fluid">
		<div class="card card-default">
			<div class="card-header">
				<div class="row">
					<div class="col-md-2">
						<a class="nav-link" onclick="goBack()" data-widget="pushmenu" href="#" role="button">
							<i class="fas fa-arrow-left"></i> Volver Atrás
						</a>
					</div>
					<div class="col-md-4">
						<h4>Editar Reabastecimiento</h4>
					</div>
				</div>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-10">
						<?php if (isset($_GET["id"]) && $_GET["id"] != ""): ?>
							<?php
							$sell = SellData::getById($_GET["id"]);
							$operations = OperationData::getAllProductsBySellId($_GET["id"]);
							$total = 0;
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
							<table class="table table-striped">
								<thead class="thead-dark">
									<th scope="col">#</th>
									<th scope="col">Codigo</th>
									<th scope="col">Cantidad</th>
									<th scope="col">Nombre del Producto</th>
									<th scope="col">Prec. Unit.</th>
									<th scope="col">Total</th>
								</thead>
								<?php
								$fila = 0;
								foreach ($operations as $operation) {
									$fila++;
									$product = $operation->getProduct();
									?>
									<tr>
										<td scope="row"><?= $fila/*."-".$product->id*/ ?></td>
										<td id="barcode:<?= $product->id ?>"><?php echo $product->barcode; ?></td>
										<td id="q:<?= $product->id . ":" . $operation->sell_id . ":" . $operation->q ?>"
											contenteditable="true">
											<?php echo $operation->q; ?>
										</td>
										<td id="name:<?= $product->id . ":" . $operation->sell_id ?>">
											<?php echo $product->name; ?>
										</td>
										<td id="prec_alt:<?= $product->id . ":" . $operation->sell_id . ":" . $operation->q ?>"
											contenteditable="true">
											<?php echo number_format($product->price_in, 2, ".", ","); ?>
										</td>
										<td id="total"><b><?php echo number_format($operation->q * $product->price_in, 2, ".", ",");
										$total += $operation->q * $product->price_in; ?></b></td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td colspan="4">
										<div id="status"></div>
									</td>
									<td style="text-align: right;">
										<h4>Total:</h4>
									</td>
									<td>
										<h4><?= number_format($total, 2, '.', ',') ?></h4>
									</td>
								</tr>
								<tr>
									<td colspan="6"><p class="text-primary"><b>** Los campos editables son Cantidad y Precio Unitario, para editarlos solo has click en el campo a editar y digita el valor deseado.**</b> </p></td>
								</tr>
							</table>
						<?php else: ?>
							501 Internal Error
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">
	$(function () {
		$("td[contenteditable=true]").blur(function () {
			var field_id = $(this).attr("id");
			var value = $(this).text().trim();
			
			// Si el valor está vacío o no es un número válido, se puede validar aquí
			if(value === "") return;

			$.post("./?action=updateres", field_id + "=" + value, function (data) {
				$("#status").html(data);
				$("#status").fadeIn();
				setTimeout(function() {
					location.reload();
				}, 1500);
			}).fail(function() {
				$("#status").html("<span class='text-danger'>Error al guardar los cambios</span>");
				$("#status").fadeIn();
			});
		});

		// Manejar Enter para quitar el foco
		$("td[contenteditable=true]").keypress(function (e) {
			if (e.which == 13) {
				e.preventDefault();
				$(this).blur();
			}
		});
	});
</script>