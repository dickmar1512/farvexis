<?php 
// Vista de Configuración de Correos
?>
<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fas fa-envelope'></i> Configuración de Correos</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
				    <li class="breadcrumb-item"><a href="#">Administración</a></li>
					<li class="breadcrumb-item active">Correos</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<section class="content">
	<div class="container-fluid">
		<div class="card card-default">
			<div class="card-header">				
				<h3 class="card-title"><i class="fas fa-at mr-2"></i>Lista de Correos Activos para Envíos</h3>				
				<div class="btn-group float-sm-right">
					<button id="newEmailBtn" class="btn btn-primary" data-toggle="modal" data-target="#emailModal">
						<i class='fas fa-plus mr-2'></i> Nuevo Correo
					</button>
				</div>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-12">
						<?php
						$emails = class_exists('EmailConfigData') ? EmailConfigData::getAll() : [];
						if (count($emails) > 0) {
							?>
							<table class="table table-bordered table-hover">
								<thead class="thead-dark">
									<th style="width: 10px;">#</th>
									<th>Email</th>
									<th>Tipo (PARA, CC, CCO)</th>
									<th>Estado</th>
									<th>Fecha de Registro</th>
									<th>Acciones</th>
								</thead>
								<tbody>
								<?php
								$cont = 0;
								foreach ($emails as $email) {
									$cont++;
									?>
									<tr>
										<td><?php echo $cont; ?></td>
										<td><?php echo $email->email; ?></td>
										<td>
                                            <?php 
                                            if($email->type == 'to') echo '<span class="badge badge-primary">PARA (TO)</span>';
                                            elseif($email->type == 'cc') echo '<span class="badge badge-info">Copia (CC)</span>';
                                            elseif($email->type == 'bcc') echo '<span class="badge badge-secondary">Copia Oculta (CCO)</span>';
                                            ?>
                                        </td>
										<td style="text-align: center;">
											<?php if ($email->is_active): ?>
												<span class="badge badge-success">Activo</span>
											<?php else: ?>
												<span class="badge badge-danger">Inactivo</span>
											<?php endif; ?>
										</td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($email->created_at)); ?></td>
										<td style="width:130px;">
											<a href="#" class="btn btn-warning btn-xs edit-email" 
                                                data-id="<?php echo $email->id; ?>"
                                                data-email="<?php echo $email->email; ?>"
                                                data-type="<?php echo $email->type; ?>"
                                                data-active="<?php echo $email->is_active; ?>"
                                                data-toggle="modal" data-target="#emailModal"><i class="fas fa-edit"></i></a>
											<a href="index.php?action=delemail&id=<?php echo $email->id; ?>" class="btn btn-danger btn-xs" onclick="return confirm('¿Está seguro de eliminar este correo?');"><i class="fas fa-trash"></i></a>
										</td>
									</tr>
									<?php
								} ?>
								</tbody>
							</table>
						<?php } else { ?>
                            <p class="alert alert-warning">No hay correos configurados. Agregue correos para las notificaciones del sistema.</p>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="index.php?action=saveemail" method="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="emailModalLabel">Configurar Correo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="id" id="email_id" value="">
          <div class="form-group">
            <label for="email">Dirección de Correo</label>
            <input type="email" class="form-control" id="email_address" name="email" required placeholder="ejemplo@correo.com">
          </div>
          <div class="form-group">
            <label for="type">Tipo de Destinatario</label>
            <select class="form-control" id="email_type" name="type" required>
                <option value="to">Para (Destinatario Principal)</option>
                <option value="cc">CC (Con Copia)</option>
                <option value="bcc">CCO (Con Copia Oculta)</option>
            </select>
          </div>
          <div class="form-group">
            <label for="is_active">Estado</label>
            <select class="form-control" id="email_active" name="is_active" required>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#newEmailBtn').click(function(){
        $('#emailModalLabel').text('Nuevo Correo');
        $('#email_id').val('');
        $('#email_address').val('');
        $('#email_type').val('to');
        $('#email_active').val('1');
    });

    $('.edit-email').click(function(){
        $('#emailModalLabel').text('Editar Correo');
        $('#email_id').val($(this).data('id'));
        $('#email_address').val($(this).data('email'));
        $('#email_type').val($(this).data('type'));
        $('#email_active').val($(this).data('active'));
    });
});
</script>
