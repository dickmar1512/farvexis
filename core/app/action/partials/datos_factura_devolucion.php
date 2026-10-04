<?php if (isset($dato)&& count($dato)!=0) {?>
<input type="hidden" id="cont" value="<?php echo count($dato) ?>">
<fieldset class="content-group">
		<legend class="text-bold">Detalle: </legend>
		<table class="table"  id="alt">
				<thead class="thead-dark">
					<th scope="col" style="border: 1px solid black; width: 150px">CANTIDAD</th>
					<th scope="col" style="border: 1px solid black; width: 400px">DESCRIPCION</th>
					<th scope="col" style="border: 1px solid black; width: 400px">CANTIDAD A DEVOLVER</th>
					<th scope="col" style="border: 1px solid black; width: 150px">PRECIO UNITARIO</th>
					<th scope="col" style="border: 1px solid black; width: 150px">IMPORTE</th>
				</thead>
				<tbody style="min-height: 500px">
					<?php
						$total = 0;
						$i=0;
						foreach ($dato as $det) {
							
							?>
								<tr>
									<td><input id="id<?php echo $i; ?>" type="hidden" value="<?php echo $det['id']; ?>"><?php echo $det['ctdUnidadItem']; ?></td>
									<td ><input id="proviejo<?php echo $i; ?>" type="hidden" value="<?php echo $det['desItem']; ?>"><?php echo $det['desItem']; ?>
									</td>
									<td ><p id="p<?php echo $i; ?>"><input id="pronuevo<?php echo $i; ?>" type="hidden" value=""></p>
										<p>Cant: <input type="number" id="cantidad<?php echo $i; ?>"></p>
									</td>
									<td><?php echo $det['mtoValorUnitario']; ?>
									</td>
									<td><?php echo $det['ctdUnidadItem']*$det['mtoValorUnitario']; ?></td>
								</tr>
							<?php
							$i++;
						}
					?>
				</tbody>			
			</table>
</fieldset>
<div class="form-group has-feedback has-feedback-left col-md-12">
				<center>
				<button type="submit" class="btn btn-primary" id="fin" name="fin">Finalizar</button>
				</center>
				</div>
<?php }else {?>

No se encontraron registros
<?php }?>

<script type="text/javascript">

$("#fin").click(function () {
		var numDoc = $("#num_comprobante_modificado").val();
		var tipo = $("#notacredito_motivo_id").val();
		var serie = $("#serie_comprobante").val();
		var comp = $("#numero_comprobante").val();
		var motivo = $("#motivo").val();

		var cont=$("#cont").val();

		var arraydet=[];

		for (var i = 0; i < cont; i++) {
			if($("#cantidad"+i).val()!=''){
				arraydet.push([$("#proviejo"+i).val(),$("#cantidad"+i).val()]);
			}
		}

			$.ajax({
				    type : "POST",
				    data: {
				    		"numDoc": numDoc,
				    		"motivo": motivo,
				    		"serie": serie,
				    		"comp": comp,
				    		"tipo":tipo,
				    		"arraydet": arraydet
				    	},
				    url: './?action=addnotacredito',

			    	success : function(data){
						window.location.href = "./?view=notacredito&num="+serie+'-'+comp;		
			    	},
			});
	});
</script>