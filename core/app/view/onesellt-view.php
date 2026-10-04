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

	$product = BoletaData::getByExtra($_GET["id"]);
	$comp_cab = CabData::getById($product->id, 3);
	$comp_aca = AcaData::getById($product->id, 3);
	$detalles = DetData::getById($product->id, 3);
	$comp_tri = TriData::getById($product->id, 3);
	$comp_ley = LeyData::getById($product->id, 3);

	$sell = SellData::getById($product->EXTRA1);
    //$mesero = UserData::getById($sell->mesero_id);

    $operations = OperationData::getAllProductsBySellId($_GET["id"]);

    $empresa = EmpresaData::getDatos();
?>
<center>
<div class="row" style="margin-top: 0px; padding-top: 0px; background: #fff;">
	<div class="col-md-10 col-md-offset-1">
		<div class="row ">
			<div class="row pull-right">
				<button id="imprimir" class="btn btn-md btn-info"><i class="fa fa-print"></i> IMPRIMIR</button>
			</div>
		</div>
		<div>
			<div class="para_imprimir">
				<div>
					<table>
						<tr>
							<td> 
							<center>
								<img src="assets/img/logo_fe.jpg" style="height: 60px; width: 250px"/>
							</center>
							</td>
						</tr>
						<tr>
						 <td style="font-family: courier new; font-size: 0.7em">
						 	<center>
							<b><?php echo $empresa->Emp_RazonSocial ?></b>
                        </center>
                        </td>
						</tr>
						<tr>
						  <td style="font-family: courier new; font-size: 0.7em"><center>
							<b><?php echo $empresa->Emp_Direccion ?></b>
                        	</center>
                        </td>
						</tr>	
						<tr>
							<td style="font-family: courier new; font-size: 0.7em">
								<center>
							<b>Telf:<?php echo $empresa->Emp_Telefono ?></b>
							</center>
							</td>
						</tr>
						<tr>
							<td style="font-family: courier new; font-size: 0.7em">
								<center>
							<b>Iquitos - Loreto - Perú</b>
						    </center>
							</td>
						</tr>
						<tr>
						  <th>
						  <center>
						  <strong>---------------------------------------------------</strong>
						  </center>
						  </th>
						</tr>
						<tr>	
							<td style="text-align: center;font-family: courier new; font-size: 0.9em">
						      	<b>RUC: <?php echo $empresa->Emp_Ruc ?></b>
						      	<div style=" color: #FFF">
						      		<img src="assets/img/bol.png" style="width: 60%; height: 50%">
						      	</div>
						      	<div><b><?php echo $product->SERIE." - ".$product->COMPROBANTE; ?></b></div>
					  		</td>
						</tr>						
						<tr>
						  <th>
						  <center>
						  <strong>---------------------------------------------------</strong>
						  </center>
						  </th>
						</tr>
					</table>
					<table>
						<tr>
							<td style="font-family: courier new; font-size: 0.7em">
									<b>DNI</b>
					    	</td>
							<td style="font-family: courier new; font-size: 0.7em">
								<b><?php echo ": ".$comp_cab->numDocUsuario; ?></b>
							</td>
						</tr>
						<tr>	
					        <td style="font-family: courier new; font-size: 0.7em">
					        		<b>NOMBRE</b>
					    	</td>
					        <td  style="font-family: courier new; font-size: 0.7em">
					        	<b><?php echo ":".$comp_cab->rznSocialUsuario; ?></b>
					        </td>
					    </tr> 
					    <tr>
					        <td style="font-family: courier new; font-size: 0.7em">
							<b>FEC. EMIS.</b>
					        </td>
					        <td style="font-family: courier new; font-size: 0.7em">
					        	<b><?php
								$fecha=date("d/m/Y");									
								 echo ": ".$comp_cab->fecEmision." | ".$comp_cab->horEmision; ?></b>
					        </td>			        
						</tr>
						<tr>
						  <th colspan="2"><strong>---------------------------------------------------</strong></th>
						</tr>
					</table>
					<table class="table-bordered" style="margin-top: 0px; padding-top: 0px">
						<thead class="thead-dark">
							<th style="font-family: courier new; font-size: 0.7em">CANT.</th>
							<td style="font-family: courier new; font-size: 0.7em">&nbsp;&nbsp;<b>DESCRIPCION</b></td>
							<th style="font-family: courier new; font-size: 0.7em">P. UNIT.</th>
							<th style="font-family: courier new; font-size: 0.7em">IMP. S/</th>
						</thead>
						<tbody>
							<?php
								$total = 0;
								foreach ($detalles as $det) {								
									?>
										<tr>
											<td class="text-center" style="font-family: courier new; font-size: 0.7em"><b><?php echo $det->ctdUnidadItem;?></b></td>
											<td style="font-family: courier new; font-size: 0.7em"><b><?php echo $det->desItem; ?></b></td>
											<td style="font-family: courier new; font-size: 0.7em"><b><?php echo $det->mtoValorUnitario; ?></b></td>
											<td style="font-family: courier new; font-size: 0.7em"><b><?php echo $det->mtoValorVentaItem; ?></b></td>
										</tr>
									<?php
									$total = $det->mtoValorVentaItem + $total;
									$totalConDesc= $total-$comp_cab->sumDescTotal;
									//$numLetra = NumLetras::convertirNumeroLetra($totalConDesc);
									$numLetra = NumeroLetras::convertir(number_format($total,2,'.',','));
								}
							?>
						</tbody>
					</table>
					<table class="table-bordered" style="margin-top: 0px; padding-top:  0px ">
						<thead style="border-style: none">
						<tr>
						  <th colspan="2">	
						  	<table>
						  	  <tr>	
								  <th colspan="2"><strong>---------------------------------------------------</strong></th>
								</tr>
								<tr>
								<td  colspan="2" style="font-family: courier new; font-size: 0.7em;" align="right">
								<b>SUB TOTAL S/<?=number_format($total, 2, '.', ',')?></b>
								</td>
								</tr>
								<tr>
								  <th colspan="2">
								  	<strong>---------------------------------------------------</strong></th>
                               </tr>
						    </table>
						</th>
						</tr>
						<tr cellspacing="0" cellpading="0">	
							<th style="width: 30%">&nbsp;&nbsp;</th>
							<th align="right" style="">
								<table style="width:100%; height:100%">
									<tr style="font-family: courier new; font-size: 0.7em">
										<td>TOTAL OP. GRATUITAS</td>
										<td>S/ 0.00</td>
									</tr>
									<tr style="font-family: courier new; font-size: 0.7em">
										<td>TOTAL OP. EXONERADA</td>
										<td>S/<?=number_format($total, 2, '.', ',')?></td>
									</tr>
									<tr style="font-family: courier new; font-size: 0.7em">
										<td>TOTAL OP. INEFECTA</td>
										<td>S/ 0.00</td>
									</tr>
									<tr style="font-family: courier new; font-size: 0.7em">
										<td>TOTAL OP. GRABADA</td>
										<td>S/ 0.00</td>
									</tr>
									<tr style="font-family: courier new; font-size: 0.7em">
										<td>TOTAL IGV</td>
										<td>S/ 0.00</td>
									</tr>
									<tr style="font-family: courier new; font-size: 0.7em">
										<td>TOTAL ICBPER</td>
										<td>S/ 0.00</td>
									</tr>
									<tr style="font-family: courier new; font-size: 0.7em">
										<td>IMPORTE TOTAL</td>
										<td>S/ <?=number_format($total, 2, '.', ',')?></td>
									</tr>				
								</table>
							</th>
                        </tr>
                        <tr>
						<td  colspan="2" style="font-family: courier new; font-size: 0.7em; align-content:left;">
						<b><?php echo $numLetra;
					 ?></b>
						</td>
						</tr>
                        <tr cellspacing="0" cellpading="0">
							<th style="align-content: center; text-align: center; font-family: courier new; font-size: 0.7em" colspan="2">
								<table style=" margin-bottom: 0px; padding-bottom:  0px ">
									<tr scope="col">									
									<th>
										<div id="qrcodigo"></div>
									</th>
									<td style="align-content: center; text-align: center; font-family: courier new; font-size: 0.7em">
									<table style=" margin-bottom: 0px; padding-bottom:  0px ">
									<tr>
									<td style="align-content: center;">
									<b><?php echo $comp_ley->desLeyenda; ?></b>
									</td>
									</tr>
									<tr>
									<td colspan="2" style="align-content: center;">
									Consulte y/o descargue su comprobante electronico en www.sunat.gob.pe, utilizando su clave SOL
									</td>
									</tr>
									<tr>
									<td colspan="2" style="align-content: center;">
									<p>Autorizado para ser emisor electrónico mediante la Resolución de Superintendencia N° 155-2017</p></td></tr>
								
									</table>
									</td>
									</tr>
									</table>
							</th>
						</tr>
						</thead>
					</table>
				</div>
			</div>
		</div>
	</div>
</div><!--  fin col-md-6 -->
</center>

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
<script>
	$("#qrcodigo").qrcode({
		render:'canvas',
		size:80,
		color:'#3A3',
		ecLevel: 'L',
		text:'<?php echo $empresa->Emp_Ruc."|03|".$product->SERIE."-".$product->COMPROBANTE."|0.00|".$total."|".$comp_cab->fecEmision."|"?>'
	});
</script>
