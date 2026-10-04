<?php $orden_id = $_GET["id"];
$orden = SellData::getById($orden_id);
$detalle = OperationData::getAllProductsBySellId($orden->id);
$cliente = PersonData::getById($orden->person_id);
$empresa = EmpresaData::getDatos();

$cajero = null;
$cajero = UserData::getById($orden->user_id)->username;

if (strlen($cliente->numero_documento) == 8):
	$docLabel = "DNI";
	$nomLabel = "SEÑOR(ES)";
else:
	$docLabel = "RUC";
	$nomLabel = "RAZON SOCIAL";
endif;
?>
<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0"><i class='fa fa-print'></i> Imprimir Orden</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Orden Venta</a></li>
				</ol>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<div class="card card-default">
			<div class="card-header">
				<div class="row">
					<div class="col-md-2">
						<h2 class="card-title">
							<i class="fa fa-file-text"></i> Generar Comprobante
						</h2>
					</div>
					<div class="col-md-9">
						<div class="row">
							<!-- <div class="col-md-3">
								<form method="post" action="./?action=addfacturao" class="form-inline"
									onsubmit="return enviado2()">
									<input type="hidden" value="<?php echo $_GET["id"] ?>" name="orden_id">
									<div class="input-group col-md-6">
										<select name="selTipoComprobante" class="form-control" id="selTipoComprobante">
											<option value="1">Factura</option>
											<option value="3">Boleta</option>
										</select>
									</div>
									<button class="btn btn-danger col-md-4" type="submit" id="btnComprobante">
										Generar
									</button>
								</form>
							</div> -->
							<div class="col-md-2">
								<div class="col-sm-16 float-sm-right">
									<button id="imprimir80mm" class="btn btn-md btn-info"><i class="fa fa-print"></i>
										IMPRIMIR
										80mm</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card-body">
				<div class="row" style="margin-top: 0px; padding-top: 0px; background: #fff">
					<div class="col-md-12" style="display: flex; justify-content: center;">
						<div class="row" style="display: flex; justify-content: center;">
							<div class="col-sm-10">
								<!-- card -->
								<div class="card col-sm-10">
									<div class="row" style="display: flex; justify-content: center;">
										<table class="table table-bordered">
											<tr>
												<td style="text-align: center; width: 100px">
													<img src="assets/img/logo2.jpg" style="height: 80px; width: 100%" />
												</td>
												<td
													style="text-align: center; font-family: courier new; font-size: 0.9em; width: 250px">
													<center>
														<b>
															<?php echo $empresa->Emp_RazonSocial ?><br>
															<?php echo $empresa->Emp_Direccion ?><br>
															Telf.: <?php echo $empresa->Emp_Telefono ?><br>
															Correo.: <?php echo $empresa->Emp_Celular ?>
														</b>
													</center>
												</td>
												<td
													style="text-align: center; font-family: courier new; font-size: 0.9em; width: 150px">
													<b>RUC:<?php echo $empresa->Emp_Ruc ?></b>
													<div style="background:#000; color: #FFF">
														ORDEN DE VENTA
													</div>
													<b><?php echo $orden->serie . "-" . $orden->comprobante; ?></b>
												</td>
											</tr>
										</table>
									</div>
									<div class="row">
										<table class="table table-bordered"
											style="font-family: courier new; font-size: 0.9em">
											<tr>
												<td class="col-sm-2">
													<b><?= $docLabel ?></b>
												</td>
												<td>
													<b><?php echo ": " . $cliente->numero_documento; ?></b>
												</td>
											</tr>
											<tr>
												<td class="col-sm-2">
													<b><?= $nomLabel ?></b>
												</td>
												<td>
													<b><?php echo ": " . $cliente->lastname . ' ' . $cliente->name; ?></b>
												</td>
											</tr>
											<tr>
												<td class="col-sm-2">
													<b>DIRECCIÓN</b>
												</td>
												<td>
													<b><?php echo ": " . $cliente->address1; ?></b>
												</td>
											</tr>
											<tr>
												<td class="col-sm-2">
													<b> FECHA EMISIÓN</b>
												</td>
												<td>
													<b><?php echo ": " . $orden->created_at; ?></b>
												</td>
											</tr>
										</table>
										<table class="table table-bordered"
											style="font-family: courier new; font-size: 0.9em">
											<thead style="background-color: #000; color:#fff">
												<tr>
													<th scope="col">CANTIDAD</th>
													<th scope="col">DESCRIPCION</th>
													<th scope="col">P. UNIT.</th>
													<th scope="col">TOTAL</th>
												</tr>
											</thead>
											<tbody>
												<?php
												$total = 0;
												foreach ($detalle as $ope) {
													$product = ProductData::getById($ope->product_id);
													$subtotal = $ope->q * $ope->prec_alt;
													?>
													<tr>
														<td align="center"><b><?php echo $ope->q; ?></b></td>
														<td>
															&nbsp;&nbsp;<b><?php echo $product->name; ?></b>
															<?php
															if ($ope->descripcion != "") {
																echo "|" . $ope->descripcion;
															}
															?>
														</td>
														<td><b><?php echo $ope->prec_alt; ?></b></td>
														<td><b><?php echo number_format($subtotal, 2, '.', ','); ?></b></td>
													</tr>
													<?php
													$total = $subtotal + $total;
													$totalConDesc = $total - $orden->discount;
													$numLetra = NumeroLetras::convertir(number_format($totalConDesc, 2, '.', ','));
												}


												$datosComprobante = array(
												 	"venta" => $orden,
													"detalles" => $detalle,
													//"comp_cab" => $comp_cab,
													//"comp_aca" => $comp_aca,
													//"comp_tri" => $comp_tri,
													//"comp_ley" => $comp_ley,
													"empresa" => $empresa,
													"cajero" => $cajero,
													"numLetra" => $numLetra
												);
												?>
											</tbody>
										</table>
										<table class="table table-bordered"
											style="font-family: courier new; font-size: 0.9em ">
											<thead>
												<tr>
													<th style="width: 50%">
														<?php
														echo "<input type='hidden' id='datosComprobante' name='datosComprobante' value='" . json_encode($datosComprobante) . "'>";
														?>
														<table class="table table-bordered">
															<tr>
																<td>
																	<?php echo "Usuario:" . $cajero; ?>
																</td>
															</tr>
														</table>
													</th>
													<th>
														<table class="table table-bordered">
															<tr>
																<td>OPERACIÓN GRATUITA</td>
																<td>0.00</td>
															</tr>
															<tr>
																<td>OPERACIÓN EXONERADA</td>
																<td><?php echo number_format($total, 2, '.', ','); ?>
																</td>
															</tr>
															<tr>
																<td>OPERACIÓN INAFECTA</td>
																<td>0.00</td>
															</tr>
															<tr>
																<td>OPERACIÓN GRAVADA</td>
																<td>0.00</td>
															</tr>
															<tr>
																<td>IGV</td>
																<td>0.00</td>
															</tr>
															<tr>
																<td>IMPORTE TOTAL</td>
																<td><?php echo number_format($total, 2, '.', ','); ?>
																</td>
															</tr>
															<tr>
																<td colspan="2">
																	<b>SON: <?php echo $numLetra; ?></b>
																</td>
															</tr>
														</table>
													</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
								<!-- end card  -->
							</div>
						</div>
					</div>
				</div><!--  fin col-md-6 -->
			</div>
		</div>
	</div>
