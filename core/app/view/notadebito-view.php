<?php
	
	$product = Factura2Data::getByNumDoc($_GET["num"]);
	$comp_cab = NotData::getById($product->id, 8);
	// $comp_aca = AcaData::getById($product->id, 1);
	$detalles = DetData::getById($product->id, 8);
	$comp_tri = TriData::getById($product->id, 8);
	$comp_ley = LeyData::getById($product->id, 8);

	// print_r($comp_cab );
	// print_r($detalles );
	// print_r($comp_tri );
	// print_r($comp_ley );

	$sell = SellData::getByNroDoc($comp_cab->serieDocModifica);
    //$mesero = UserData::getById($sell->mesero_id);
    //$cajero= null;
    //$cajero = UserData::getById($sell->user_id);
    $empresa = EmpresaData::getDatos();

    $datosComprobante = [
        "venta" => $product ? ["SERIE" => $product->SERIE, "COMPROBANTE" => $product->COMPROBANTE] : [],
        "detalles" => $detalles,
        "comp_cab" => $comp_cab,
        "empresa" => $empresa,
        "cajero" => ($sell && isset($sell->user_id)) ? UserData::getById($sell->user_id)->username : "",
        "sell" => $sell,
        "numLetra" => ""
    ];
?>
<div class="row" style="margin-top: 0px; padding-top: 0px; background: #fff">
	<div class="col-md-10 col-md-offset-1">
		<div class="row">
			<div class="col-md-12 text-right">
				<div class="btn-group" role="group" aria-label="Impresión de nota de débito">
					<button id="imprimir80mm" class="btn btn-md btn-info"><i class="fa fa-print"></i> IMPRIMIR 80mm</button>
					<button id="imprimirA4" class="btn btn-md btn-success"><i class="fa fa-print"></i> IMPRIMIR A4</button>
					<button id="imprimirA5" class="btn btn-md btn-warning"><i class="fa fa-print"></i> IMPRIMIR A5</button>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="para_imprimir">
				<br>
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
					      	<h5 style="">FEC. EMIS.: <?php echo ": ".$comp_cab->fecEmision." | ".$comp_cab->horEmision; ?></h5>
						</td>
						<td style="text-align: center; width: 230px; border-color: #222; border-width: 20px; margin-top: 0px; padding-top: 0px">
							<div class="row" >
					      	<h2><b>RUC: <?php echo $empresa->Emp_Ruc ?></b></h2>
					      	<div style=" color: #FFF">
					      		<img src="assets/img/fac.png" style="width: 90%;">
					      	</div>
					      	<div><h2><?php echo $product->SERIE."-".$product->COMPROBANTE; ?></h2></div>
					      	</div>
					      	<div style=" color: red"><h5><b><?php if($comp_cab->codTipoNota==1){ echo("Intereses por mora");} ?></b></h5></div>
					      	</div>
				  		</td>
					</tr>
				</table>
				<label>Documento que modifica:</label>
				<table>
					<tr>
					    <td><div class="container" style="width: 100px">
								<p><b>Factura Electrónica</b></p>
							</div></td>
						<td>
						<td><div class="container" style="width: 130px">
								<p><b>:<?php echo $comp_cab->serieDocModifica; ?></b></p>
							</div></td>
						<td>
							<div class="container" style="width: 60px">
								<p><b>RUC</b></p>
							</div>
				    	</td>
						<td class="container" style="width: 120px">
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
					</tr>
				</table>
				<table>
					<tr>
						<td><div class="container" style="width: 200px">
								<p><b>Motivo</b></p>
							</div></td>
						 <td class="container" style="width: 400px">
				        	<p><?php echo ": ".$comp_cab->descMotivo; ?></p>
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
									<td><?php if($comp_cab->codTipoNota==1 ){  echo('S/'); echo number_format($total, 2, '.', ',');} else { echo("S/ 0.00");}?></td></tr>
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
									<td><?php if($comp_cab->codTipoNota==1){  echo('S/'); echo number_format($total, 2, '.', ',');} else { echo("S/ 0.00");}?></td>
								</tr>				
							</table>
						</th>
					</thead>
				</table>
			</div>
		</div>
	</div>
</div><!--  fin col-md-6 -->

<input type="hidden" id="datosComprobante" value="<?= htmlspecialchars(json_encode($datosComprobante, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8'); ?>">

<script>
  $(document).ready(function(){
	    $("#product_code").keydown(function(e){
	        if(e.which==17 || e.which==74 ){
	            e.preventDefault();
	        }else{
	            console.log(e.which);
	        }
	    })

		    $('#imprimir80mm').click(function() {
		    	$('#imprimir80mm').hide();
		    	$('#imprimirA4').hide();
		    	$('#imprimirA5').hide();
	      	$('#div_opciones').hide();
	      	$('.logo').hide();
	      	window.print();

		      $('#imprimir80mm').show();
		      $('#imprimirA4').show();
		      $('#imprimirA5').show();
	      	$('#div_opciones').show(); 
	      	$('.logo').show(); 
	    });
	});
</script>

