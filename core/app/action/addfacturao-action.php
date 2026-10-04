<?php
	$SERIE = "";
	$SERIE_F = "";
	$SERIE_B = "";
	$COMPROBANTE_B = "";
	$COMPROBANTE_F = "";
	$mysqli = Database::getCon(); //BASE DE DATOS

	if(count($_POST) > 0)
	{
		####################### NUMERO DE REGISTRO DE COMPROBANTES ###############################
		$query = $mysqli->prepare("SELECT * FROM boleta");//TABLA
		$query->execute();
		$query->store_result();
		$registros = $query->num_rows;

		$query = $mysqli->prepare("SELECT * FROM factura");//TABLA
		$query->execute();
		$query->store_result();
		$registros_factura = $query->num_rows;

		$id_comprobante_actual = $registros + 1;
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
		$NUMERO_COMPROBANTE=generaCerosComprobante($id_comprobante_actual);
		//CAPTAMOS SERIE DE B001 AL B999
		$NUMERO_SERIE = (int)( ( $id_comprobante_actual / 99999999 ) + 1 );
		$SERIE_B = "B".generaCerosSerie($NUMERO_SERIE);
		//COMPROBANTE SERIE B001 AL SUPERIOR
		if ($NUMERO_SERIE>1) {  $NUMERO_COMPROBANTE = $id_comprobante_actual % 99999999; }
		$COMPROBANTE_B=generaCerosComprobante($NUMERO_COMPROBANTE);

		//COMPROBANTE PRIMERA SERIE F001
		$NUMERO_COMPROBANTE_F = generaCerosComprobante($id_comprobante_actual_f);
		//CAPTAMOS SERIE DE F001 AL F999
		$NUMERO_SERIE_F = (int)( ( $id_comprobante_actual_f / 99999999 ) + 1 );
		$SERIE_F = "F".generaCerosSerie($NUMERO_SERIE_F);
		//COMPROBANTE SERIE F001 AL SUPERIOR
		if ($NUMERO_SERIE_F>1) {  $NUMERO_COMPROBANTE_F = $id_comprobante_actual_f % 99999999; }
		$COMPROBANTE_F=generaCerosComprobante($NUMERO_COMPROBANTE_F);		

		$empresa = EmpresaData::getDatos();

		$orden_id = $_POST["id"];

		$orden = SellData::getById($orden_id);

		//BOLETA / FACTURA
		$RUC = $empresa->Emp_Ruc;
		$TIPO = $_POST['selTipoComprobante'];
		
		$estado = 1;

		//ARCHIVO CAB
		$tipOperacion = "0101";
		$fecEmision = date("Y-m-d");
		$horEmision = date('H:i:s');
		$fecVencimiento = "-";
		$codLocalEmisor = "0000";

		if($TIPO == 1)
		{
			$tipDocUsuario = 6;
			$SERIE = $SERIE_F;
			$COMPROBANTE = $COMPROBANTE_F;
		}
		else
		{
			$tipDocUsuario = 1;
			$SERIE = $SERIE_B;
			$COMPROBANTE = $COMPROBANTE_B;
		}

		$person_id = $orden->person_id;

		if(is_null($person_id))
		{
			
		}
		else
		{
			$person = PersonData::getById($person_id);

			$numDocUsuario = $person->numero_documento;
			$rznSocialUsuario = $person->name.' '.$person->lastname;
			$codUbigeoCliente = $person->ubigeo;
			$desDireccionCliente = $person->address1;
		}
		
		$tipMoneda = 'PEN';
		$sumTotTributos = 0;
		//precio total
		$sumTotValVenta = $orden->mano_obra;
		$sumPrecioVenta = $orden->mano_obra;

		//ARCHIVO DET
		$codUnidadMedida = "NIU";
		$codProducto = 0;
		$codProductoSUNAT = "-";
		$codTriIGV = "9997";
		$mtoIgvItem = 0;
		$mtoIscItem = 0;
		$nomTributoIgvItem = "EXO";
		$codTipTributoIgvItem = "VAT";
		$tipAfeIGV = 20;
		$porIgvItem = 0;
		$codTriISC = "-";
		$mtoIscItem = "";
		$mtoBaseIscItem = 0;
		$nomTributoIscItem = "";
		$codTipTributoIscItem = "-";
		$tipSisISC = "";
		$porIscItem = "";
		$codTriOtroItem = "-";

		$sumTotTributosItem = 0;

		$mtoTriOtroItem = '';
		$mtoBaseTriOtroItem = '';
		$nomTributoIOtroItem = '';
		$codTipTributoIOtroItem = '';
		$porTriOtroItem = '';

		$mtoValorReferencialUnitario = 0;

		//ARCHVIO TRI
		$ideTributo = "9997";
		$nomTributo = "EXO";
		$codTipTributo = "VAT";
		$mtoBaseImponible = 0;
		$mtoTributo = 0;

		$mtoBaseIgvItem = 0.00;

		//ARCHVIO LEY
		$codLeyenda = "2002";

		if ($codLeyenda == "2001")
		{
			$desLeyenda = "BIENES TRANSFERIDOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA";//$_POST["desLeyenda"];
		}
		if ($codLeyenda == "2002")
		{
			$desLeyenda = "SERVICIOS PRESTADOS EN LA AMAZONIA REGION SELVA PARA SER CONSUMIDOS EN LA MISMA";//$_POST["desLeyenda"];
		}		

		//ARCHIVO ACA
		$ctaBancoNacionDetraccion = "-";
		$codBienDetraccion = "-";
		$porDetraccion = "-";
		$mtoDetraccion = "-";
		$codPaisCliente = 'PE';
		// $codUbigeoCliente = $person->ubigeo;
		// $desDireccionCliente = $person->addres1;
		$codPaisEntrega = "-";
		$codUbigeoEntrega = "-";
		$desDireccionEntrega = "-";
	
		$sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
		$COMPROBANTE = ComprobanteCorrelativoData::siguiente($mysqli, (string)$TIPO, $SERIE, $codLocalEmisor ?? '0000', $sucursalId);
		$id_factura_impresa = $mysqli->insert_id;
		$TIPO_DOC = $TIPO;
		// $ID_TIPO_DOC = $COMPROBANTE;
		$ID_TIPO_DOC = $id_factura_impresa;

		//########## CONTENIDO ARCHIVO DET === 32 ITEMS #############################
		//$downloadfile2 = "../efact1.3.4/sunat_archivos/sfs/DATA/".$RUC."-".$TIPO."-".$SERIE."-".$COMPROBANTE.".det";

		//$filecontent2 = "";		
		
		#BASE DE DATOS TIPO DOCUMENTO - BOLETA/FACTURA
		$sql_DOC = "insert into factura (
		RUC,
		TIPO,
		SERIE,
		COMPROBANTE,
		EXTRA1
		) values (
		\"$RUC\",
		\"$TIPO\",
		\"$SERIE\",
		\"$COMPROBANTE\",
		\"$orden_id\"
		)";

		#CAPTAR TIPO DE DOCUMENTO: FACTURA y SU ID: OBTENIENDO SERIE Y NRO
		$mysqli->query($sql_DOC);

		$id_factura_impresa = $mysqli->insert_id;
		$TIPO = '0'.$TIPO;
		$TIPO_DOC = $TIPO;
		$ID_TIPO_DOC = $id_factura_impresa;

		//cambiar estado a sell
		$orden2= new OrdenTrabajoData();
		$orden2->id = $orden_id;
		$orden2->estado = $estado;
		$orden2->update_estado();

		
		//fin de servicio
		
		$detalle = OperationData::getAllProductsBySellId($orden_id);



		foreach ($detalle as $item)
		{
			$product = $item->getProduct();

			$cantidad = $item->q;
			$precio_unitario = $item->prec_alt;
			$descripcion_producto = addslashes($product->name);



			$mtoValorVentaItem = $cantidad * $precio_unitario;
			$mtoPrecioVentaUnitario = $precio_unitario;
			$mtoBaseIgvItem = $mtoValorVentaItem;

			// $filecontent2 .= $codUnidadMedida.'|'.
			// 				$cantidad.'|'.
			// 				$codProducto."|".
			// 				$codProductoSUNAT."|".
			// 				$descripcion_producto.'|'.
			// 				$precio_unitario.'|'.
			// 				$sumTotTributosItem.'|'.
			// 				$codTriIGV.'|'.
			// 				$mtoIgvItem.'|'.
			// 				$mtoBaseIgvItem.'|'.
			// 				$nomTributoIgvItem.'|'.
			// 				$codTipTributoIgvItem.'|'.
			// 				$tipAfeIGV.'|'.
			// 				$porIgvItem.'|'.
			// 				$codTriISC.'|'.
			// 				$mtoIscItem."|".
			// 				$mtoBaseIscItem."|".
			// 				$nomTributoIscItem."|".
			// 				$codTipTributoIscItem."|".
			// 				$tipSisISC."|".
			// 				$porIscItem."|".
			// 				$codTriOtroItem."|".
			// 				$mtoTriOtroItem."|".
			// 				$mtoBaseTriOtroItem."|".
			// 				$nomTributoIOtroItem."|".
			// 				$codTipTributoIOtroItem."|".
			// 				$porTriOtroItem."|".
			// 				$mtoPrecioVentaUnitario."|".
			// 				$mtoValorVentaItem."|".
			// 				$mtoValorReferencialUnitario."|".PHP_EOL;

			// #BASE DE DATOS ARCHIVO .DET
							
			$sql_DET = "insert into det (
			  TIPO_DOC,
			  ID_TIPO_DOC,
			  codUnidadMedida,
			  ctdUnidadItem,
			  codProducto,
			  codProductoSUNAT,
			  desItem,
			  mtoValorUnitario,
			  sumTotTributosItem,
			  codTriIGV,
			  mtoIgvItem,
			  mtoBaseIgvItem,
			  nomTributoIgvItem,
			  codTipTributoIgvItem,
			  tipAfeIGV,
			  porIgvItem,
			  codTriISC,
			  mtoIscItem,
			  mtoBaseIscItem,
			  nomTributoIscItem,
			  codTipTributoIscItem,
			  tipSisISC,
			  porIscItem,
			  codTriOtroItem,
			  mtoTriOtroItem,
			  mtoBaseTriOtroItem,
			  nomTributoIOtroItem,
			  codTipTributoIOtroItem,
			  porTriOtroItem,
			  mtoPrecioVentaUnitario,
			  mtoValorVentaItem,
			  mtoValorReferencialUnitario
			) values (
			  \"$TIPO_DOC\",
			  \"$ID_TIPO_DOC\",
			  '".$codUnidadMedida."',
			  '".$cantidad."',
			  \"$codProducto\",
			  \"$codProductoSUNAT\",
			  '".$descripcion_producto."',
			  '".$precio_unitario."',
			  \"$sumTotTributosItem\",
			  \"$codTriIGV\",
			  \"$mtoIgvItem\",
			  \"$mtoBaseIgvItem\",
			  \"$nomTributoIgvItem\",
			  \"$codTipTributoIgvItem\",
			  \"$tipAfeIGV\",
			  \"$porIgvItem\",
			  \"$codTriISC\",
			  \"$mtoIscItem\",
			  \"$mtoBaseIscItem,\",
			  \"$nomTributoIscItem\",
			  \"$codTipTributoIscItem\",
			  \"$tipSisISC\",
			  \"$porIscItem\",
			  \"$codTriOtroItem\",
			  \"$mtoTriOtroItem\",
			  \"$mtoBaseTriOtroItem\",
			  \"$nomTributoIOtroItem\",
			  \"$codTipTributoIOtroItem\",
			  \"$porTriOtroItem\",
			  \"$mtoPrecioVentaUnitario\",
			  \"$mtoValorVentaItem\",
			  \"$mtoValorReferencialUnitario\"
			)";

			$mysqli->query($sql_DET);

			$sumTotValVenta = $cantidad * $precio_unitario + $sumTotValVenta;
			$sumPrecioVenta = $sumTotValVenta + $sumTotTributos;//$_POST["sumPrecioVenta"]; UNID * PREC. UNIT
		}

		$mtoBaseImponible = $sumTotValVenta;//$_POST["mtoBaseImponible"];

		$sumDescTotal = 0;
		$sumOtrosCargos = 0;
		$sumTotalAnticipos = 0;
		$sumImpVenta = $sumPrecioVenta-$sumDescTotal+$sumOtrosCargos-$sumTotalAnticipos;
		$ublVersionId = "2.1";	
		$customizationId = "2.0";

		#BASE DE DATOS ARCHIVO .CAB
		$sql_CAB = "insert into cab (
			  TIPO_DOC,
			  ID_TIPO_DOC,
			  tipOperacion,
			  fecEmision,
			  horEmision,
			  fecVencimiento,
			  codLocalEmisor,
			  tipDocUsuario,
			  numDocUsuario,
			  rznSocialUsuario,
			  tipMoneda,
			  sumTotTributos,
			  sumTotValVenta,
			  sumPrecioVenta,
			  sumDescTotal,
			  sumOtrosCargos,
			  sumTotalAnticipos,
			  sumImpVenta,
			  ublVersionId,
			  customizationId
			) values (
			  \"$TIPO_DOC\",
			  \"$ID_TIPO_DOC\",
			  \"$tipOperacion\",
			  \"$fecEmision\",
			  \"$horEmision\",
			  \"$fecVencimiento\",
			  \"$codLocalEmisor\",
			  \"$tipDocUsuario\",
			  \"$numDocUsuario\",
			  \"$rznSocialUsuario\",
			  \"$tipMoneda\",
			  \"$sumTotTributos\",
			  \"$sumTotValVenta\",
			  \"$sumPrecioVenta\",
			  \"$sumDescTotal\",
			  \"$sumOtrosCargos\",
			  \"$sumTotalAnticipos\",
			  \"$sumImpVenta\",
			  \"$ublVersionId\",
			  \"$customizationId\"
			)";

		$mysqli->query($sql_CAB);

		$ar = fopen($downloadfile2, "a") or die("Error al crear");
		fwrite($ar, $filecontent2);

		//FIN SI EXISTE UN METODO POST "SE ESTAN RECIBIENDO DATOS"

		// //########## CONTENIDO ARCHIVO CAB === 21 ITEMS #############################
		// $downloadfile="../efact1.3.4/sunat_archivos/sfs/DATA/".$RUC."-".$TIPO."-".$SERIE."-".$COMPROBANTE.".cab";

		// $filecontent=
		// 	$tipOperacion."|".
		// 	$fecEmision."|".
		// 	$horEmision."|".
		// 	$fecVencimiento."|".
		// 	$codLocalEmisor."|".
		// 	$tipDocUsuario."|".
		// 	$numDocUsuario."|".
		// 	$rznSocialUsuario."|".
		// 	$tipMoneda."|".
		// 	$sumTotTributos."|".
		// 	$sumTotValVenta."|".
		// 	$sumPrecioVenta."|".
		// 	$sumDescTotal."|".
		// 	$sumOtrosCargos."|".
		// 	$sumTotalAnticipos."|".
		// 	$sumImpVenta."|".
		// 	$ublVersionId."|".
		// 	$customizationId."|";

		// $ar = fopen($downloadfile, "a") or die("Error al crear");
		// fwrite($ar, $filecontent);	

		// //########## CONTENIDO ARCHIVO TRI === 6 ITEMS #############################
		// $downloadfile3 = "../efact1.3.4/sunat_archivos/sfs/DATA/".$RUC."-".$TIPO."-".$SERIE."-".$COMPROBANTE.".tri";

		// $filecontent3=
		// 	$ideTributo."|".
		// 	$nomTributo."|".
		// 	$codTipTributo."|".
		// 	$mtoBaseImponible."|".
		// 	$mtoTributo."|";

		// $ar = fopen($downloadfile3, "a") or die("Error al crear");
		// fwrite($ar, $filecontent3);

			#BASE DE DATOS ARCHIVO .TRI
		$sql_TRI = "insert into tri (
			TIPO_DOC,
		  	ID_TIPO_DOC,
			ideTributo,
			nomTributo,
			codTipTributo,
			mtoBaseImponible,
			mtoTributo
			) values (
			\"$TIPO_DOC\",
			\"$ID_TIPO_DOC\",
			\"$ideTributo\",
			\"$nomTributo\",
			\"$codTipTributo\",
			\"$mtoBaseImponible\",
			\"$mtoTributo\"
			)";

		$mysqli->query($sql_TRI);

		// //########## CONTENIDO ARCHIVO ACA === 10 ITEMS #############################
		// $downloadfile10 = "../efact1.3.4/sunat_archivos/sfs/DATA/".$RUC."-".$TIPO."-".$SERIE."-".$COMPROBANTE.".aca";

		// $filecontent10 =
		// 	$ctaBancoNacionDetraccion."|".
		// 	$codBienDetraccion."|".
		// 	$porDetraccion."|".
		// 	$mtoDetraccion."|".
		// 	$codPaisCliente."|".
		// 	$codUbigeoCliente."|".
		// 	$desDireccionCliente."|".
		// 	$codPaisEntrega."|".
		// 	$codUbigeoEntrega."|".
		// 	$desDireccionEntrega."|";

		// 	$ar = fopen($downloadfile10, "a") or die("Error al crear");
		// 	fwrite($ar, $filecontent10);


		#BASE DE DATOS ARCHIVO .TRI
		$sql_CAB = "insert into aca (
			TIPO_DOC,
		  	ID_TIPO_DOC,
			ctaBancoNacionDetraccion,
			codBienDetraccion,
			porDetraccion,
			mtoDetraccion,
			codPaisCliente,
			codUbigeoCliente,
			desDireccionCliente,
			codPaisEntrega,
			codUbigeoEntrega,
			desDireccionEntrega
			) values (
			\"$TIPO_DOC\",
			\"$ID_TIPO_DOC\",
			\"$ctaBancoNacionDetraccion\",
			\"$codBienDetraccion\",
			\"$porDetraccion\",
			\"$mtoDetraccion\",
			\"$codPaisCliente\",
			\"$codUbigeoCliente\",
			\"$desDireccionCliente\",
			\"$codPaisEntrega\",
			\"$codUbigeoEntrega\",
			\"$desDireccionEntrega\"
			)";

		$mysqli->query($sql_CAB);

		// //########## CONTENIDO ARCHIVO LEY === 2 ITEMS #############################0

		// $downloadfile4 = "../efact1.3.4/sunat_archivos/sfs/DATA/".$RUC."-".$TIPO."-".$SERIE."-".$COMPROBANTE.".ley";

		// $filecontent4 =
		// 	$codLeyenda."|".
		// 	$desLeyenda."|";

		// $ar = fopen($downloadfile4, "a") or die("Error al crear");
		// fwrite($ar, $filecontent4);

		#BASE DE DATOS ARCHIVO .LEY
			$sql_LEY = "insert into ley (
			  TIPO_DOC,
			  ID_TIPO_DOC,
			  codLeyenda,
			  desLeyenda
			) values (
			  \"$TIPO_DOC\",
			  \"$ID_TIPO_DOC\",
	  		  \"$codLeyenda\",
	  		  \"$desLeyenda\"
			)";

		$mysqli->query($sql_LEY);

		echo "El archivo de texto se creo correctamente";

		print "<script>window.location='?view=onesell&id=$orden_id';</script>";
	}	
 ?>