</section>
<?php
function generaCerosComprobante($numero)
{
	$largo_numero = strlen($numero);
	$largo_maximo = 6;
	$agregar = $largo_maximo - $largo_numero;
	for ($i = 0; $i < $agregar; $i++) {
		$numero = "0" . $numero;
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
	// function enviado2() {
	// 	go = confirm("¿Seguro que desea emitir el comprobante, la accion no podrá ser revertida ?");

	// 	if (go == true) {
	// 		if (cuenta == 0) {
	// 			cuenta++;
	// 			return true;
	// 		}
	// 		else {
	// 			alert("El formulario ya está siendo enviado, por favor aguarde un instante.");
	// 			return false;
	// 		}
	// 	}
	// 	else {
	// 		return false;
	// 	}
	// }

	// $(document).ready(function () {
	// 	$("#product_code").keydown(function (e) {
	// 		if (e.which == 17 || e.which == 74) {
	// 			e.preventDefault();
	// 		} else {
	// 			console.log(e.which);
	// 		}
	// 	})

	// 	$('#imprimir').click(function () {
	// 		$('#imprimir').hide();
	// 		$('#btnComprobante').hide();
	// 		$('#selTipoComprobante').hide();
	// 		$('#div_opciones').hide();
	// 		$('.logo').hide();
	// 		$('.eliminar_repuesto').hide();
	// 		window.print();

	// 		$('#imprimir').show();
	// 		$('#div_opciones').show();
	// 		$('#btnComprobante').show();
	// 		$('#selTipoComprobante').show();
	// 		$('.eliminar_repuesto').show();
	// 		$('.logo').show();
	// 	});
	// });
</script>