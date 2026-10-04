<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fas fa-history'></i> Bitácora de Acciones (Log de Sistema)</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Sistema</a></li>
					<li class="breadcrumb-item active">Bitácora</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<section class="content">
	<div class="container-fluid">
		<div class="card card-default">
			<div class="card-header">
				<h3 class="card-title">Últimos 1000 movimientos registrados</h3>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-striped table-hover datatable" style="width:100%; font-size:14px;">
						<thead class="thead-dark text-center">
							<tr>
								<th>FECHA Y HORA</th>
								<th>USUARIO</th>
								<th>ACCIÓN / MOVIMIENTO</th>
								<th>DOCUMENTO / REF</th>
								<th>IMPORTE</th>
								<th>ESTADO</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$logs = SellData::getSystemLog();
							foreach($logs as $log):
								// Determinar acción
								$accion = "";
								$icon = "";
								$badge = "";
								if ($log['operation_type_id'] == 2) {
									$accion = "VENTA DE MERCADERÍA";
									$icon = "fas fa-shopping-cart";
									$badge = "badge-success";
								} else if ($log['operation_type_id'] == 1) {
									$accion = "INGRESO DE MERCADERÍA";
									$icon = "fas fa-box-open";
									$badge = "badge-primary";
								} else {
									$accion = "MOVIMIENTO OTROS";
									$icon = "fas fa-exchange-alt";
									$badge = "badge-secondary";
								}

								// Comprobante
								$comp = "N/A";
								if($log['tipo_comprobante'] == 1) $comp = "FE"; //Factura Electronica
								else if($log['tipo_comprobante'] == 3) $comp = "BE"; //Boleta Electronica
								else if($log['tipo_comprobante'] == 60) $comp = "ID"; //Ingreso Diversa
								else if($log['tipo_comprobante'] == 65) $comp = "SD"; //Salida Diversa
								else if($log['tipo_comprobante'] == 70) $comp = "NV"; //Nota de venta
								
								// Estado
								if($log['estado'] == 0) {
									$estadoStr = "<span class='badge badge-danger'><i class='fas fa-ban'></i> ANULADO</span>";
									$accion .= " (ANULADA)";
								} else {
									$estadoStr = "<span class='badge badge-success'><i class='fas fa-check'></i> REGISTRADO</span>";
								}
						?>
							<tr>
								<td class="text-center font-weight-bold"><?php echo date("d/m/Y h:i A", strtotime($log['created_at'])); ?></td>
								<td><i class="fas fa-user-circle mr-1 text-muted"></i> <?php echo strtoupper($log['name'] . " " . $log['lastname']); ?></td>
								<td><span class="badge <?php echo $badge; ?>" style="font-size: 13px;"><i class="<?php echo $icon; ?>"></i> <?php echo $accion; ?></span></td>
								<td class="text-center"><?php echo $comp . " " . $log['serie'].'-'.$log['comprobante']; ?></td>
								<td class="text-right font-weight-bold text-success">S/ <?php echo number_format($log['total'], 2); ?></td>
								<td class="text-center"><?php echo $estadoStr; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
