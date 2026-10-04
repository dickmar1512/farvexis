<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class='fa fa-th-list'></i> Categorias</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Catalogos</a></li>
					<li class="breadcrumb-item active">Lista Categorias</li>
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
                        <h4>Lista categorias</h4>
                    </div>
                    <div class="col-md-6" style="display: flex; justify-content: right;">
                        <div class="btn-group float-sm-right">
                            <!-- <a href="./?view=newcategory" class="btn btn-default"><i class='fa fa-plus'></i>
                                Nueva Categoria</a> -->
                            <button id="openModalAgregarCategoria" class="btn btn-primary">
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
                        <?php
						$categorias = CategoryData::getAll();
						if(count($categorias)>0){
							// si hay usuarios
							?>
                        <table class="table table-bordered table-hover datatable">
                            <thead class="thead-dark">
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </thead>
							<tbody>
								<?php
								foreach($categorias as $categoria){
								?>
								<tr>
									<td><?php echo $categoria->name; ?></td>
                                    <td><?php echo $categoria->description; ?></td>
									<td style="display: flex; justify-content: center; width:90px;">
										<!-- <a href="./?view=editcategory&id=<?php echo $categoria->id;?>" class="btn btn-warning btn-xs"><i class="fas fa-pencil-alt"></i></a> 
										<a href="<?= url('delcategory', ['id' => $categoria->id]) ?>" class="btn btn-danger btn-xs"><i class="fa fa-power-off img-circle"></i></a> -->
                                        <?php 
										if ($categoria->status == 1): ?>
											<a href="#" class="btn btn-warning btn-xs edit-categoria" data-id="<?=$categoria->id; ?>" title="Editar Categoria"><i class="fas fa-pencil-alt"></i></a>
											<a href="#" class="btn btn-danger btn-xs delete-categoria" data-id="<?=$categoria->id.'|D'; ?>" title="Desactivar Categoria"><i class="fa fa-power-off img-circle"></i></a>													
										<?php
										else: ?>
											<a href="#" class="btn btn-success btn-xs delete-categoria" data-id="<?=$categoria->id.'|A'; ?>" title='Activar Categoria'><i class='fa fa-power-off img-circle'></i></a>
										<?php 
										endif; ?>
									</td>
								</tr>
								<?php
								}
								?>
							</tbody>
                        </table>
                        <?php
						}else{
							echo "<p class='alert alert-danger'>No hay Categorias</p>";
						}
						?>

                    </div>
                </div>
            </div>
		</div>
	</div>
</section>		