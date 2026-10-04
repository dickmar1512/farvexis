<div class="row">
	<h2>Ordenes de Servicio</h2>
	<div class="col-md-12">
		<div class="row pull-right">
			<a  href="./?view=neworden" class="btn btn-success">GENERAR ORDEN DE TRABAJO</a>
		</div>
	</div>	
	<div class="col-md-12">
		<?php
			$ordenes = OrdenTrabajoData::getAll();
		?>
		<div>
			<table class="table">
				<thead>
					<tr>
						<th>N°</th>
						<th>TIPO DE SERVICIO</th>
						<th>DESCRIPCIÓN</th>
						<th>CLIENTE</th>
						<th>ESTADO</th>
						<th>OPIONES</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($ordenes as $orden)
						{
							$cliente = PersonData::getById($orden->person_id);

							?>
								<tr>
									<td><?php echo generaCerosComprobante($orden->id) ?></td>
									<td>
										<?php
											if($orden->tipo_servicio == 1)
											{
												echo "Garantía";
											}
											else if($orden->tipo_servicio == 2)
											{
												echo "Presupuesto";
											}
										?>
									</td>
									<td><?php echo $orden->descripcion ?></td>
									<td><?php echo $cliente->name.' '.$cliente->lastname ?></td>
									<td>
										<?php
											if($orden->estado == 0)
											{
												echo "Pendiente";
											}
											else if($orden->estado == 1)
											{
												echo "Comprobante Generado";
											}
										?>
									</td>
									<td>
										<a href="<?= url('oneorden', ['id' => $orden->id]) ?>" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-eye-open"></i></a>
										<?php
											if($orden->estado == 0)
											{
												?>
													<a href="<?= url('editarordentrabajo', ['id' => $orden->id]) ?>" class="btn btn-xs btn-default"><i class="fa fa-edit"></i></a>
													<a href="<?= url('repuestosordentrabajo', ['id' => $orden->id]) ?>" class="btn btn-xs btn-default"><i class="fa fa-plus"></i></a>
												<?php
											}
										?>
										
									</td>
								</tr>
							<?php
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
	function generaCerosComprobante($numero)
	{
	    $largo_numero = strlen($numero);
	    $largo_maximo = 6;
	    $agregar = $largo_maximo - $largo_numero;
	    for($i = 0; $i < $agregar; $i++)
	    {
	    	$numero = "0".$numero;
	    }
	    return $numero;
	}
?>