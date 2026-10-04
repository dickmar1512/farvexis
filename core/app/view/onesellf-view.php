<?php
	$sell = SellData::get_ventas_x_id($_GET["id"]);
	$operations = OperationData::getAllProductsBySellId($sell->id);
    $empresa = EmpresaData::getDatos();
?>
<div class="row" style="margin-top: 0px; padding-top: 0px; background: #fff">
	<div class="col-md-10 col-md-offset-1">
		<div class="row ">
			<div class="row pull-right">
				<button id="imprimir" class="btn btn-md btn-info"><i class="fa fa-print"></i> IMPRIMIR</button>
			</div>
		</div>
		<div class="row">
			<div class="para_imprimir">
				<br>
				<table style="margin-top: 0px; padding-top: 0px">
					<tr style="margin-top: 0px; padding-top: 0px">
						<td style="text-align: center; width: 200px; margin-top: -5px; padding-top: 0px">
				      		<img src="assets/img/logo1.jpg" style="height: 100px; width: 100%"><br>
						</td>
						<td style="text-align: center; color: green; margin-top: 0px; padding-top: 0px; width: 420px">
							<h2 style="margin-top: 0px; padding-top: 0px"><b><?php echo $empresa->Emp_RazonSocial ?></b></h2>
					      	<h6><b><?php echo $empresa->Emp_Descripcion ?></b></h6>
					      	<p style="margin: 2px;"><?php echo $empresa->Emp_Direccion ?></p>
					      	<p style="margin: 2px;">Telf.: <?php echo $empresa->Emp_Telefono?></p>
					      	<p style="margin: 2px;">Correo.: <?php echo $empresa->Emp_Celular?></p>
						</td>
						<td style="text-align: center; width: 230px; border-color: #222; border-width: 20px; margin-top: 0px; padding-top: 0px">
							<div class="row" >
					      	<h3><b>RUC: <?php echo $empresa->Emp_Ruc ?></b></h3>
					      	<div style="font-weight: bold; font-size: 20px">
					      		BOLETA DE VENTA
					      	</div>
					      	<h2><?php echo $sell->serie."-".$sell->comprobante; ?></h2>
					      	</div>
				  		</td>
					</tr>
				</table>
				<table class="table">
					<tr>
					    <td>
							<b>RUC</b> <?php echo ": ".$sell->numero_documento; ?>
				    	</td>
				    	<td>
							<b>Nombre</b> <?php echo ": ".$sell->name; ?>
				    	</td>
				    </tr>
				    <tr>
				        <td>
							<b>Dirección</b> <?php echo ": ".$sell->address1; ?>
				    	</td>
				        <td>
				        	<b> FEC. EMIS.</b> <?php echo ": ".$sell->created_at; ?>
				        </td>
					</tr>
				</table>
				<table class="table-bordered" style="max-width: 900px">
					<thead class="thead-dark">
						<th style="width: 50px">CANTIDAD</th>
						<th style="width: 50px">&nbsp;&nbsp;CODIGO</th>
						<th style="width: 450px">&nbsp;&nbsp;DESCRIPCION</th>
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
										<td>&nbsp;&nbsp;<?php echo $product->barcode; ?></td>
										<td>&nbsp;&nbsp;<?php echo utf8_encode($product->name); ?></td>
										<td><?php echo $ope->prec_alt; ?></td>
										<td><?php echo number_format($subtotal, 2, '.', ','); ?></td>
									</tr>
								<?php
								$total = $subtotal + $total;
							}
						?>
					</tbody>
				</table>
				<table class="table table-bordered"  style="max-width: 900px; margin-bottom: 0px; padding-bottom:  0px ">
					<thead style="align-content: center; text-align: center; border-style: none">
						<th style="align-content: center; text-align: center;">
							<table>
								<tr scope="col"><td><b>BIENTES TRANSFERIDOS EN LA AMAZONÍA PARA SER CONSUMIDOS EN LA MISMA <br>
								SERVICIOS PRESTADOS EN LA AMAZONÍA</b></td></tr>
							</table>
						</th>	
						<th>
							<table>
								<tr>
									<td style="width: 270px;">OP. GRATUITA</td>
									<td style="min-width: 190px">S/ 0.00</td></tr>
								<tr>
									<td>OP. EXONERADA</td>
									<td>S/ <?php echo number_format($total, 2, '.', ','); ?></td></tr>
								<tr>
									<td>OP. INAFECTA</td>
									<td>S/ 0.00</td></tr>
								<tr>
									<td>OP. GRAVADA</td>
									<td>S/ 0.00</td></tr>
								<tr>
									<td>IGV</td>
									<td>S/ 0.00</td></tr>
								<tr>
									<td>MONTO TOTAL</td>
									<td>S/ <?php echo number_format($total, 2, '.', ','); ?></td>
								</tr>
							</table>
						</th>
					</thead>
				</table>
			</div>
		</div>
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
	      	window.print();

	      	$('#imprimir').show();
	      	$('#div_opciones').show(); 
	      	$('.logo').show(); 
	    });
	});
</script>