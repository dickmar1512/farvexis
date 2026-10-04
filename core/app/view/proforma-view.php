<?php
	$sell = SellData::get_ventas_x_id($_GET["id"]);

	$operations = OperationData::getAllProductsBySellId($sell->id);

	$empresa = EmpresaData::getDatos();

	function generaCerosComprobante($numero)
	{
	    $largo_numero = strlen($numero); //OBTENGO EL LARGO DEL NUMERO
	    $largo_maximo = 8; //ESPECIFICO EL LARGO MAXIMO DE LA CADENA
	    $agregar = $largo_maximo - $largo_numero; //TOMO LA CANTIDAD DE 0 AGREGAR
	    for($i =0; $i<$agregar; $i++)
	    {
	      $numero = "0".$numero;
	    } //AGREGA LOS CEROS
	    return $numero; //RETORNA EL NUMERO CON LOS CEROS
	}
?>
<div class="row" style="margin-top: 0px; padding-top: 0px; background: #fff">
	<div class="col-md-10 col-md-offset-1">
		<div class="row ">
			<div class="row pull-right">
				<?php 
					$pagina_action = ($sell->tipo_comprobante == 1) ? 'addfacturap' : 'addboletap';
				?>
				<input type="hidden" id="proforma_sell_id" value="<?php echo $_GET['id'] ?>">
				<input type="hidden" id="proforma_action" value="<?php echo $pagina_action ?>">
				<button class="btn btn-md btn-danger" type="button" id="venta" onclick="procesarVentaProforma()"><i class="fa fa-shopping-cart"></i> VENTA</button>
				<a id="imprimir" class="btn btn-md btn-info" href="#"><i class="fa fa-print"></i> IMPRIMIR</a>
			</div>
		</div>
		<div class="row">
			<div class="para_imprimir">
				<br>
				<div>
					<table style="margin-top: 0px; padding-top: 0px">
						<tr style="margin-top: 0px; padding-top: 0px">
							<td style="text-align: center; width: 180px; margin-top: -5px; padding-top: 0px">
					      		<img src="assets/img/logo.jpg" style="height: 150px; width: 100%"><br>
							</td>
							<td style="text-align: center; color: green; margin-top: 0px; padding-top: 0px; width: 420px">
								<h2 style="margin-top: 0px; padding-top: 0px"><b><?php echo $empresa->Emp_RazonSocial ?></b></h2>
						      	<h5><b><?php echo $empresa->Emp_Descripcion ?></b></h5>
						      	<p style="margin: 2px;"><?php echo $empresa->Emp_Direccion ?></p>
						      	<p style="margin: 2px;">Cel.: <?php echo $empresa->Emp_Celular?></p>
						      	<h5  style="margin-top: 2px;">SOFTWARE YAQHA v1.2 - SUNAT v1.2 - UBL 2.1</h5>
							</td>
							<td style="text-align: center; width: 230px; border-color: #222; border-width: 20px; margin-top: 0px; padding-top: 0px">
								<div class="row" >
						      	<h2><b>RUC: <?php echo $empresa->Emp_Ruc ?></b></h2>
						      	<h3 style="border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; padding: 3px 0px"><b>PROFORMA</b></h3>
						      	<div><h2><?php echo "P-".generaCerosComprobante($sell->id) ?></h2></div>
						      	</div>
					  		</td>
						</tr>
					</table>
					<table>
						<tr>
						    <td></td>
							<td>
								<div class="container" style="width: 50px">
									<p><b>DNI</b></p>
								</div>
					    	</td>
							<td class="container" style="width: 120px">
								<p>
									<?php 
									if ($sell->numero_documento == '') 
									{
										echo "00000000";
									}
									else
									{
										echo $sell->numero_documento; 
									}
									?>
								</p> 
							</td>
					        <td>
					        	<div class="container" style="width: 100px">
					        		<p><b>NOMBRE:</b></p>
					        	</div>
					    	</td>
					        <td class="container" style="width: 250px">
					        	<p><?php echo $sell->name; ?></p>
					        </td>
					        <td></td>
					        <td>
					        	<div  class="container" style="width: 100px"><p><b>FEC. EMIS.</b></p></div>
					        </td>
					        <td class="container">
					        	<p><?php echo ": ".$sell->created_at; ?></p>
					        </td>
						</tr>
					</table>
					<table class=" table-bordered" style="max-width: 900px">
						<thead class="thead-dark">
							<th style="width: 50px">CANTIDAD</th>
							<th style="width: 450px">DESCRIPCION</th>
							<th style="width: 200px">PRECIO UNIT.</th>
							<th style="width: 200px">IMPORTE</th>
						</thead>
						<tbody>
							<?php
								$total = 0;
								foreach ($operations as $ope) 
								{
									$product = ProductData::getById($ope->product_id);
									$subtotal = $ope->q*$ope->prec_alt;
									?>
										<tr>
											<td><?php echo $ope->q; ?></td>
											<td><?php echo $product->name; ?></td>
											<td><?php echo $ope->prec_alt; ?></td>
											<td><?php echo $subtotal; ?></td>
										</tr>
									<?php
									$total = $subtotal + $total;
								}
							?>
						</tbody>						
						<tfoot>
							<tr>
								<td colspan="3"><i>SON <?php echo NumeroLetras::convertir(number_format($total, 2, '.', ','), 'soles', 'centimos'); ?></i></td>
								<td>S/ <?php echo number_format($total, 2, '.', ','); ?></td></tr>
							</tr>
						</tfoot>
					</table>
					
				</div>
			</div>
		</div>
		
		<!-- <div class="row pull-right" id="div_opciones" style="display: show">
			<label>Opciones: </label>
			<br>
			<a href="./?view=1.1_boleta" class="btn btn-primary"><i class="fa fa fa-plus-circle"></i> Generar nueva factura</a>
			<a href="./?view=reporte_boleta" class="btn btn-primary"><i class="fa fa-mail-forward"></i> Ir a Reporte</a>			
		</div> -->
	</div>
</div><!--  fin col-md-6 -->

<script>
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
	      	$('#div_opciones').hide();
	      	$('.logo').hide();
	      	$('#venta').hide();
	      	window.print();

	      	$('#imprimir').show();
	      	$('#div_opciones').show(); 
	      	$('#venta').show(); 
	      	$('.logo').show(); 
	    });
	});

	function procesarVentaProforma() {
		var sellId = $('#proforma_sell_id').val();
		var action = $('#proforma_action').val();
		Swal.fire({
			title: '¿Confirmar Venta?',
			text: 'Se procesará la proforma como venta.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#6c757d',
			confirmButtonText: 'Sí, procesar',
			cancelButtonText: 'Cancelar'
		}).then(function(result) {
			if (result.isConfirmed) {
				Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
				$.ajax({
					type: 'POST',
					url: './?action=' + action,
					data: { sell_id: sellId },
					dataType: 'json',
					success: function(response) {
						if (response.status === 'success') {
							Swal.fire({ icon: 'success', title: '¡Listo!', text: response.message, timer: 1500, showConfirmButton: false })
								.then(function() { window.location.href = response.redirect; });
						} else {
							Swal.fire({ icon: 'error', title: 'Error', text: response.message });
						}
					},
					error: function() {
						Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo comunicar con el servidor.' });
					}
				});
			}
		});
	}
</script>