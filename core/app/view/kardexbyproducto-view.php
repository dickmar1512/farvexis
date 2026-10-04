<?php
	$categories = CategoryData::getAll();
	$productos = ProductData::getAll();
	$sd = (isset($_GET["sd"])) ? $_GET["sd"] : date("Y-m-d");
	$ed = (isset($_GET["ed"])) ? $_GET["ed"] : date("Y-m-d");
?>
<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fa fa-file-excel-o'></i> Kardex Productos</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Inventarios</a></li>
					<li class="breadcrumb-item active">Kardex</li>
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
					<div class="col-md-12">
						<form class="text-center">
							<input type="hidden" name="view" value="kardexbyproducto">
							<div class="row" style="display: flex; justify-content: center;">
								<div class="col-md-2">
									<select name="selCategory" class="form-control">
										<option value="">TODOS</option>
										<?php 
											foreach($categories as $c)
											{
												?>
													<option value="<?php echo $c->id;?>"><?php echo $c->name;?></option>
												<?php
											}
										?>
									</select>
								</div>
								<div class="col-md-3" id="productsbycategory">
										<select name="selProduct" class="form-control select2bs4">
											<option value="T">Seleccione</option>
											<?php foreach($productos as $p):?>
											<option value="<?php echo $p->id;?>"><?php echo $p->name;?></option>
											<?php endforeach; ?>
										</select>
								</div>					
								<div class="col-md-2">
									<input type="date" name="sd" value="<?=$sd?>" class="form-control">
								</div>
								<div class="col-md-2">
									<input type="date" name="ed" value="<?=$ed?>" class="form-control">
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
						<div class="col-md-12">
						<?php if(isset($_GET["sd"]) && isset($_GET["ed"]) ):?>
						<?php 	if($_GET["sd"]!=""&&$_GET["ed"]!=""):?>
								<?php 
									$operations = array();

									if($_GET["selProduct"]=="T")
									{
										$operations = SellData::getAllByKardex($_GET["sd"],$_GET["ed"]);
									}
									else
									{
										$operations = SellData::getAllByKardexProd($_GET["selProduct"],$_GET["sd"],$_GET["ed"],2);
									}

									//echo json_encode($operations);
								?>
							<?php 	if(count($operations)>0):?>
								<?php $supertotal = 0; ?>
									<table class="table table-bordered datatable" >
										<thead class="thead-dark">
											<th>Cant.</th>
											<th>Producto</th>
											<th>Precio Unit.</th>
											<th>Descuento</th>
											<th>Costo Unit.</th>
											<th>Comprobante</th>
											<th>Cliente/Proveedor</th>
											<th>Tipo Ope.</th>
											<th>Fecha Ope.</th>
										</thead>
								<?php foreach($operations as $operation):?>
										<tr>
											<td><?=$operation->q?></td>
											<td><?=$operation->prod?></td>
											<td><?=number_format($operation->prec_alt,2,'.',',')?></td>
											<td><?=$operation->descuento?></td>
											<td><?=number_format($operation->cu,2,'.',',')?></td>
											<td><?=$operation->comp?></td>
											<td><?=$operation->nombre?></td>
											<td><?=$operation->tipo?></td>
											<td><?=$operation->created_at; ?></td>
										</tr>
								<?php
									$supertotal+= ($operation->total-$operation->discount);
									endforeach; ?>
									</table>
									<!-- <h1>Total de ventas: S/ <?php echo number_format($supertotal,2,'.',','); ?></h1> -->
							<?php else:						
							?>
									<script>
										$("#wellcome").hide();
									</script>
									<div class="jumbotron">
										<h2>No hay operaciones</h2>
										<p>El rango de fechas seleccionado no proporciono ningun resultado de operaciones.</p>
									</div>

							<?php endif; ?>
						<?php else:?>
								<script>
									$("#wellcome").hide();
								</script>
								<div class="jumbotron">
									<h2>Fecha Incorrectas</h2>
									<p>Puede ser que no selecciono un rango de fechas, o el rango seleccionado es incorrecto.</p>
								</div>
						<?php endif;?>

						<?php endif; ?>
						</div>
					</div>
			</div>
			<!-- /.card-body -->
			 </div>
			 </div>
		</div>
	</div>
</section>