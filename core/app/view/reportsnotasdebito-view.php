<section class="content">
<div class="row">
	<div class="col-md-12">
		<h1><i class="fa fa-file"></i> Reporte de Notas de débito de Factura</h1>
		<form>
			<input type="hidden" name="view" value="reportsnotasdebito">
			<div class="row">
				<?php
					$series = Factura2Data::get_series_notas_debito();
				?>
				<div class="col-md-2 hide">
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
				$facturas = NotData::get_notas_debito_factura_x_fecha($_GET["sd"],$_GET["ed"]);
			?>
		<?php if(count($facturas) > 0):?>
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
				<th>DOC MODIFICADO</th>
				<th>N° DOC USUARIO</th>
				<th>RAZÓN SOCIAL</th>
				<th>PRECIO VENTA</th>
				<th class="text-center">OPCIONES</th>
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
							<td><?php echo $fac->serieDocModifica ?></td>
							<td><?php echo $fac->numDocUsuario ?></td>
							<td><?php echo $fac->rznSocialUsuario ?></td>
							<td><?php echo 'S/ '.$fac->sumPrecioVenta ?></td>
							<td class="text-center">
								<?php 
								$nota_debito =Factura2Data::getByID($fac->ID_TIPO_DOC);
								 ?>
								<!-- <a href="SFS_v1.1/sunat_archivos/sfs/REPO/<?php echo $fac->RUC ?>-01-<?php echo $fac->SERIE ?>-<?php echo $fac->COMPROBANTE ?>.pdf" class="btn btn-info btn-xs" target="_blank"><i class="fa fa-file-pdf">Ver</i> PDF</a> -->
								<a href="./?view=notadebitot&num=<?php echo $nota_debito->SERIE . '-' . $nota_debito->COMPROBANTE ?>" class="btn btn-info btn-xs"><i class="fa fa-file-pdf">Ver</i></a>
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
		<h2>No hay notas de débito</h2>
		<p>El rango de fechas seleccionado no proporciono ningun resultado de notas.</p>
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
