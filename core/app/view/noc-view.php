<div class="row">
	<div class="col-md-12">
		<fieldset class="content-group">
			<legend class="text-bold">Documento: </legend>
				<!-- <div class="form-group col-md-2">
				    <label><i class="icon-barcode2 position-left"></i> Serie:</label>
				    <input type="text" name="serie_comprobante" id="serie_comprobante" value="F001" class="form-control" readonly="readonly">
			    </div>
			    <div class="form-group col-md-2">
			        <label><i class="icon-file-text2 position-left"></i> Número (6 números):</label>
			        <input type="text" name="numero_comprobante" id="numero_comprobante" value="659854" class="form-control" readonly="readonly">
			    </div> -->
			    <!-- <div class="form-group col-md-3">
			        <label><i class="icon-calendar2 position-left"></i> Fecha.Doc:</label>
			        <input type="date" name="fecha_comprobante" id="fecha_comprobante" placeholder="" class="form-control" readonly="readonly">
			    </div> -->				
				<div class="content_debito_credito" style="display: block;">
			        <div class="form-group col-md-3">
			            <div class="form-group has-feedback has-feedback-left">
			                <label class="col-md-12"><i class="icon-file-text position-left"></i> Documento Modificado <span class="text-danger">*</span></label>
			                <select title="Documento Modificado" data-placeholder="Selecciona el Documento" class="form-control" id="tipo_comprobante_modificado" required="" tabindex="-1" aria-hidden="true">
			                    <option value="01">Factura</option>
			                    <option value="03">Boleta</option>
			                </select>
			            </div>                        
			        </div>
				    <div class="form-group col-md-3">
				        <label><i class="icon-file-text position-left"></i> N° Doc. Modificado:</label>
				        <input type="text" name="num_comprobante_modificado" id="num_comprobante_modificado" placeholder="856887" class="form-control">
				    </div>
			            <div class="form-group has-feedback has-feedback-left col-md-4">
			                <label class="col-md-12"><i class="icon-profile position-left"></i>Motivo <span class="text-danger">*</span></label>
			                <select title="Selecciona el Motivo" data-placeholder="Selecciona el Motivo" class="form-control" name="notacredito_motivo_id" id="notacredito_motivo_id" required="" tabindex="-1" aria-hidden="true">
			                    <option value="01">ANULACION DE LA OPERACION</option>
						        <option value="02">ANULACION POR ERROR EN EL RUC</option>
						        <option value="03">CORRECION POR ERROR EN LA DESCRIPCION</option>
						        <option value="04">DESCUENTO GLOBAL</option>
						        <option value="05">DESCUENTO POR ITEM</option>
						        <option value="06">DEVOLUCION TOTAL</option>
						        <option value="07">DEVOLUCION POR ITEM</option>
						        <option value="08">BONIFICACION</option>
						        <option value="09">DISMINUCION EN EL VALOR</option>
			                </select>
			        </div>
				</div>
				<div class="form-group has-feedback has-feedback-left col-md-2">
				<center>
				<button type="submit" class="btn btn-primary" id="buscar" name="buscar" style="margin-top:25px;"><i class="glyphicon glyphicon-search" ></i> Buscar</button>
				</center>
				</div>
		</fieldset>

		<div id="datos" name="datos">
			
		</div>
	</div>
</div>

<script type="text/javascript">

	$("#buscar").click(function () {

		var numDoc = $("#num_comprobante_modificado").val();

		$.ajax({
			    type : "POST",
			    data: {
			    		"numDoc": numDoc
			    	},
			    url: './?action=obtener_datos_factura_ajax',

		    	success : function(data){
		    			$("#datos").html('');
	            		$("#datos").html(data);   		
		    	},
		  	});
	});
</script>