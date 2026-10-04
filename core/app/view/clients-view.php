<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fa fa-user-friends'></i> Lista de Clientes</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Administración</a></li>
					<li class="breadcrumb-item active">Lista Clientes</li>
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
						<h4>Directorio de Clientes</h4>
					</div>					
					<div class="col-md-6" style="display: flex; justify-content: right;">
						<div class="btn-group float-sm-right">
							<button id="openModalNuevoCliente" class="btn btn-primary">
								<i class='fa fa-user-plus'></i>
								Nuevo Cliente
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
						$clients = PersonData::getClients();

						if (count($clients) > 0) {
							?>
							<table class="table table-bordered table-hover datatable">
								<thead class="thead-dark">
									<th>N° Doc.</th>
									<th>Nombre completo</th>
									<th>Direccion</th>
									<th>Ubigeo</th>
									<th>Email</th>
									<th>Telefono</th>
									<th><i class='fa fa-gears'></i></th>
								</thead>
								<?php
								foreach ($clients as $client) {
									?>
									<tr style="<?=($client->status == 1) ? '' : 'background: #FFCBD1;'; ?>;">
										<td><?=$client->numero_documento ?></td>
										<td>
											<?php echo $client->lastname . " " . $client->name; ?>
										</td>
										<td><?=$client->address1; ?></td>
										<td><?=$client->ubigeo; ?></td>
										<td><?=$client->email1; ?></td>
										<td><?=$client->phone1; ?></td>
										<td>
											<div class="row" style="display: flex; justify-content: center;">
												<?php 
												if ($client->status == 1): ?>
													<a href="#" class="btn btn-warning btn-xs edit-client"
														data-id="<?=$client->id; ?>" title="Editar Cliente"><i class="fas fa-pencil-alt"></i></a>
													<a href="#" class="btn btn-danger btn-xs delete-client"
														data-id="<?=$client->id.'|D'; ?>" title="Desactivar cliente"><i class="fa fa-power-off img-circle"></i></a>													
												<?php
												else: ?>
													<a href="#" class="btn btn-success btn-xs delete-client"
														data-id="<?=$client->id.'|A'; ?>" title='Activar cliente'><i class='fa fa-power-off img-circle'></i></a>
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
							echo "<p class='alert alert-danger'>No hay clientes</p>";
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
