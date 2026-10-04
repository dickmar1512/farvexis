<?php if (isset($dato)&& count($dato)!=0) {?>
<input type="hidden" id="cont" value="<?php echo count($dato) ?>">
<fieldset class="content-group">
		<legend class="text-bold">Detalle: </legend>
		<table class="table"  id="alt">
				<thead class="thead-dark">
					<th scope="col" style="border: 1px solid black; width: 150px">CANTIDAD</th>
					<th scope="col" style="border: 1px solid black; width: 500px">DESCRIPCION</th>
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
									<td ><p><input id="proviejo<?php echo $i; ?>" type="hidden" value="<?php echo $det['desItem']; ?>"><?php echo $det['desItem']; ?></p>
										<p id="p<?php echo $i; ?>"><input id="pronuevo<?php echo $i; ?>" type="hidden" value=""></p>
										<p>Cód. Nuevo: <input type="text" id="cod<?php echo $i; ?>">		<a href='javascript:void(0);' class='search-product btn btn-primary' data-cod="cod<?php echo $i; ?>" data-p="p<?php echo $i; ?>"
										    data-input="pronuevo<?php echo $i; ?>"  title='Remove field'><i class='glyphicon glyphicon-search'></i></a></p>
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
$("#alt").on('click', '.search-product', function(e){ //Once remove button is clicked
    e.preventDefault();
    var cod = $("#"+$(this).attr("data-cod")).val();
    var p_mod= "#"+$(this).attr("data-p");
    var cod_input_nuevo= $(this).attr("data-input");
    // alert($("#"+$(this).attr("data-cod")).val());

    $.ajax({
				    type : "POST",
				    data: {
				    		"cod": cod,
				    		"cod_input_nuevo":cod_input_nuevo
				    	},
				    url: './?action=obtener_nombre_producto_ajax',

			    	success : function(data){
			    			$(p_mod).html('');
		            		$(p_mod).html(data);   		
			    	},
			});
});

$("#fin").click(function () {
		var numDoc = $("#num_comprobante_modificado").val();
		var tipo = $("#notacredito_motivo_id").val();
		var serie = $("#serie_comprobante").val();
		var comp = $("#numero_comprobante").val();
		var motivo = $("#motivo").val();

		var cont=$("#cont").val();

		var arraydet=[];

		for (var i = 0; i < cont; i++) {
			if($("#pronuevo"+i).val()!=''){

				arraydet.push([$("#proviejo"+i).val(),$("#pronuevo"+i).val()]);
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