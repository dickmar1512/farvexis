<?php
	if(count($_POST)>0)
	{
		$orden = OrdenTrabajoData::getById($_POST["orden_id"]);
		$orden_id = $_POST["orden_id"];

		$orden->person_id = $_POST["selCliente"];
		$orden->descripcion = addslashes($_POST["descripcion"]);
		$orden->diagnostico = addslashes($_POST["diagnostico"]);
		$orden->mano_obra = $_POST["mano_obra"];
		$orden->tecnico_evaluador = $_POST["tecnico_evaluador"];
		$orden->fecha_evaluacion = $_POST["fecha_evaluacion"].' '.date("H:m:s");
		$orden->tipo_servicio = $_POST["selTipoServicio"];
		$orden->update_orden();

		$activo = ActivoData::getById($_POST["activo_id"]);

		$activo->nombre = $_POST["nombre_equipo"];
		$activo->modelo = $_POST["modelo_equipo"];
		$activo->serie = $_POST["serie_equipo"];
		$activo->tipo = $_POST["tipo_equipo"];
		$activo->fecha_fabricacion = $_POST["fecha_fabricacion"];
		$activo->fecha_compra = $_POST["fecha_compra"];
		
		$activo->update_activo();

		print "<script>window.location='./?view=oneorden&id=$orden_id';</script>";
	}
?>