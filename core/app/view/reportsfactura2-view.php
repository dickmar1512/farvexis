<section class="content">
<div class="row">
	<div class="col-md-12">
		<h1>Reporte de Facturas</h1>
		<form>
			<input type="hidden" name="view" value="reportsfactura2">
			<div class="row">
				<?php
					$series = Factura2Data::get_series();
				?>
				<div class="col-md-2">
					<select name="selSerie" class="form-control">
						<option value="0">:: Seleccione ::</option>
						<?php
							foreach ($series as $serie) {
								?>
									<option value="<?php echo $serie->SERIE?>"><?php echo $serie->SERIE?></option>
								<?php
							}
						?>
					</select>
				</div>
				<div class="col-md-2">
					<input type="text" name="comprobante" placeholder="N° comprobante" class="form-control">
				</div>
				<div class="col-md-2">
					<input type="date" name="sd" value="<?php if(isset($_GET["sd"])){ echo $_GET["sd"]; } else { echo date("Y-m-d"); }?>" class="form-control">
				</div>
				<div class="col-md-2">
					<input type="date" name="ed" value="<?php if(isset($_GET["ed"])){ echo $_GET["ed"]; } else { echo date("Y-m-d"); } ?>" class="form-control">
				</div>
				<div class="col-md-2">
					<input type="submit" class="btn btn-primary btn-block" value="Procesar">
				</div>
			</div>
		</form>
	</div>
</div>
<br>
<!--- -->
<div class="row">	
	<div class="col-md-12">
		<?php if(isset($_GET["sd"]) && isset($_GET["ed"]) && isset($_GET["selSerie"]) && isset($_GET["comprobante"])):?>
			<?php if($_GET["sd"]!=""&&$_GET["ed"]!=""):?>
			<?php 
				$facturas = array();				
				$facturas = Factura2Data::get_facturas_x_fecha($_GET["sd"],$_GET["ed"], $_GET["selSerie"], $_GET["comprobante"]);
			?>
		<?php if(count($facturas) > 0):?>
			<button id="imprimir" class="btn btn-md btn-info"><i class="fa fa-print"></i> IMPRIMIR</button>
		<table class="table table-bordered">
			<?php 
				$subtotal = 0;
				$descuento = 0;
				$total = 0;
				$nro = 0;
			?>
			<thead>
				<th>N°</th>
				<th>SERIE</th>
				<th>COMPROBANTE</th>
				<th>FECHA EMISIÓN</th>
				<th>N° DOC USUARIO</th>
				<th>RAZÓN SOCIAL</th>
				<th>DETALLE</th>
				<th>PRECIO VENTA</th>
				<th class="text-center boton">OPCIONES</th>
			</thead>
			<?php
				foreach($facturas as $fac)
				{
					$nro++;
					?>
						<tr>
							<td><?php echo $nro ?></td>
							<td><?php echo $fac->SERIE ?></td>
							<td><?php echo $fac->COMPROBANTE ?></td>
							<td><?php echo $fac->fecEmision ?></td>
							<td><?php echo $fac->numDocUsuario ?></td>
							<td><?php echo $fac->rznSocialUsuario ?></td>
							<td>
								<?php 
									$operations = OperationData::getAllProductsBySellId($fac->EXTRA1);

									foreach ($operations as $ope) 
									{
										$product = ProductData::getById($ope->product_id);
										echo $product->name;

										if($ope->descripcion != "")
										{
											echo "|".$ope->descripcion;
										}

										echo "<br>";
									}
								?>						
							</td>
							<td><?php echo 'S/ '.$fac->sumPrecioVenta ?></td>
							<td class="text-center boton">
								<!-- <a href="SFS_v1.1/sunat_archivos/sfs/REPO/<?php echo $fac->RUC ?>-01-<?php echo $fac->SERIE ?>-<?php echo $fac->COMPROBANTE ?>.pdf" class="btn btn-info btn-xs" target="_blank"><i class="fa fa-file-pdf">Ver</i> PDF</a> -->
								<a href="<?= url('onesell2t', ['id' => $fac->EXTRA1]) ?>" class="btn btn-info btn-xs"><i class="fa fa-file-pdf">Ver</i></a>
								<a href="<?= url('noc2', ['id' => $fac->EXTRA1]) ?>" class="btn btn-info btn-xs btn-warning"><i class="fa fa-file-pdf">N.C.</i></a>
								<a href="<?= url('nodf', ['id' => $fac->EXTRA1]) ?>" class="btn btn-info btn-xs btn-default"><i class="fa fa-file-pdf">N.D.</i></a>
							</td>
						</tr>
					<?php
					$total = $fac->sumPrecioVenta + $total;
				}
				
			?>
			<tfoot>
				<td colspan="7"></td>
				<td><?php echo 'S/ '. number_format($total,2,'.',',');?></td>
			</tfoot>
		</table>
		<h1>Total: S/ <?php echo number_format($total,2,'.',','); ?></h1>

		<?php else:
		// si no hay operaciones
		?>
	<script>
		$("#wellcome").hide();
	</script>
	<div class="jumbotron">
		<h2>No hay facturas</h2>
		<p>El rango de fechas seleccionado no proporciono ningun resultado de facturas.</p>
	</div>
	<?php endif; ?>
	<?php else:?>
	<script>
		$("#wellcome").hide();
	</script>
	<div class="jumbotron">
		<h2>Fecha Incorrectas</h2>
		<p>Puede ser que no selecciono un rango de fechas, o el rango seleccionado es incorrecto.</p>
	</div>
	<?php endif;?>

	<?php endif; ?>
	</div>
</div>

<br><br><br><br>
</section>
<script type="text/javascript">
	$('#imprimir').click(function() {
	    	imprimir();
	    });
	function imprimir()
    {
    	$('#imprimir').hide();
      	$('.boton').hide();
      	// $('.logo').hide();
      	window.print();

      	$('#imprimir').show();
      	$('.boton').show(); 
      	// $('.logo').show(); 
    }
</script>