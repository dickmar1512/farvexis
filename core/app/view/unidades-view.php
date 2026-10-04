<?php
$unidades = UnidadMedidaData::getAll();
?>
<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fa fa-th-list'></i> Unidad de medida</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Catalogos</a></li>
					<li class="breadcrumb-item active">Unidades</li>
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
					<div class="col-md-6">
						<h4>Lista unidades</h4>
					</div>
					<div class="col-md-6" style="display: flex; justify-content: right;">
						<div class="btn-group float-sm-right">
							<button id="openModalAgregarUnidad" class="btn btn-primary">
								<i class='fas fa-plus'></i>
								Agregar
							</button>
						</div>
					</div>
				</div>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-12">
						<?php if (count($unidades) > 0): ?>
							<table class="table table-bordered table-hover datatable">
								<thead class="thead-dark">
									<th>Nombre</th>
									<th>Sigla</th>
									<th>Acciones</th>
								</thead>
								<?php foreach ($unidades as $unidad): ?>
									<tr>
										<td><b><?php echo $unidad->name; ?></b></td>
										<td><b><?php echo $unidad->sigla; ?></b></td>
										<td style="width:90px;">
											<button data-id="<?= $unidad->id ?>"
												class="btn btn-warning btn-sm edit-unidad" title="Editar Unidad">
												<i class="fas fa-pencil-alt"></i>
											</button>
											<!-- <a href="./?view=hidecategory&id=<?php echo $unidad->id; ?>" id="del-<?php echo $unidad->id; ?>" class="btn btn-sm btn-danger hide">
														<i class='fa fa-power-off img-circle'></i>
													</a> -->
										</td>
									</tr>
								<?php endforeach; ?>
							</table>
						<?php else: ?>
							<div class="callout callout-info">
								<h2><i class="fas fa-minus-circle"></i> No hay grupo de unidades registrados
								</h2>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>