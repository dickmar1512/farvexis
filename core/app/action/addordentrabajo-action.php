<?php
	if(count($_POST)>0)
	{	
		$activo = new ActivoData();

		$activo->nombre = addslashes($_POST["nombre_equipo"]);
		$activo->modelo = addslashes($_POST["modelo_equipo"]);
		$activo->serie = addslashes($_POST["serie_equipo"]);
		$activo->tipo = $_POST["tipo_equipo"];
		$activo->fecha_fabricacion = $_POST["fecha_fabricacion"];
		$activo->fecha_compra = $_POST["fecha_compra"];

		$act = $activo->add();

		$activo_id = $act[1];

		$orden_trabajo = new OrdenTrabajoData();

		$orden_trabajo->person_id = $_POST["selCliente"];
		$orden_trabajo->user_id = $_SESSION["user_id"];
		$orden_trabajo->tecnico_evaluador = addslashes($_POST["tecnico_evaluador"]);
		$orden_trabajo->activo_id = $activo_id;
		$orden_trabajo->descripcion = addslashes($_POST["descripcion"]);
		$orden_trabajo->diagnostico = addslashes($_POST["diagnostico"]);
		$orden_trabajo->mano_obra = $_POST["mano_obra"];
		$orden_trabajo->tipo_servicio = $_POST["selTipoServicio"];
		// $orden_trabajo->fecha_evaluacion = $_POST["fecha_evaluacion"];

		$ort = $orden_trabajo->add();

		print "<script>window.location='./?view=repuestosordentrabajo&id=$ort[1]';</script>";
	}
?>