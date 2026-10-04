<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fa fa-archive'></i> Ingreso gastos</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Caja</a></li>
					<li class="breadcrumb-item active">Ingreso gastos</li>
				</ol>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
 <!-- Main content -->
 <section class="content">
	<div class="container-fluid col-md-12">
		<div class="card card-default">
			<div class="card-header">
				<div class="row">
          <div class="col-md-8">
             <form id="formFiltros">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" id="fecha_inicio" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" id="fecha_fin" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Usuario</label>
                            <select id="usuario_id" class="form-control">
                                <option value="0">Todos</option>
                                <?php foreach(UserData::getAll() as $user): ?>
                                <option value="<?= $user->id ?>"><?= htmlspecialchars($user->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group">
                          <button type="submit" class="btn btn-primary">
                              <i class="fas fa-search"></i> Filtrar
                          </button>
                        </div>
                    </div>
                </div>
            </form>
          </div>
					<div class="col-md-4 d-flex align-items-end" style="display: flex; justify-content: right;">            
            <div class="form-group col-md-6">  
              <div class="col-md-8">
                <div class="total-display">
                    <h5>Total: <span id="totalGastos">0.00</span></h5>
                </div>
              </div>
            </div> 
            <div class="form-group col-md-6">
              <div class="col-md-10" style="display: flex; justify-content: right;">							
                <!-- Botón para agregar gasto -->
                <button id="btnNuevoGasto" class="btn btn-info">
                    <i class="fa fa-plus"></i> Registrar Gasto
                </button>
              </div>
            </div> 
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
        <div class="row">
          <!-- Tabla para mostrar gastos -->
          <div class="table-responsive">
              <table class="table table-bordered table-hover" id="tablaGastos">
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Comprobante</th>
                        <th>Usuario</th>
                        <th>Importe</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Cargado por AJAX -->
                </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
 </section>     