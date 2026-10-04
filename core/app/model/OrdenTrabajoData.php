<?php

#[AllowDynamicProperties]
class OrdenTrabajoData
{
	public static $tablename = "orden_trabajo";

	public function OrdenTrabajoData()
	{
		$this->id = "";
		$this->person_id = "";
		$this->user_id = "";
		$this->activo_id = "";
		$this->serie_comprobante = "";
		$this->descripcion = "";
		$this->diagnostico = "";
		$this->mano_obra = "";
		$this->fecha_evaluacion = "";
		$this->tipo_servicio = "";
		$this->estado = "";
		$this->created_at = "";
		$this->tecnico_evaluador = "";
	}

	public function add()
	{
		$sql = "insert into orden_trabajo (person_id, user_id, tecnico_evaluador, activo_id, descripcion,  diagnostico, mano_obra, tipo_servicio) ";
		$sql .= "value (\"$this->person_id\", \"$this->user_id\", \"$this->tecnico_evaluador\", \"$this->activo_id\", \"$this->descripcion\", \"$this->diagnostico\", \"$this->mano_obra\", $this->tipo_servicio)";
		return Executor::doit($sql);
	}

	public function update_estado()
	{
		$sql = "update " . self::$tablename . " set estado = 1 where id=$this->id";
		return Executor::doit($sql);
	}

	public function update_orden()
	{
		$sql = "update " . self::$tablename . " set person_id=\"$this->person_id\", tecnico_evaluador=\"$this->tecnico_evaluador\", descripcion=\"$this->descripcion\",diagnostico=\"$this->diagnostico\",mano_obra=\"$this->mano_obra\", fecha_evaluacion=\"$this->fecha_evaluacion\", tipo_servicio=\"$this->tipo_servicio\" where id=$this->id";
		Executor::doit($sql);
	}

	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "select * from " . self::$tablename . " where id=$id_val";
		$query = Executor::doit($sql);
		$found = null;
		$data = new OrdenTrabajoData();

		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->person_id = $r['person_id'];
			$data->user_id = $r['user_id'];
			$data->activo_id = $r['activo_id'];
			$data->serie_comprobante = $r['serie_comprobante'];
			$data->tecnico_evaluador = $r['tecnico_evaluador'];
			$data->tipo_servicio = $r['tipo_servicio'];
			$data->descripcion = $r['descripcion'];
			$data->diagnostico = $r['diagnostico'];
			$data->estado = $r['estado'];
			$data->created_at = $r['created_at'];
			$data->mano_obra = $r['mano_obra'];
			$found = $data;
			break;
		}

		return $found;
	}

	public static function getAll()
	{
		$sql = "select * from " . self::$tablename;
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;

		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new OrdenTrabajoData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->person_id = $r['person_id'];
			$array[$cnt]->user_id = $r['user_id'];
			$array[$cnt]->tecnico_evaluador = $r['tecnico_evaluador'];
			$array[$cnt]->tipo_servicio = $r['tipo_servicio'];
			$array[$cnt]->activo_id = $r['activo_id'];
			$array[$cnt]->serie_comprobante = $r['serie_comprobante'];
			$array[$cnt]->descripcion = $r['descripcion'];
			$array[$cnt]->diagnostico = $r['diagnostico'];
			$array[$cnt]->estado = $r['estado'];
			$array[$cnt]->created_at = $r['created_at'];
			$cnt++;
		}
		return $array;
	}
}
?>