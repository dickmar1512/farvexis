<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fa fa-table'></i> Reporte Pivot Venta - Producto</h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Ventas</a></li>
					<li class="breadcrumb-item active">Reporte Pivot</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<section class="content">
	<div class="container-fluid">
		<div class="card card-default">
			<div class="card-header">			
				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-8">
						<form>
							<input type="hidden" name="view" value="salesbyproductstock">
							<div class="row">
								<div class="col-md-4">
                                    <label>Mes / Fecha de Inicio</label>
									<input type="date" name="sd"
										value="<?php echo isset($_GET["sd"]) ? $_GET["sd"] : date("Y-m-01"); ?>"
										class="form-control" required>
								</div>
								<div class="col-md-4">
                                    <label>Mes / Fecha de Fin</label>
									<input type="date" name="ed"
										value="<?php echo isset($_GET["ed"]) ? $_GET["ed"] : date("Y-m-t"); ?>"
										class="form-control" required>
								</div>
								<div class="col-md-4">
                                    <label>&nbsp;</label>
									<input type="submit" class="btn btn-success btn-block" value="Generar Pivot">
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			
			<div class="card-body">
				<div class="row">
					<div class="col-md-12">
						<?php if (isset($_GET["sd"]) && isset($_GET["ed"]) && $_GET["sd"] != "" && $_GET["ed"] != ""): ?>
							<?php
								// Determinar los meses en el rango
								$sd = $_GET["sd"];
								$ed = $_GET["ed"];
								
								$start = new DateTime(substr($sd, 0, 7) . '-01');
								$end = new DateTime(substr($ed, 0, 7) . '-01');
								$interval = new DateInterval('P1M');
								$end->add($interval); 
								$period = new DatePeriod($start, $interval, $end);
								
								$months = [];
								$monthNames = ['01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', '05'=>'Mayo', '06'=>'Junio', '07'=>'Julio', '08'=>'Agosto', '09'=>'Setiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'];
								foreach ($period as $dt) {
									$m = $dt->format("Y-m");
									$months[$m] = $monthNames[$dt->format("m")];
								}
								
								$products = ProductData::getAll();
								$initial_stocks = OperationData::getStockBeforeDate($sd);
								$monthly_movements = OperationData::getMonthlyMovementsBetweenDates($sd, $ed);
								
								$report_data = [];
								foreach($products as $p) {
									$pid = $p->id;
									$prod_movements = isset($monthly_movements[$pid]) ? $monthly_movements[$pid] : [];
									
									$has_movement = !empty($prod_movements);
									$current_stock = isset($initial_stocks[$pid]) ? $initial_stocks[$pid] : 0;
									
									if (!$has_movement && $current_stock <= 0) continue;
									
									$row = [
										'name' => $p->name,
										'estado' => $p->is_active ? 'ACTIVO' : 'INACTIVO',
										'months' => [],
										'total_sold_qty' => 0,
										'total_sold_amount' => 0
									];
									
									$temp_stock = $current_stock;
									foreach($months as $m_key => $m_name) {
										$stock_inicial = $temp_stock;
										$sales_qty = isset($prod_movements[$m_key]['sales_qty']) ? $prod_movements[$m_key]['sales_qty'] : 0;
										$sales_amount = isset($prod_movements[$m_key]['sales_amount']) ? $prod_movements[$m_key]['sales_amount'] : 0;
										$purchases_qty = isset($prod_movements[$m_key]['purchase_qty']) ? $prod_movements[$m_key]['purchase_qty'] : 0;
										
										$row['months'][$m_key] = [
											'stock_inicial' => $stock_inicial,
											'sales_qty' => $sales_qty,
											'sales_amount' => $sales_amount
										];
										
										$row['total_sold_qty'] += $sales_qty;
										$row['total_sold_amount'] += $sales_amount;
										
										// stock para el prox mes es el actual + entradas - ventas
										$temp_stock = $temp_stock + $purchases_qty - $sales_qty;
									}
									$row['stock_final'] = $temp_stock;
									
									if ($row['total_sold_qty'] == 0 && $row['stock_final'] == 0 && !$has_movement) continue;
									
									$num_months = count($months);
									$row['avg_qty'] = $num_months > 0 ? $row['total_sold_qty'] / $num_months : 0;
									$row['avg_amount'] = $num_months > 0 ? $row['total_sold_amount'] / $num_months : 0;
									
									if ($row['avg_qty'] >= 15) {
										$row['rotacion'] = '(A)-ALTA ROTACION';
									} else if ($row['avg_qty'] >= 5) {
										$row['rotacion'] = '(B)-MEDIA ROTACION';
									} else if ($row['avg_qty'] > 0) {
										$row['rotacion'] = '(C)-BAJA ROTACION';
									} else {
										$row['rotacion'] = 'SIN ROTACION';
									}
									
									$report_data[] = $row;
								}
							?>

							<?php if (count($report_data) > 0): ?>
								<div class="table-responsive">
									<table class="table table-bordered table-striped table-hover datatable" style="width:100%; white-space:nowrap; font-size:13px;">
										<thead class="thead-dark text-center">
											<tr>
												<th rowspan="2" style="vertical-align: middle;">Etiquetas de fila</th>
												<?php foreach($months as $m_key => $m_name): ?>
													<th colspan="3"><?php echo strtoupper($m_name); ?></th>
												<?php endforeach; ?>
												<th colspan="7">RESUMEN</th>
											</tr>
											<tr>
												<?php foreach($months as $m_key => $m_name): ?>
													<th class="bg-secondary">Stock Inicial</th>
													<th class="bg-info">CANT. Vendida</th>
													<th class="bg-success">Venta Soles</th>
												<?php endforeach; ?>
												
												<th class="bg-primary">STOCK ACTUAL/<br> A LA FECHA</th>
												<th class="bg-secondary">PROMEDIO CANT.<br> VENDIDAS X MES</th>
												<th class="bg-info">TOTAL CANT.<br> VENDIDA</th>
												<th class="bg-dark">ESTADO</th>
												<th class="bg-dark">ROTACION</th>
												<th class="bg-secondary">PROMEDIO<br> VENTAS X MES</th>
												<th class="bg-success">SUB TOTAL<br> VENTAS</th>
											</tr>
										</thead>
										<tbody>
										<?php 
											$g_totals = array_fill_keys(array_keys($months), ['stock_inicial' => 0, 'sales_qty' => 0, 'sales_amount' => 0]);
											$g_stock_final = 0;
											$g_total_sold_qty = 0;
											$g_total_sold_amount = 0;
											
											foreach ($report_data as $row): 
												$g_stock_final += $row['stock_final'];
												$g_total_sold_qty += $row['total_sold_qty'];
												$g_total_sold_amount += $row['total_sold_amount'];
										?>
											<tr>
												<td><b><?php echo $row['name']; ?></b></td>
												
												<?php foreach($months as $m_key => $m_name): 
													$m_data = $row['months'][$m_key];
													$g_totals[$m_key]['stock_inicial'] += $m_data['stock_inicial'];
													$g_totals[$m_key]['sales_qty'] += $m_data['sales_qty'];
													$g_totals[$m_key]['sales_amount'] += $m_data['sales_amount'];
												?>
													<td class="text-right"><?php echo number_format($m_data['stock_inicial'], 0); ?></td>
													<td class="text-right text-info font-weight-bold"><?php echo number_format($m_data['sales_qty'], 0); ?></td>
													<td class="text-right text-success">S/ <?php echo number_format($m_data['sales_amount'], 2); ?></td>
												<?php endforeach; ?>
												
												<td class="text-right text-primary font-weight-bold"><?php echo number_format($row['stock_final'], 0); ?></td>
												<td class="text-right"><?php echo number_format($row['avg_qty'], 2); ?></td>
												<td class="text-right text-info font-weight-bold"><?php echo number_format($row['total_sold_qty'], 0); ?></td>
												<td class="text-center">
													<?php if($row['estado'] == 'ACTIVO'): ?>
														<span class="badge badge-success">ACTIVO</span>
													<?php else: ?>
														<span class="badge badge-danger">INACTIVO</span>
													<?php endif; ?>
												</td>
												<td class="text-center"><?php echo $row['rotacion']; ?></td>
												<td class="text-right">S/ <?php echo number_format($row['avg_amount'], 2); ?></td>
												<td class="text-right text-success font-weight-bold">S/ <?php echo number_format($row['total_sold_amount'], 2); ?></td>
											</tr>
										<?php endforeach; ?>
										</tbody>
										<tfoot class="bg-dark text-white font-weight-bold text-right">
											<tr>
												<td class="text-center">TOTALES GENERALES</td>
												<?php foreach($months as $m_key => $m_name): ?>
													<td><?php echo number_format($g_totals[$m_key]['stock_inicial'], 0); ?></td>
													<td class="text-info"><?php echo number_format($g_totals[$m_key]['sales_qty'], 0); ?></td>
													<td class="text-success">S/ <?php echo number_format($g_totals[$m_key]['sales_amount'], 2); ?></td>
												<?php endforeach; ?>
												
												<td class="text-primary" style="font-size:14px;"><?php echo number_format($g_stock_final, 0); ?></td>
												<td>-</td>
												<td class="text-info" style="font-size:14px;"><?php echo number_format($g_total_sold_qty, 0); ?></td>
												<td>-</td>
												<td>-</td>
												<td>-</td>
												<td class="text-success" style="font-size:14px;">S/ <?php echo number_format($g_total_sold_amount, 2); ?></td>
											</tr>
										</tfoot>
									</table>
								</div>
							<?php else: ?>
								<div class="alert alert-info">
									<h4><i class="icon fa fa-info"></i> No hay datos</h4>
									No se encontraron movimientos ni productos con stock en el rango de fechas seleccionado.
								</div>
							<?php endif; ?>
						<?php else: ?>
							<div class="alert alert-primary">
								<h4><i class="icon fa fa-calendar"></i> Seleccione un rango de fechas</h4>
								Utilice el formulario superior para generar el reporte Pivot. Se agruparán los meses automáticamente según las fechas.
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>					
		</div>
	</div>
</section>
