<?php
	$orden_id = $_GET["id"];
	$orden = OrdenTrabajoData::getById($orden_id);
	$detalle = DetalleOrdenData::getAllProductsByOrdenIdGroup($orden_id);
	$empresa = EmpresaData::getDatos();
?>
<div class="row" >
	<div class="col-md-10 col-md-offset-1">
		<div class="row ">
			<div class="row pull-right">
				<!-- <a href="" class="btn btn-success">Generar Comprobante</a>
				<button id="imprimir" class="btn btn-md btn-info"><i class="fa fa-print"></i> IMPRIMIR</button> -->
				<form method="post" action="./?action=addfacturao" class="form-inline" onsubmit="return enviado2()">
					<input type="hidden" value="<?php echo $_GET["id"] ?>" name="orden_id">					
					<?php
						if($orden->estado == 0)
						{
							?>
							<div class="input-group">
								<select name="selTipoComprobante" class="form-control" id="selTipoComprobante">
									<option value="1">Factura</option>
									<option value="3">Boleta</option>
								</select>						
							</div>
								<button class="btn btn-md btn-danger" type="submit" id="btnComprobante"><i class="fa fa-file-text"></i> COMPROBANTE</button>
							<?php
						}
					?>					
					<a id="imprimir" class="btn btn-md btn-info" href="#"><i class="fa fa-print"></i> IMPRIMIR</a>
				</form>
			</div>
		</div>
		<div class="row" style="background: #fff; margin-top: 5px">
			<br>
			<div class="para_imprimir">
				<div class="col-md-12 row">
					<div style="width: 70%; text-align: center; float: left;">
						<img src="assets/img/logo.jpg" style="width: 160px; height: 60px">
						<h3 style="padding: 0; margin:0"><b><?php echo $empresa->Emp_RazonSocial ?></b></h3>
						<h5><b><?php echo $empresa->Emp_Descripcion ?></b></h5>
						<p style="margin: 2px;"><?php echo $empresa->Emp_Direccion ?></p>
						<p style="margin: 2px;">Tel.: <?php echo $empresa->Emp_Telefono?></p>
						<p style="margin: 2px;">Cel.: <?php echo $empresa->Emp_Celular?></p>
						<p style="margin: 2px;">Correo: serviciotecnico@amazonserviceperu.com</p>
					</div>
					<div style="width: 30%;float: left;">
						<div style="margin: 15px; text-align: center;">
							<h4>IQUITOS - PERÚ</h4>
							<div style="border: 2px solid black;">
								ORDEN DE TRABAJO:<br>
								<span style="color: #dd4b39; font-weight: bold;"><?php echo generaCerosComprobante($orden->id); ?></span>
								<br>
								F. EMISIÓN <span style=""><?php echo convertir_fecha($orden->created_at); ?></span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12" style="width: 100%">
					<h5><b>DATOS DEL CLIENTE</b></h5>
					<?php
						$cliente = PersonData::getById($orden->person_id);
					?>
					<h5 class="text-center"><b>TIPO DE SERVICIO: <?php if($orden->tipo_servicio == 1){ echo "GARANTÍA"; } else{ echo "PRESUPUESTO"; } ?></b></h5>
					<div style="border: 1px solid black; padding: 10px">
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td>ID CLIENTE:</td>
									<td><?php echo $orden->person_id ?></td>
									<td>CLIENTE:</td>
									<td><?php echo $cliente->name.' '.$cliente->lastname ?></td>
								</tr>
								<tr>
									<td>RUC:</td>
									<td><?php echo $cliente->numero_documento ?></td>
									<td>REPRESENTANTE</td>
									<td></td>
								</tr>
								<tr>
									<td>CELULAR:</td>
									<td><?php echo $cliente->phone1 ?></td>
									<td>CORREO:</td>
									<td><?php echo $cliente->email1 ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-md-12" style="width: 100%">
					<h5><b>DATOS DEL EQUIPO</b></h5>
					<?php
						$activo = ActivoData::getById($orden->activo_id);
					?>
					<div style="border: 1px solid black; padding: 10px">
						<table style="width: 100%;">
							<tbody>
								<tr>
									<td>MODELO/CÓDIGO:</td>
									<td><?php echo $activo->modelo ?></td>
									<td>EQUIPO:</td>
									<td><?php echo $activo->nombre ?></td>
								</tr>
								<tr>
									<td>F. FABRICACIÓN:</td>
									<td><?php echo $activo->fecha_fabricacion ?></td>
									<td>SERIE</td>
									<td><?php echo $activo->serie ?></td>
								</tr>
								<tr>
									<td>DOCUMENTO:</td>
									<td>FT</td>
									<td>TIPO:</td>
									<td><?php echo $activo->tipo ?></td>
								</tr>
								<tr>
									<td>F. COMPRA:</td>
									<td><?php echo $activo->fecha_compra ?></td>
								</tr>
								<tr>
									<td>OBSERVACIONES:</td>
									<td><?php echo $orden->descripcion ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<!-- <div class="col-md-12" style="width: 100%">
					<h5><b>DESCRIPCIÓN DEL PRODUCTO/SERVICIO</b></h5>
					<div style="border: 1px solid black; padding: 10px">
						<?php echo $orden->descripcion.' ';?>
						<b>Costo S/. <?php echo $orden->mano_obra ?></b>
					</div>
				</div> -->
				<div class="col-md-12" style="width: 100%">
					<h5><b>DIAGNÓSTICO Y FALLAS</b></h5>
					<div style="border: 1px solid black; padding: 10px">
						<?php echo $orden->diagnostico ?>
					</div>
				</div>
				<div class="col-md-12" style="width: 100%">
					<h5><b>RELACIÓN DE REPUESTOS INVOLUCRADOS EN EL SERVICIO</b></h5>
					<div>
						<?php
							$detalle = DetalleOrdenData::getAllProductsByOrdenIdGroup($orden_id);
						?>
						<table border="1" style="width: 100%">
							<thead>
								<tr>
									<th>CODIGO</th>
									<th>DESCRIPCIÓN</th>
									<th>CANTIDAD</th>
									<th>P. UNIT</th>
									<th>P. TOTAL</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php
									$total = $orden->mano_obra;

									foreach ($detalle as $det) 
									{
										$product = ProductData::getById($det->product_id);
										$subtotal = $det->q*$det->prec_alt;
										?>
										<tr>
											<td><?php echo $product->barcode ?></td>
											<td><?php echo $product->name ?></td>
											<td><?php echo $det->cantidad ?></td>
											<td><?php echo $det->prec_alt ?></td>
											<td><?php echo $subtotal ?></td>
											<td class="text-center">
												<a href="./?action=deleterepuesto&id=<?php echo $det->id; ?>&orden_id=<?php echo $orden->id; ?>" class="btn btn-xs btn-danger eliminar_repuesto"><i class="fa fa-trash"></i></a>
											</td>
										</tr>
										<?php
										$total = $subtotal + $total;
									}
								?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="4" class="text-right"><b>TOTAL</b>&nbsp; &nbsp; </td>
									<td><b>S/ <?php echo $total?></b></td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
				<div class="col-md-12" style="width: 100%; margin-top: 10px">
					<div style="border: 1px solid black; padding: 10px">
						FECHA DE EVALUACIÓN: <?php echo convertir_fecha($orden->created_at) ?> <br>
						TÉCNICO EVALUADOR: <?php echo $orden->tecnico_evaluador ?> <br>
						FECHA DE REPARACIÓN: <?php echo $orden->fecha_evaluacion ?> <br>
						FECHA DE ENTREGA: <?php echo $orden->fecha_evaluacion ?> <br>
					</div>
				</div>
				<div class="col-md-12" style="width: 100%; margin-top: 60px; text-align: center;">
					<div style="padding: 10px; width: 50%; float: left;">
						<hr style="border: 1px solid black; width: 60%">
						SERVICIO TÉCNICO AUTORIZADO <br>
						JEFE DEL TALLER
					</div>
					<div style="padding: 10px; width: 50%; float: left;">
						<hr style="border: 1px solid black; width: 60%">
						FIRMA <br>
						NOMBRE/CLIENTE: <?php echo $cliente->name.' '.$cliente->lastname ?> <br>
						N° DOCUMENTO: <?php echo $cliente->numero_documento ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div><!--  fin col-md-6 -->
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

	function convertir_fecha($fecha)
	{
	    $date = date_create($fecha);
	    return date_format($date, 'd-m-Y');
	}


?>
<script>
	function enviado2() 
	{
	    go = confirm("¿Seguro que desea emitir el comprobante, la accion no podrá ser revertida ?");
	     
	    if(go == true)
	    {
	        if (cuenta == 0)
	        {
	          cuenta++;
	          return true;
	        }
	        else
	        {
	          alert("El formulario ya está siendo enviado, por favor aguarde un instante.");
	          return false;
	        }
	    }
	    else
	    {
	        return false;
	    }	    
	}

  $(document).ready(function(){
	    $("#product_code").keydown(function(e){
	        if(e.which==17 || e.which==74 ){
	            e.preventDefault();
	        }else{
	            console.log(e.which);
	        }
	    })

	    $('#imprimir').click(function() {
	    	$('#imprimir').hide();
	    	$('#btnComprobante').hide();
	    	$('#selTipoComprobante').hide();
	      	$('#div_opciones').hide();
	      	$('.logo').hide();
	      	$('.eliminar_repuesto').hide();
	      	window.print();

	      	$('#imprimir').show();
	      	$('#div_opciones').show(); 
	      	$('#btnComprobante').show();
	      	$('#selTipoComprobante').show();
	      	$('.eliminar_repuesto').show();
	      	$('.logo').show(); 
	    });
	});
</script>