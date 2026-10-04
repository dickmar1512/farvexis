<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class="fa fa-gear"></i> Configuración del Sistema</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Administración</a></li>
					<li class="breadcrumb-item active">Configuración</li>
				</ol>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid" style="display: flex; justify-content: center;">
		<div class="card card-default col-md-10">
			<div class="card-header bg-navy">
				<h4 class="card-title"><i class="fas fa-building mr-2"></i> Datos de la Empresa y Facturación Electrónica SUNAT</h4>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<?php
					$empresa = EmpresaData::getDatos();
					$igvDefecto = $empresa->sunat_igv_tipo_defecto ?? '20';
					$ambiente = $empresa->sunat_ambiente ?? 'beta';
				?>
				<form id="form_settings" method="post" enctype="multipart/form-data" class="form-horizontal">
					<input type="hidden" name="id" value="<?php echo $empresa->Emp_IdEmpresa; ?>">
					
					<h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-id-card mr-1"></i> Información General</h5>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label font-weight-bold">RUC Emisor:</label>
								<input type="text" name="ruc" value="<?php echo $empresa->Emp_Ruc ?>" class="form-control form-control-sm" required>
							</div>
						</div>									
						<div class="col-md-9">
							<div class="form-group">
								<label class="control-label font-weight-bold">Razón Social:</label>
								<input type="text" name="razon_social" value="<?php echo $empresa->Emp_RazonSocial ?>" class="form-control form-control-sm" required>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Nombre Comercial / Descripción:</label>
								<input type="text" name="descripcion" value="<?php echo $empresa->Emp_Descripcion ?>" class="form-control form-control-sm">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label font-weight-bold">Dirección Fiscal:</label>
								<input type="text" name="direccion" value="<?php echo $empresa->Emp_Direccion ?>" class="form-control form-control-sm">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label">Teléfono:</label>
								<input type="text" name="telefono" value="<?php echo $empresa->Emp_Telefono ?>" class="form-control form-control-sm">
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label">Correo Electrónico:</label>
								<input type="text" name="celular" value="<?php echo $empresa->Emp_Celular ?>" class="form-control form-control-sm">
							</div>
						</div>
						<div class="col-md-4">
							<label class="control-label">Logo de Empresa:</label>
							<div class="d-flex align-items-center">
								<div class="custom-file flex-grow-1 mr-3">
									<input type="file" name="image" id="image" class="custom-file-input" accept="image/*">
									<label class="custom-file-label" for="image">Elegir archivo...</label>
								</div>
								<div id="logo_empresa_preview" class="border rounded bg-light d-flex align-items-center justify-content-center" style="width:72px; height:72px; overflow:hidden;">
									<?php if ($empresa && $empresa->Emp_Logo): ?>
										<img src="storage/images/<?php echo $empresa->Emp_Logo; ?>" alt="Logo empresa" style="max-width:100%; max-height:100%; object-fit:contain;">
									<?php else: ?>
										<i class="fas fa-image text-muted fa-2x"></i>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>

					<h5 class="text-success border-bottom pb-2 mt-4 mb-3"><i class="fas fa-satellite-dish mr-1"></i> Configuración SUNAT (Envío Directo Independiente)</h5>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label font-weight-bold">Ambiente SUNAT:</label>
								<select name="sunat_ambiente" class="form-control form-control-sm">
									<option value="beta" <?php echo ($ambiente === 'beta') ? 'selected' : ''; ?>>🧪 Pruebas / Beta</option>
									<option value="produccion" <?php echo ($ambiente === 'produccion') ? 'selected' : ''; ?>>🚀 Producción</option>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label font-weight-bold">Tipo Afectación Defecto:</label>
								<select name="sunat_igv_tipo_defecto" class="form-control form-control-sm">
									<option value="20" <?php echo ($igvDefecto === '20') ? 'selected' : ''; ?>>Exonerado (20)</option>
									<option value="10" <?php echo ($igvDefecto === '10') ? 'selected' : ''; ?>>Gravado - 18% IGV (10)</option>
									<option value="30" <?php echo ($igvDefecto === '30') ? 'selected' : ''; ?>>Inafecto (30)</option>
									<option value="11" <?php echo ($igvDefecto === '11') ? 'selected' : ''; ?>>Gratuito (11)</option>
								</select>
								<small class="text-muted">Afectación sugerida al agregar productos al carrito</small>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label font-weight-bold">Usuario SOL:</label>
								<input type="text" name="sunat_usuario_sol" value="<?php echo $empresa->sunat_usuario_sol ?: 'MODDATOS'; ?>" class="form-control form-control-sm" placeholder="Ej: MODDATOS o USUARIO">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label font-weight-bold">Clave SOL:</label>
								<input type="password" name="sunat_clave_sol" value="<?php echo $empresa->sunat_clave_sol ?: 'moddatos'; ?>" class="form-control form-control-sm" placeholder="Contraseña SOL">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label font-weight-bold">Certificado Digital (.pfx / .p12):</label>
								<div class="custom-file">
									<input type="file" name="sunat_cert_file" id="sunat_cert_file" class="custom-file-input" accept=".pfx,.p12">
									<label class="custom-file-label" for="sunat_cert_file">Seleccionar archivo .pfx...</label>
								</div>
								<small class="text-muted">
									Actual: <code><?php echo $empresa->sunat_cert_path ?: 'storage/CERT/demo.pfx'; ?></code>
								</small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label font-weight-bold">Contraseña del Certificado Digital:</label>
								<input type="password" name="sunat_cert_pass" value="<?php echo $empresa->sunat_cert_pass ?: '123456'; ?>" class="form-control form-control-sm" placeholder="Contraseña del archivo .pfx">
							</div>
						</div>
					</div>

					<div class="text-right mt-4">
						<button type="submit" class="btn btn-primary px-4 shadow-sm">
							<i class="fas fa-save mr-1"></i> Guardar Cambios
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<script>
$(document).ready(function() {
    $('#image').on('change', function () {
        var file = this.files && this.files[0];
        var preview = $('#logo_empresa_preview');

        if (!file || !file.type.startsWith('image/')) {
            preview.html('<i class="fas fa-image text-muted fa-2x"></i>');
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            preview.html('<img src="' + e.target.result + '" alt="Logo empresa" style="max-width:100%; max-height:100%; object-fit:contain;">');
        };
        reader.readAsDataURL(file);
    });

    $('#form_settings').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        Swal.fire({
            title: 'Guardando configuración',
            text: 'Actualizando datos y configuración SUNAT...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        $.ajax({
            url: './?action=updateempresa',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        title: '¡Guardado!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(function () {
                        window.location.reload();
                    }, 1200);
                } else {
                    Swal.fire('Error', res.message || 'Error al guardar', 'error');
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'Error al conectar con el servidor';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>
