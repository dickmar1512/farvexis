<script type="text/javascript">
	function imprimir()
    {
    	$('#imprimir').hide();
      	$('#div_opciones').hide();
      	// $('.logo').hide();
      	window.print();

      	$('#imprimir').show();
      	$('#div_opciones').show(); 
      	// $('.logo').show(); 
    }
</script>
<?php
	if (isset($_GET['im'])) 
	{
		?>
			<script type="text/javascript">
				imprimir();
			</script>
		<?php
	}

// 	$venta = Factura2Data::getByExtra($_GET["id"]);

// $comp_cab = CabData::getById($venta->id, $_GET["tipodoc"]);
// $comp_aca = AcaData::getById($venta->id, $_GET["tipodoc"]);
// $detalles = DetData::getById($venta->id, $_GET["tipodoc"]);
// $comp_tri = TriData::getById($venta->id, $_GET["tipodoc"]);
// $comp_ley = LeyData::getById($venta->id, $_GET["tipodoc"]);
// $sell = SellData::getById($venta->EXTRA1);


	$product = Factura2Data::getByExtra($_GET["id"]);
	$comp_cab = CabData::getById($product->id, 1);
	$comp_aca = AcaData::getById($product->id, 1);
	$detalles = DetData::getById($product->id, 1);
	$comp_tri = TriData::getById($product->id, 1);
	$comp_ley = LeyData::getById($product->id, 1);

	$sell = SellData::getById($product->EXTRA1);

    $operations = OperationData::getAllProductsBySellId($_GET["id"]);

    $empresa = EmpresaData::getDatos();
?>
<div class="row" style="background: #FFF; margin-top: 0px; padding-top: 0px">
	<div class="col-md-8 col-md-offset-2">
		<div class="row pull-right">
			<br>
			<button id="imprimir" class="btn btn-md btn-info"><i class="fa fa-print"></i> IMPRIMIR</button>
		</div>
		<div class="row" id="ticketera">
			<div class="col-md-8 col-md-offset-1" style="border-right: 1px solid #ccc; border-left: 1px solid #cccc; border: 1px solid #cccc;">
				<table class="table">
					<tr style="text-align: center;">
						<td rowspan="2" class="logo">
							<img src="assets/img/logoF.jpg" style="height: 65px; width: 90px"><br>
						</td>
						<td colspan="3">
							<?php echo $empresa->Emp_RazonSocial ?><br>
							<?php echo $empresa->Emp_Descripcion ?> <br>
						</td>
					</tr>
					<tr>
						<td class="text-center">
							<?php echo $empresa->Emp_Direccion ?><br>
							Tel. <?php echo $empresa->Emp_Telefono ?><br>
							<strong>RUC <?php echo $empresa->Emp_Ruc ?></strong><br>
							
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align: center;">
							<span class="label-comprobante" style="font-size: 16px; font-weight: bold;">FACTURA ELECTRÓNICA</span>
							<br>
							<!-- <img src="assets/img/bol.png" style="width: 300px;"><br><br> -->
							<span style="font-size: 16px"><?php echo $product->SERIE."-".$product->COMPROBANTE; ?></span>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							RUC: <?php echo ": ".$comp_cab->numDocUsuario; ?>
							<br>
							RAZÓN SOCIAL: <?php echo ": ".$comp_cab->rznSocialUsuario; ?>
							<br>
							<?php $person = PersonData::getById($sell->person_id); ?>
							DIRECCIÓN <?php echo ": ".$comp_aca->desDireccionCliente; ?>
							<br>
							FEC. EMIS. <?php echo ": ".$comp_cab->fecEmision." | ".$comp_cab->horEmision; ?>
							
						</td>
					</tr>
				</table>
				<div id="detalle_venta" <?php if(isset($_GET['con'])){ ?> style="display: none" <?php } ?>>
					<table class="table" style="max-width: 900px">
						<thead class="thead-dark">
							<th>CANT.</th>							
							<th>DESCRIP.</th>
							<th>P. UNIT.</th>
							<th>P. TOTAL</th>
						</thead>
						<tbody>
							<?php
								$total = 0;
								foreach ($operations as $ope) 
								{
									$product = ProductData::getById($ope->product_id);
									$subtotal = $ope->q*$ope->prec_alt;

												$unidad = UnidadMedidaData::getById($product->unit);

									?>
										<tr>
											<td  style="padding: 0px; text-align: center" align="center"><?php echo $ope->q." ".$unidad->sigla; ?></td>
											
											<td  style="padding: 0px; text-align: left; font-size: 11px">
												&nbsp;&nbsp;<?php echo $product->name; ?>
												<?php
													if($ope->descripcion != "")
													{
														echo " | ".$ope->descripcion;
													}
												?>
											</td>
											<td  style="padding: 0px; text-align: center"><?php echo $ope->prec_alt; ?></td>
											<td  style="padding: 0px; text-align: center"><?php echo number_format($subtotal, 2, '.', ','); ?></td>
										</tr>
									<?php
									$total = $subtotal + $total;
								}
							?>
						</tbody>
					</table>
				</div>
				<div style="text-align: right;">
					<table style="width: 40%">
						<tr></tr>
					</table>
					<table style="width: 60%; float: right;">
						<tbody style="text-align: left;">
							<tr>
								<td>Op. gratuita</td>
								<td>S/ 0.00</td>
							</tr>
							<tr>
								<td>Op. exonerada</td>
								<td>S/ <?php echo number_format($total, 2, '.', ','); ?></td>
							</tr>
							<tr>
								<td>Op. inafecta</td>
								<td>S/ 0.00</td>
							</tr>
							<tr>
								<td>OP. gravada</td>
								<td>S/ 0.00</td>
							</tr>
							<tr>
								<td>IGV</td>
								<td>S/ 0.00</td>
							</tr>
							<tr>
								<td><b>MONTO TOTAL</b></td>
								<td><b>S/ <?php echo number_format($total, 2, '.', ','); ?></b></td>
							</tr>
							<!-- <tr>
								<td>EFECTIVO</td>
								<td>S/ <?php echo number_format($sell2->cash,2,'.',','); ?></td>
							</tr>
							<tr>
								<td>VUELTO</td>
								<td>S/ <?php echo number_format($sell2->cash - $total,2,'.',','); ?></td>
							</tr> -->							
						</tbody>
					</table>
				</div>
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
	    	imprimir();
	    });	    
	});  
</script>
