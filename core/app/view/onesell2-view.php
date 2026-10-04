<?php
	$product = Factura2Data::getByExtra($_GET["id"]);

	$comp_cab = CabData::getById($product->id, 1);
	$comp_aca = AcaData::getById($product->id, 1);

	$detalles = DetData::getById($product->id, 1);
	$comp_tri = TriData::getById($product->id, 1);
	$comp_ley = LeyData::getById($product->id, 1);

	$sell = SellData::getById($product->EXTRA1);
    //$mesero = UserData::getById($sell->mesero_id);
    //$cajero= null;
    //$cajero = UserData::getById($sell->user_id);
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
					<tr>
						<td >
				      		<center>
								<img src="assets/img/logo2.jpg" width="30%" height="20%" class="img-circle"/>
</center>
						</td>
				    </tr>
				    <tr>		
						<td style="font-family: courier new; font-size: 0.9em"><b><?php echo $empresa->Emp_RazonSocial ?></b>
					</td>
					</tr>
					<tr>		
					      	<h5><b><?php echo $empresa->Emp_Descripcion ?></b></h5>
					      	<p style="margin: 2px;"><?php echo $empresa->Emp_Direccion ?></p>
					      	<p style="margin: 2px;">Telf:<?php echo $empresa->Emp_Telefono ?></p>
						      	<p style="margin: 2px;">Cel.: <?php echo $empresa->Emp_Celular?></p>
					      	<h5  style="margin-top: 2px;">SOFTWARE YAQHA v1.2 - SUNAT v1.2 - UBL 2.1</h5>
						</td>
						<td style="text-align: center; width: 230px; border-color: #222; border-width: 20px; margin-top: 0px; padding-top: 0px">
							<div class="row" >
					      	<h2><b>RUC: <?php echo $empresa->Emp_Ruc ?></b></h2>
					      	<div style=" color: #FFF">
					      		<img src="assets/img/fac.png" style="width: 90%;">
					      	</div>
					      	<div><h2><?php echo $product->SERIE."-".$product->COMPROBANTE; ?></h2></div>
					      	</div>
				  		</td>
					</tr>
				</table>
				<table>
					<tr>
					    <td></td>
						<td>
							<div class="container" style="width: 60px">
								<p><b>RUC</b></p>
							</div>
				    	</td>
						<td class="container" style="width: 100px">
							<p><?php echo ": ".$comp_cab->numDocUsuario; ?></p>
						</td>
				        <td>
				        	<div class="container" style="width: 120px">
				        		<p><b>Razón Social</b></p>
				        	</div>
				    	</td>
				        <td class="container" style="width: 130px">
				        	<p><?php echo ": ".$comp_cab->rznSocialUsuario; ?></p>
				        </td>
				        <td>
				        	<p><?php echo "<b>Dirección</b>: ".$comp_aca->desDireccionCliente; ?></p>
				        </td>
				        <td>
				        	<div  class="container" style="width: 100px"><p><b>FEC. EMIS.</b></p></div>
				        </td>
				        <td class="container" style="width: 130px">
				        	<p><?php echo ": ".$comp_cab->fecEmision." | ".$comp_cab->horEmision; ?></p>
				        </td>			        
					</tr>
				</table>
				<table class="table-bordered" style="max-width: 900px">
					<thead class="thead-dark">
						<th style="width: 50px">CANTIDAD</th>
						<th style="width: 450px">DESCRIPCION</th>
						<th style="width: 200px">PRECIO UNIT.</th>
						<th style="width: 200px">IMPORTE</th>
					</thead>
					<tbody>
						<?php
							$total = 0;
							foreach ($detalles as $det) {
								?>
									<tr>
										<td><?php echo $det->ctdUnidadItem; ?></td>
										<td><?php echo $det->desItem; ?></td>
										<td><?php echo $det->mtoValorUnitario; ?></td>
										<td><?php echo $det->mtoValorVentaItem; ?></td>
									</tr>
								<?php
								$total = $det->mtoValorVentaItem + $total;
							}
						?>
					</tbody>
				</table>
				<table class="table table-bordered"  style="max-width: 900px; margin-bottom: 0px; padding-bottom:  0px ">
					<thead style="align-content: center; text-align: center; border-style: none">
						<th style="align-content: center; text-align: center;">
							<table>
								<tr scope="col"><td><b><?php echo $comp_ley->desLeyenda; ?></b></td></tr>
								<tr><td style="font-size: 11px">Consulte y/o descargue su comprobante electronico en www.sunat.gob.pe, utilizando su clave SOL</td></tr>
								<tr><td style="font-size: 11px"><p>Autorizado para ser emisor electrónico mediante la Resolución de Superintendencia N° 155-2017</p></td></tr>
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
