<style type="text/css">
	.procesar-venta[disabled] {
		pointer-events: none;
		opacity: 0.65;
		cursor: not-allowed;
	}

	.difference-container {
		margin-bottom: 20px;
		padding: 12px;
		background-color: #f8f9fa;
		border-radius: 8px;
		border: 2px dashed #dee2e6;
		transition: all 0.3s ease;
	}
	.denominations-table input[type="number"] {
		max-width: 90px;
		text-align: right;
		font-weight: 600;
		border-radius: 4px;
		border: 1px solid #ced4da;
		padding: 2px 6px;
	}
	.denominations-table input[type="number"]:focus {
		border-color: #3b82f6;
		outline: none;
		box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
	}
	.denominations-table label {
		margin-bottom: 0;
		font-weight: 500;
		color: #4b5563;
	}
	.denominations-table tr:hover {
		background-color: #f3f4f6;
	}
	.text-purple { color: #6f42c1 !important; }
	.text-indigo { color: #6610f2 !important; }
</style>

<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row align-items-center mb-2">
			<div class="col-sm-6">
				<h1 class="h4 font-weight-bold text-dark mb-0">
					<i class="fa fa-archive text-success mr-2"></i> Cierre de Caja
				</h1>
			</div><!-- /.col -->
			<div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
				<div class="btn-group shadow-sm">
					<a href="./?view=boxhistory" class="btn btn-outline-primary bg-white">
						<i class="fa fa-clock-o mr-1"></i> Historial
					</a>
					<button id="procesarVentasBtn" class="btn btn-success procesar-venta" disabled>
						Procesar Ventas <i class="fa fa-arrow-right ml-1"></i>
					</button>
				</div>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<div class="row">
					<!-- PANEL IZQUIERDO: COMPROBANTES GENERADOS -->
					<div class="col-lg-6 col-md-12 mb-4">
						<div class="d-flex align-items-center mb-3">
							<span class="badge bg-dark p-2 text-uppercase font-weight-bold mr-2 text-white" style="background-color: #343a40;">Comprobantes Generados</span>
							<small class="text-muted">(Ventas pendientes de procesar)</small>
						</div>
						<?php
						$products = SellData::getSellsUnBoxed();
						$total_total = 0;
						$yape_val = 0;
						$plin_val = 0;
						$tdebito_val = 0;
						$tcredito_val = 0;

						$dbPath = '../efact1.3.4/bd/BDFacturador.db';
						$sqliteDb = null;
						if (file_exists($dbPath) && class_exists('SQLite3')) {
							try {
								$sqliteDb = new SQLite3($dbPath, SQLITE3_OPEN_READONLY);
							} catch (Exception $e) {}
						}

						if (count($products) > 0) {
							$i = 1;
							?>
							<div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
								<table class="table table-sm table-bordered table-hover text-sm" id="box">
									<thead class="bg-dark text-white" style="background-color: #343a40;">
										<tr>
											<th>#</th>
											<th>Comprobante</th>
											<th class="text-right">Total</th>
											<th class="text-center">Fecha</th>
											<th>Usuario</th>
										</tr>
									</thead>
									<tbody>
									<?php foreach ($products as $sell): 
										$notacomprobar = $sell->serie . "-" . $sell->comprobante; 
										$probar = NotData::getByIdComprobado($notacomprobar);
										$fechaObj = new DateTime($sell->created_at);
										$fechaFormateada = $fechaObj->format('d/m/Y H:i:s');
										
										$estadoSituacion = '-';
										if (!empty($sell->estado_sunat)) {
											$isRejected = ($sell->estado_sunat === 'rechazado');
										} else {
											if ($sqliteDb) {
												$query_sfs = "SELECT IND_SITU FROM DOCUMENTO WHERE NUM_DOCU = '" . $notacomprobar . "'";
												$results_sfs = $sqliteDb->query($query_sfs);
												if ($results_sfs && $doc = $results_sfs->fetchArray(SQLITE3_ASSOC)) {
													$estadoSituacion = $doc['IND_SITU'];
												}
											}
											$isRejected = in_array($estadoSituacion, ["05", "10", "06", "11", "12"]);
										}
										$isCreditNote = (isset($probar->TIPO_DOC) && $probar->TIPO_DOC == 7);
										$isAnnulled = ($sell->estado == 0);
										$isInvalid = ($isRejected || $isCreditNote || $isAnnulled);
										
										$rowBg = '';
										if ($isInvalid) {
											$rowBg = 'background-color: #fde8e8;'; // Light red for invalid/credit note
										} elseif (isset($probar) && $probar->TIPO_DOC == 8) {
											$rowBg = 'background-color: #e8f5e9;'; // Light green for type 8
										}
									?>
										<tr style="<?= $rowBg ?>">
											<td><?= $i++ ?></td>
											<td class="font-weight-bold">
												<?= $sell->serie . "-" . $sell->comprobante ?>
												<?php if ($isInvalid): ?>
													<span class="badge badge-danger text-xs ml-1" style="background-color: #dc3545;">Anulado/N.C.</span>
												<?php endif; ?>
											</td>
											<td class="text-right font-weight-bold text-dark">
												<?php
												$operations = OperationData::getAllProductsBySellId($sell->id);
												$total = 0;
												foreach ($operations as $operation) {
													$product = $operation->getProduct();
													$total += $operation->q * ($operation->prec_alt - $operation->descuento);
												}
												if ($isInvalid) $total = 0;
												$total_total += $total;
												
												if ($total > 0) {
													$pago_parcial_data = SellData::getImportePagoParcial($sell->id);
													$importe_pp = 0;
													if (!empty($pago_parcial_data)) {
														$importe_pp = floatval($pago_parcial_data[0]->importepp);
													}
													$monto_principal = $total - $importe_pp;

													if ($sell->tipo_pago == 2) $plin_val += $monto_principal;
													if ($sell->tipo_pago == 3) $yape_val += $monto_principal;
													if ($sell->tipo_pago == 4) $tdebito_val += $monto_principal;
													if ($sell->tipo_pago == 5) $tcredito_val += $monto_principal;
												}
												?>
												S/ <?= number_format($total, 2, ".", ",") ?>
											</td>	
											<td class="text-center text-muted"><?= $fechaFormateada ?></td>
											<td><?= $sell->user ?></td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>
							<div class="mt-3 text-right">
								<h3 class="font-weight-bold text-primary mb-0">Total Ventas: S/ <?php echo number_format($total_total, 2, ".", ","); ?></h3>
								<input type="hidden" id="totalVentas" name="totalVentas" value="<?=$total_total; ?>">
							</div>
							<?php
						} else {
							?>
							<div class="text-center py-5 bg-light rounded border">
								<i class="fa fa-file-text-o fa-3x text-muted mb-3"></i>
								<h5>No hay ventas registradas</h5>
								<p class="text-muted mb-0">Todas las ventas del día han sido procesadas o no se ha realizado ninguna venta aún.</p>
							</div>
							<input type="hidden" id="totalVentas" name="totalVentas" value="0">
						<?php } ?>
					</div>

					<!-- PANEL CENTRAL: ARQUEO DE EFECTIVO Y MÉTODOS DE PAGO -->
					<div class="col-lg-3 col-md-6 mb-4">
						<div class="d-flex align-items-center mb-3">
							<span class="badge bg-dark p-2 text-uppercase font-weight-bold text-white" style="background-color: #343a40;">Billetes y Monedas</span>
						</div>
						<?php if ($total_total > 0): ?>
							<div class="table-responsive">
								<table class="table table-sm table-bordered denominations-table" id="denominations">
									<thead class="thead-light text-center" style="background-color: #f8f9fa;">
										<tr>
											<th>Denominación</th>
											<th>Cantidad</th>
										</tr>
									</thead>
									<tbody>
										<!-- Billetes -->
										<tr>
											<td><label for="b200"><i class="fa fa-money text-success mr-2"></i>Billete &nbsp;200</label></td>
											<td class="text-center"><input type="number" name="b200" id="b200" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<tr>
											<td><label for="b100"><i class="fa fa-money text-success mr-2"></i>Billete &nbsp;100</label></td>
											<td class="text-center"><input type="number" name="b100" id="b100" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<tr>
											<td><label for="b50"><i class="fa fa-money text-success mr-2"></i>Billete &nbsp;&nbsp;50</label></td>
											<td class="text-center"><input type="number" name="b50" id="b50" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<tr>
											<td><label for="b20"><i class="fa fa-money text-success mr-2"></i>Billete &nbsp;&nbsp;20</label></td>
											<td class="text-center"><input type="number" name="b20" id="b20" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<tr>
											<td><label for="b10"><i class="fa fa-money text-success mr-2"></i>Billete &nbsp;&nbsp;10</label></td>
											<td class="text-center"><input type="number" name="b10" id="b10" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<!-- Monedas -->
										<tr>
											<td><label for="m5"><i class="fa fa-coins text-warning mr-2"></i>Moneda &nbsp;&nbsp;5</label></td>
											<td class="text-center"><input type="number" name="m5" id="m5" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<tr>
											<td><label for="m2"><i class="fa fa-coins text-warning mr-2"></i>Moneda &nbsp;&nbsp;2</label></td>
											<td class="text-center"><input type="number" name="m2" id="m2" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<tr>
											<td><label for="m1"><i class="fa fa-coins text-warning mr-2"></i>Moneda &nbsp;&nbsp;1</label></td>
											<td class="text-center"><input type="number" name="m1" id="m1" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<tr>
											<td><label for="c50"><i class="fa fa-coins text-muted mr-2"></i>Moneda &nbsp;0.50</label></td>
											<td class="text-center"><input type="number" name="c50" id="c50" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<tr>
											<td><label for="c20"><i class="fa fa-coins text-muted mr-2"></i>Moneda &nbsp;0.20</label></td>
											<td class="text-center"><input type="number" name="c20" id="c20" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<tr>
											<td><label for="c10"><i class="fa fa-coins text-muted mr-2"></i>Moneda &nbsp;0.10</label></td>
											<td class="text-center"><input type="number" name="c10" id="c10" min="0" class="form-control-sm" value="0"></td>
										</tr>
										<!-- Canales Digitales -->
										<tr class="bg-light">
											<td><label for="yape" class="font-weight-bold text-purple"><i class="fa fa-mobile mr-2 text-purple"></i>Yape</label></td>
											<?php $yape = ($yape_val > 0) ? number_format($yape_val, 2, '.', '') : "0.00"; ?>
											<td class="text-center"><input type="number" step="0.01" name="yape" id="yape" class="form-control-sm text-purple font-weight-bold" value="<?=$yape?>"></td>
										</tr>
										<tr class="bg-light">
											<td><label for="plin" class="font-weight-bold text-info"><i class="fa fa-mobile mr-2 text-info"></i>Plin</label></td>
											<?php $plin = ($plin_val > 0) ? number_format($plin_val, 2, '.', '') : "0.00"; ?>
											<td class="text-center"><input type="number" step="0.01" name="plin" id="plin" class="form-control-sm text-info font-weight-bold" value="<?=$plin?>"></td>
										</tr>
										<tr class="bg-light">
											<td><label for="tdebito" class="font-weight-bold text-primary"><i class="fa fa-credit-card mr-2 text-primary"></i>T. Débito</label></td>
											<?php $tdebito = ($tdebito_val > 0) ? number_format($tdebito_val, 2, '.', '') : "0.00"; ?>
											<td class="text-center"><input type="number" step="0.01" name="tdebito" id="tdebito" class="form-control-sm text-primary font-weight-bold" value="<?=$tdebito?>"></td>
										</tr>
										<tr class="bg-light">
											<td><label for="tcredito" class="font-weight-bold text-indigo"><i class="fa fa-credit-card mr-2 text-indigo"></i>T. Crédito</label></td>
											<?php $tcredito = ($tcredito_val > 0) ? number_format($tcredito_val, 2, '.', '') : "0.00"; ?>
											<td class="text-center"><input type="number" step="0.01" name="tcredito" id="tcredito" class="form-control-sm text-indigo font-weight-bold" value="<?=$tcredito?>"></td>
										</tr>
									</tbody>
								</table>
							</div>
						<?php else: ?>
							<div class="text-center py-5 bg-light rounded border text-muted">
								<i class="fa fa-calculator fa-2x mb-2"></i>
								<p class="mb-0">Sin ingresos a cerrar</p>
							</div>
						<?php endif; ?>
					</div>

					<!-- PANEL DERECHO: INSTRUCCIONES Y CÁLCULOS EN TIEMPO REAL -->
					<div class="col-lg-3 col-md-6 mb-4">
						<div class="d-flex align-items-center mb-3">
							<span class="badge bg-dark p-2 text-uppercase font-weight-bold text-white" style="background-color: #343a40;">Resultados</span>
						</div>
						<?php if ($total_total > 0): ?>
							<!-- Live Calculations -->
							<div class="difference-container" id="differenceContainer">
								<h6 class="font-weight-bold text-muted text-uppercase mb-2 text-xs">Arqueo en Tiempo Real</h6>
								<div class="row mb-1 text-sm">
									<div class="col-7 text-muted">Total Efectivo:</div>
									<div class="col-5 font-weight-bold text-right text-dark" id="totalEfectivoText">S/ 0.00</div>
								</div>
								<div class="row mb-1 text-sm">
									<div class="col-7 text-muted">Total Digital:</div>
									<div class="col-5 font-weight-bold text-right text-dark" id="totalDigitalText">S/ 0.00</div>
								</div>
								<hr class="my-2">
								<div class="row mb-1 text-sm">
									<div class="col-7 text-muted font-weight-bold">Total Arqueo:</div>
									<div class="col-5 font-weight-bold text-right text-primary" id="totalArqueoText">S/ 0.00</div>
								</div>
								<div class="row mb-1 text-sm">
									<div class="col-7 text-muted font-weight-bold">Esperado:</div>
									<div class="col-5 font-weight-bold text-right text-dark">S/ <?= number_format($total_total, 2) ?></div>
								</div>
								<hr class="my-2">
								<div class="row align-items-center">
									<div class="col-7 text-muted font-weight-bold text-sm">Diferencia:</div>
									<div class="col-5 text-right h5 font-weight-bold mb-0 text-danger" id="differenceText">S/ 0.00</div>
								</div>
							</div>

							<!-- Instruction Board -->
							<div class="card card-outline card-info bg-light border-info" style="border-top: 3px solid #17a2b8;">
								<div class="card-body p-3">
									<h6 class="font-weight-bold text-info"><i class="fa fa-info-circle mr-2"></i>Instrucciones</h6>
									<ul class="pl-3 mb-0 text-xs text-muted" style="line-height: 1.5; padding-left: 20px;">
										<li class="mb-2">Ingrese las cantidades físicas de billetes y monedas contadas en caja.</li>
										<li class="mb-2">Ajuste los importes cobrados vía Yape, Plin y Tarjetas de ser necesario.</li>
										<li class="mb-2">La sumatoria total debe coincidir exactamente con el total de ventas esperado de la jornada (Diferencia = 0.00).</li>
										<li>Una vez balanceado, el botón <b>"Procesar Ventas"</b> se habilitará para permitir registrar el corte.</li>
									</ul>
								</div>
							</div>
						<?php else: ?>
							<div class="text-center py-5 bg-light rounded border text-muted">
								<i class="fa fa-clipboard fa-2x mb-2"></i>
								<p class="mb-0">Sin arqueos activos</p>
							</div>
						<?php 
						endif; 
						if ($sqliteDb) { $sqliteDb->close(); }
						?>		
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
