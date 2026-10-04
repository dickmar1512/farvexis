<section class="content">
	<div class="row">
		<div class="col-md-12">
			<h1>Modificar Comprobantes</h1>
			<form action="./?view=editarcomprobantesvarios">
				<input type="hidden" name="view" value="editarcomprobantesvarios">
				<div class="row">
					<div class="col-md-2">
						<select name="selComprobante" class="form-control" required="required">
							<option value="0">-Comprobante-</option>
							<option value="3">Boleta</option>
							<option value="1">Factura</option>
						</select>
					</div>
					<div class="col-md-2">
						<input type="date" name="fecha_inicio" value="<?php echo date("Y-m-d"); ?>" class="form-control">
					</div>
					<div class="col-md-2">
						<input type="date" name="fecha_fin" value="<?php echo date("Y-m-d"); ?>" class="form-control">
					</div>
				</div>
				<br>
				<h5>Cambiar fecha</h5>
				<div class="row">
					<div class="col-md-2">
						<input type="date" name="fecha_nueva" value="<?php echo date("Y-m-d"); ?>" class="form-control">
					</div>
					<div class="col-md-2">
						<input type="submit" class="btn btn-primary btn-block" value="Cambiar Fecha">
					</div>
				</div>
			</form>
		</div>
	</div>
</section>