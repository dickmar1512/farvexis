<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fa fa-truck'></i> Lista de proveedores</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Administración</a></li>
					<li class="breadcrumb-item active">Lista Proveedores</li>
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
					<div class="col-md-6">
						<h4>Directorio de Proveedores</h4>
					</div>
					<div class="col-md-6"  style="display: flex; justify-content: right;">
						<div class="btn-group float-sm-right">
							<button id="openModalNuevoProveedor" class="btn btn-primary">
								<i class='fa fa-truck'></i>
									Nuevo Proveedor
							</button>
						</div>
					</div>
				</div>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-12">
						<?php
						$providers = PersonData::getProviders();

						if (count($providers) > 0) {
							?>
							<table class="table table-bordered table-hover datatable" id="gridProviders">
								<thead class="thead-dark">
									<th>N° Doc.</th>
									<th>Nombre completo</th>
									<th>Direccion</th>
									<th>Ubigeo</th>
									<th>Email</th>
									<th>Telefono</th>
									<th>Acciones</th>
								</thead>
								<?php
								foreach ($providers as $provider) {
									?>
									<tr style="<?=($provider->status == 1) ? '' : 'background: #FFCBD1;'; ?>;">
										<td><?=$provider->numero_documento;?></td>
										<td>
											<?=$provider->name . " " . $provider->lastname?>
										</td>
										<td><?=$provider->address1?></td>
										<td><?=$provider->ubigeo?></td>
										<td><?=$provider->email1?></td>
										<td><?=$provider->phone1?></td>
										<td style="display: flex; justify-content: center;">
											<div class="row" style="display: flex; justify-content: center;">
												<?php 
												if ($provider->status == 1): ?>
													<a href="#" class="btn btn-warning btn-xs edit-provider"
														data-id="<?=$provider->id; ?>" title="Editar proveedor"><i class="fas fa-pencil-alt"></i></a>
													<a href="#" class="btn btn-danger btn-xs delete-provider"
														data-id="<?=$provider->id.'|D'; ?>" title="Desactivar proveedor"><i class="fa fa-power-off img-circle"></i></a>													
												<?php
												else: ?>
													<a href="#" class="btn btn-success btn-xs delete-provider"
														data-id="<?=$provider->id.'|A'; ?>" title='Activar proveedor'><i class='fa fa-power-off img-circle'></i></a>
												<?php 
												endif; ?>
											</div>	
										</td>
									</tr>
									<?php
								}
								?>
							</table>
							<?php
						} else {
							echo "<p class='alert alert-danger'>No hay proveedores</p>";
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>