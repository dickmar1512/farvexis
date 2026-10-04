<?php
	
$product = Factura2Data::getByExtra($_GET["id"]);

  ####################### NUMERO DE REGISTRO DE COMPROBANTES ###############################
  $mysqli = new mysqli('localhost','root','','inventary',3306); //BASE DE DATOS

  $query = $mysqli->prepare("SELECT * FROM 1_2_factura_yaqha");//TABLA
  $query->execute();
  $query->store_result();
  $registros_factura = $query->num_rows;

  $id_comprobante_actual_f = $registros_factura + 1;

  function generaCerosComprobante($numero)
  {
    $largo_numero = strlen($numero); //OBTENGO EL LARGO DEL NUMERO
    $largo_maximo = 8; //ESPECIFICO EL LARGO MAXIMO DE LA CADENA
    $agregar = $largo_maximo - $largo_numero; //TOMO LA CANTIDAD DE 0 AGREGAR
    for($i =0; $i<$agregar; $i++)
    {
      $numero = "0".$numero;
    } //AGREGA LOS CEROS
    return $numero; //RETORNA EL NUMERO CON LOS CEROS
  }

  function generaCerosSerie($numero2)
  {
    $largo_numero = strlen($numero2);//OBTENGO EL LARGO DEL NUMERO
    $largo_maximo = 3;//ESPECIFICO EL LARGO MAXIMO DE LA CADENA
    $agregar = $largo_maximo - $largo_numero;   //TOMO LA CANTIDAD DE 0 AGREGAR
    for($i =0; $i<$agregar; $i++)
    {
    	$numero2 = "0".$numero2;
    } //AGREGA LOS CEROS
    return $numero2; //RETORNA EL NUMERO CON LOS CEROS
  }

  //COMPROBANTE PRIMERA SERIE B001
  $NUMERO_COMPROBANTE_F = generaCerosComprobante($id_comprobante_actual_f);
  //CAPTAMOS SERIE DE B001 AL B999
  $NUMERO_SERIE_F = (int)( ( $id_comprobante_actual_f / 99999999 ) + 1 );
  $SERIE_F = "F".generaCerosSerie($NUMERO_SERIE_F);
  //COMPROBANTE SERIE B001 AL SUPERIOR
  if ($NUMERO_SERIE_F>1) {  $NUMERO_COMPROBANTE_F = $id_comprobante_actual_f % 99999999; }
  $COMPROBANTE_F=generaCerosComprobante($NUMERO_COMPROBANTE_F);

  #####################################################################################
  $empresa = EmpresaData::getDatos();
?>
<div class="row">
	<div class="col-md-12">
		<fieldset class="content-group">
			<legend class="text-bold">Documento: </legend>
				<div class="form-group col-md-4">
				    <label><i class="icon-barcode2 position-left"></i> Serie:</label>
				    <input type="text" name="serie_comprobante" id="serie_comprobante" value="<?php echo $SERIE_F; ?>" class="form-control" readonly="readonly">
			    </div>
			    <div class="form-group col-md-4">
			        <label><i class="icon-file-text2 position-left"></i> Número:</label>
			        <input type="text" name="numero_comprobante" id="numero_comprobante" value="<?php echo $COMPROBANTE_F; ?>" class="form-control" readonly="readonly">
			    </div>
			    <!-- <div class="form-group col-md-3">
			        <label><i class="icon-calendar2 position-left"></i> Fecha.Doc:</label>
			        <input type="date" name="fecha_comprobante" id="fecha_comprobante" placeholder="" class="form-control" readonly="readonly">
			    </div> -->				
				<div class="content_debito_credito" style="display: block;">
				    <div class="form-group col-md-4">
				        <label><i class="icon-file-text position-left"></i> N° Doc. Modificado:</label>
				        <input type="text" name="num_comprobante_modificado" id="num_comprobante_modificado" class="form-control" readonly="readonly" value="<?php echo $product->SERIE."-".$product->COMPROBANTE; ?>">
				    </div>
			            <div class="form-group has-feedback has-feedback-left col-md-5">
			                <label class="col-md-12"><i class="icon-profile position-left"></i>Tipo <span class="text-danger">*</span></label>
			                <select title="Selecciona el Tipo" data-placeholder="Selecciona el Tipo" class="form-control" name="notacredito_motivo_id" id="notacredito_motivo_id" required="" tabindex="-1" aria-hidden="true">
			                    <option value="01">ANULACION DE LA OPERACION</option>
						       <!--  <option value="02">ANULACION POR ERROR EN EL RUC</option>
						        <option value="03">CORRECION POR ERROR EN LA DESCRIPCION</option>
						        <option value="04">DESCUENTO GLOBAL</option>
						        <option value="05">DESCUENTO POR ITEM</option>
						        <option value="06">DEVOLUCION TOTAL</option>
						        <option value="07">DEVOLUCION POR ITEM</option>
						        <option value="08">BONIFICACION</option>
						        <option value="09">DISMINUCION EN EL VALOR</option> -->
			                </select>
			        </div>
				</div>
				<div class="form-group col-md-7">
				    <label><i class="icon-barcode2 position-left"></i> Motivo:</label>
				    <input type="text" name="motivo" id="motivo" value="" class="form-control" required>
			    </div>
			    <div class="form-group col-md-4" id="dscto_global" style="display:none;">
				    <label><i class="icon-barcode2 position-left"></i> Descuento:</label>
				    <input type="number" name="dscto" id="dscto" value="" class="form-control" required>
			    </div>
				<div class="form-group has-feedback has-feedback-left col-md-12">
				<center>
				<button type="submit" class="btn btn-primary" id="buscar" name="buscar">Continuar</button>
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
		var tipo = $("#notacredito_motivo_id").val();
		var serie = $("#serie_comprobante").val();
		var comp = $("#numero_comprobante").val();
		var motivo = $("#motivo").val();

		if(tipo=="01" || tipo=="02" || tipo=="06"){
			// window.location.href = "./?view=onesell2&id="+3;
			$.ajax({
				    type : "POST",
				    data: {
				    		"numDoc": numDoc,
				    		"motivo": motivo,
				    		"serie": serie,
				    		"comp": comp,
				    		"tipo":tipo
				    	},
				    url: './?action=addnotacredito',

			    	success : function(data){
			    			if(data)	
			    				window.location.href = '-'+'+'comp'-'+comp;
			    	},
			});
		}

		else if(tipo=='03' || tipo=='05' || tipo=='07'){

			$.ajax({
				    type : "POST",
				    data: {
				    		"numDoc": numDoc,
				    		"tipo": tipo
				    	},
				    url: './?action=obtener_datos_factura_ajax',

			    	success : function(data){
			    			$("#datos").html('');
		            		$("#datos").html(data);   		
			    	},
			});
		}

		else if(tipo=='04'){
			var dscto=$("#dscto").val();

			$.ajax({
				    type : "POST",
				    data: {
				    		"numDoc": numDoc,
				    		"motivo": motivo,
				    		"serie": serie,
				    		"comp": comp,
				    		"tipo": tipo,
				    		"dscto": dscto
				    	},
				    url: './?action=addnotacredito',

			    	success : function(data){
			    			window.location.href = '-'+'+'comp'-'+comp;		
			    	},
			});
		}

	});

	$( "#notacredito_motivo_id" ).change(function() {
	  	if($( "#notacredito_motivo_id" ).val()=='04'){
	  		$( "#dscto_global" ).attr('style','display:block');
	  	}

	  	else{
	  		$( "#dscto_global" ).attr('style','display:none');
	  	}
	});
</script>