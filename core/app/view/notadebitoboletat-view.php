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

	$product = BoletaData::getByNumDoc($_GET["num"]);
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

	// print_r($sell ); exit();
    //$mesero = UserData::getById($sell->mesero_id);
    //$cajero= null;
    //$cajero = UserData::getById($sell->user_id);
    $empresa = EmpresaData::getDatos();
?>
<div class="row" style="background: #FFF; margin-top: 0px; padding-top: 0px">
	<div class="col-md-8 col-md-offset-2">
		<div class="row pull-right">
			<br>
			<button id="imprimir" class="btn btn-md btn-info"><i class="fa fa-print"></i> IMPRIMIR</button>
		</div>
		<div class="row" id="ticketera">
			<div class="col-md-8 col-md-offset-2" style="border-right: 1px solid #ccc; border-left: 1px solid #cccc; border: 1px solid #cccc;">
				<table class="table">
					<tr style="text-align: center;">
						<td rowspan="2" class="logo">
							<img src="assets/img/logo.jpg" style="height: 120px; width: 130px"><br>
						</td>
						<td colspan="3">
							<?php echo $empresa->Emp_Descripcion ?> <br>
							<?php echo $empresa->Emp_RazonSocial ?><br>
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
							<span class="label-comprobante" style="font-size: 16px; font-weight: bold;">NOTA DE DÉBITO ELECTRÓNICA</span>
							<br>
							<!-- <img src="assets/img/bol.png" style="width: 300px;"><br><br> -->
							<span style="font-size: 16px"><?php echo $product->SERIE."-".$product->COMPROBANTE; ?></span>
							<div style=" color: red"><h5><b><?php if($comp_cab->codTipoNota==1){ echo("Intereses por mora");} ?></b></h5></div>
						</td>
					</tr>
					<tr>
						<td colspan="3">	
							<b>DOCUMENTO QUE MODIFICA</b><br>
							<b>Boleta Electrónica: <?php echo $comp_cab->serieDocModifica; ?></b>
							<br>
							DNI: <?php echo ": ".$comp_cab->numDocUsuario; ?>
							<br>
							NOMBRE: <?php echo ": ".$comp_cab->rznSocialUsuario; ?>
							<br>
							<?php $person = PersonData::getById($sell->person_id); ?>
							GRADO Y SECC <?php echo ": ".$person->company; ?>
							<br>
							MOTIVO <?php echo ": ".$comp_cab->descMotivo; ?>							
						</td>
					</tr>
				</table>				
				<div id="detalle_venta" <?php if(isset($_GET['con'])){ ?> style="display: none" <?php } ?>>
					<table class="table" style="max-width: 900px">
						<thead class="thead-dark">
							<th>CANTIDAD</th>							
							<th>DESCRIPCION</th>
							<th>P. UNIT.</th>
							<th>P. TOTAL</th>
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
				</div>
				<div style="text-align: right;">
					<table style="width: 40%">
						<tr></tr>
					</table>
					<table style="width: 60%; float: right;">
						<tbody style="text-align: left;">
							<tr>
								<td>OP. GRATUITA</td>
								<td>S/ 0.00</td>
							</tr>
							<tr>
								<td>OP. EXONERADA</td>
								<td>S/ <?php echo number_format($total, 2, '.', ','); ?></td>
							</tr>
							<tr>
								<td>OP. INAFECTA</td>
								<td>S/ 0.00</td>
							</tr>
							<tr>
								<td>OP. GRAVADA</td>
								<td>S/ 0.00</td>
							</tr>
							<tr>
								<td>IGV</td>
								<td>S/ 0.00</td>
							</tr>
							<tr>
								<td>MONTO TOTAL</td>
								<td>S/ <?php echo number_format($total, 2, '.', ','); ?></td>
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

