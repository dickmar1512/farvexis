<?php
	$product = BoletaData::getByExtra($_GET["id"]);

	$comp_cab = CabData::getById($product->id, 3);
	$comp_aca = AcaData::getById($product->id, 3);
	$detalles = DetData::getById($product->id, 3);
	$comp_tri = TriData::getById($product->id, 3);
	$comp_ley = LeyData::getById($product->id, 3);

	$sell = SellData::getById($product->EXTRA1);
    //$mesero = UserData::getById($sell->mesero_id);
    $cajero= null;    
    $cajero = UserData::getById($sell->user_id);

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
			<table style="margin-top: 0px; padding-top: 0px">
				<tr style="margin-top: 0px; padding-top: 0px">
					<td style="text-align: center; width: 260px; margin-top: 0px; padding-top: 0px">
			      		<img src="assets/img/logo.jpg" style="height: 120px; width: 100%"><br>
					</td>
					<td style="color: green; margin-top: 0px; padding-top: 0px; width: 360px;">
						<h4 style="margin-top: 0px; padding-top: 0px"><b><?php echo $empresa->Emp_RazonSocial; ?></b></h4>
				      	<span style="margin: 0px;">Dirección: <?php echo $empresa->Emp_Direccion; ?></span><br>
				      	<h5  style="margin-top: 0px;">SOFTWARE YAQHA v1.2 - SUNAT v1.1 - UBL 2.1</h5>
					</td>
					<td style="width: 100px"></td>
					<td style="text-align: center; width: 300px; border-color: #222; border-width: 20px; margin-top: 0px; border: 1px solid #ccc; padding: 2px">						
				      	<h4><b>R.U.C. N° <?php echo $empresa->Emp_Ruc; ?></b></h4>
				      	<h4><b>BOLETA DE VENTA ELECTRÓNICA</b></h4>
				      	<h4><b><?php echo $product->SERIE."-".$product->COMPROBANTE; ?></b></h4>				      	
			  		</td>
				</tr>
			</table>
			<table>
				<tbody>
					<tr>
						<span>Señor(es): <?php echo $comp_cab->rznSocialUsuario; ?></span><br>
						<span>Dirección: <?php echo $comp_aca->desDireccionCliente; ?></span><br>
						<span>DNI: <?php echo $comp_cab->numDocUsuario; ?></span><br>
						<span>Fecha: <?php echo ": ".$comp_cab->fecEmision." | ".$comp_cab->horEmision; ?></span><br>
					</tr>
				</tbody>
			</table>
			<br>
			<table style="width: 100%">
				<thead class="thead-dark">
					<th scope="col" style="border: 1px solid black">CANTIDAD</th>
					<th scope="col" style="border: 1px solid black">DESCRIPCION</th>
					<th scope="col" style="border: 1px solid black">PRECIO UNITARIO</th>
					<th scope="col" style="border: 1px solid black">IMPORTE</th>
				</thead>
				<tbody>
					<?php
						$total = 0;
						foreach ($detalles as $det) {
							?>
								<tr>
									<td><?php echo $det->ctdUnidadItem; ?></td>
									<td><?php echo utf8_encode($det->desItem); ?></td>
									<td><?php echo $det->mtoValorUnitario; ?></td>
									<td><?php echo $det->mtoValorVentaItem; ?></td>
								</tr>
							<?php
							$total = $det->mtoValorVentaItem + $total;
						}
					?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2"></td>
						<td style="border: 1px solid black">OP. GRATUITA</td>
						<td style="border: 1px solid black">S/ 0.00</td></tr>
					<tr>
						<td colspan="2"></td>
						<td style="border: 1px solid black">OP. EXONERADA</td>
						<td style="border: 1px solid black">S/ <?php echo number_format($total, 2, '.', ','); ?></td></tr>
					<tr>
						<td colspan="2"></td>
						<td style="border: 1px solid black">OP. INAFECTA</td>
						<td style="border: 1px solid black">S/ 0.00</td></tr>
					<tr>
						<td colspan="2"></td>
						<td style="border: 1px solid black">OP. GRAVADA</td>
						<td style="border: 1px solid black">S/ 0.00</td></tr>
					<tr>
						<td colspan="2"></td>
						<td style="border: 1px solid black">IGV</td>
						<td style="border: 1px solid black">S/ 0.00</td></tr>
					<tr>
						<td colspan="2"></td>
						<th style="border: 1px solid black">MONTO TOTAL</th>
						<th style="border: 1px solid black">S/ <?php echo number_format($total, 2, '.', ','); ?></th>
					</tr>
				</tfoot>				
			</table>
			<br>
			<table>
				<tr scope="col">
					<td>
						<b><?php echo $comp_ley->desLeyenda; ?></b>
					</td>
				</tr>
				<tr>
					<td style="font-size: 11px">Consulte y/o descargue su comprobante electronico en www.sunat.gob.pe, utilizando su clave SOL</td>
				</tr>
				<tr>
					<td style="font-size: 11px"><p>Autorizado para ser emisor electrónico mediante la Resolución de Superintendencia N° 155-2017</p></td>
				</tr>
			</table>
		</div>
		<div class="row">
			<div class="para_imprimir">
				<br>
				<div>
					
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
	      	window.print();

	      	$('#imprimir').show();
	      	$('#div_opciones').show(); 
	      	$('.logo').show(); 
	    });
	});
</script>
