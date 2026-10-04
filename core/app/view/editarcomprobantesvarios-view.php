<?php
	$tipo = $_GET["selComprobante"];
	$fecha_nueva = $_GET["fecha_nueva"];

	$boletas = BoletaData::get_boletas_x_fecha2($_GET["fecha_inicio"], $_GET["fecha_fin"]);

	foreach ($boletas as $bol)
	{
		$sell_id = $bol->EXTRA1;
		$sell_id2 = $bol->EXTRA2;
		
		$comp_cab = CabData::getById($bol->id, $tipo);

		$sell_ = SellData::getById($sell_id);

		$created_at = $fecha_nueva.' '.convertir_hora($sell_->created_at);

		$sell = new SellData();
		$sell->id = $sell_id;
		$sell->created_at = $created_at;

		$sell->update_created_at();

		//editar cab
		$bol = BoletaData::getByExtra($sell_id);

		$comp_cab_ = CabData::getById($bol->id, 3);

		$comp_cab = new CabData();
		$comp_cab->id = $comp_cab_->id;
		$comp_cab->fecEmision = $fecha_nueva;
		$comp_cab->horEmision = convertir_hora($sell_->created_at);

		$comp_cab->update_fecha();

		//operation 
		$operations = OperationData::getAllProductsBySellId($sell_id);

		foreach ($operations as $ope)
		{
			$operation = new OperationData();
			$operation->id = $ope->id;
			$operation->created_at = $created_at;

			$operation->update_created_at();
		}

		//editar sell2
		/*$sell_ = Sell2Data::getById($sell_id2);

		$sell = new Sell2Data();
		$sell->id = $sell_id2;
		$sell->created_at = $created_at;

		$sell->update_created_at();*/

		//------------------ editar cab

		//editar texto plano
		$archivo = '../yaqhafactura/sunat_archivos/sfs/DATA/'.$bol->RUC.'-'.$bol->TIPO.'-'.$bol->SERIE.'-'.$bol->COMPROBANTE.'.cab';

		$abrir = fopen($archivo,'r+');
		$contenido = fread($abrir,filesize($archivo));
		fclose($abrir);
		 
		// Separar linea por linea
		$contenido = explode("|",$contenido);
		 
		// Modificar linea deseada ( 2 ) 
		$contenido[1] = $fecha_nueva;
		$contenido[2] = convertir_hora($sell_->created_at);
		$contenido[6] = '00000000';
		 
		// Unir archivo
		$contenido = implode("|", $contenido);
		 
		// Guardar Archivo
		$abrir = fopen($archivo,'w');
		fwrite($abrir,$contenido);
		fclose($abrir);

		
	}

	$facturas = Factura2Data::get_facturas_x_fecha2($_GET["fecha_inicio"], $_GET["fecha_fin"]);

	foreach ($facturas as $fact)
	{
		$sell_id = $fact->EXTRA1;
		$sell_id2 = $fact->EXTRA2;
		
		$comp_cab = CabData::getById($fact->id, $tipo);

		$sell_ = SellData::getById($sell_id);

		$created_at = $fecha_nueva.' '.convertir_hora($sell_->created_at);

		$sell = new SellData();
		$sell->id = $sell_id;
		$sell->created_at = $created_at;

		$sell->update_created_at();

		//editar cab
		$fact = Factura2Data::getByExtra($sell_id);

		$comp_cab_ = CabData::getById($fact->id, 1);

		$comp_cab = new CabData();
		$comp_cab->id = $comp_cab_->id;
		$comp_cab->fecEmision = $fecha_nueva;
		$comp_cab->horEmision = convertir_hora($sell_->created_at);

		$comp_cab->update_fecha();

		//operation 
		$operations = OperationData::getAllProductsBySellId($sell_id);

		foreach ($operations as $ope)
		{
			$operation = new OperationData();
			$operation->id = $ope->id;
			$operation->created_at = $created_at;

			$operation->update_created_at();
		}

		//editar sell2
		/*$sell_ = Sell2Data::getById($sell_id2);

		$sell = new Sell2Data();
		$sell->id = $sell_id2;
		$sell->created_at = $created_at;

		$sell->update_created_at();*/

		//------------------ editar cab

		//editar texto plano
		$archivo = '../yaqhafactura/sunat_archivos/sfs/DATA/'.$fact->RUC.'-'.$fact->TIPO.'-'.$fact->SERIE.'-'.$fact->COMPROBANTE.'.cab';

		$abrir = fopen($archivo,'r+');
		$contenido = fread($abrir,filesize($archivo));
		fclose($abrir);
		 
		// Separar linea por linea
		$contenido = explode("|",$contenido);
		 
		// Modificar linea deseada ( 2 ) 
		$contenido[1] = $fecha_nueva;
		$contenido[2] = convertir_hora($sell_->created_at);
		$contenido[6] = '00000000';
		 
		// Unir archivo
		$contenido = implode("|", $contenido);
		 
		// Guardar Archivo
		$abrir = fopen($archivo,'w');
		fwrite($abrir,$contenido);
		fclose($abrir);

		
	}

	function convertir_fecha($fecha)
	{
	    $date = date_create($fecha);
	    return date_format($date, 'd-m-Y');
	}

	function convertir_hora($fecha)
	{
	    $date = date_create($fecha);
	    return date_format($date, 'H:m:s');
	}
?>
