<?php

#[AllowDynamicProperties]
class ActivoData
{
	public static $tablename = "activo";

	public function ActivoData()
	{
		$this->id = "";
		$this->nombre = "";
		$this->modelo = "";
		$this->serie = "";
		$this->tipo = "";
		$this->fecha_fabricacion = "";
		$this->fecha_compra = "";
	}

	public function add()
	{
		$sql = "insert into activo (nombre, modelo, serie, tipo, fecha_fabricacion,  fecha_compra) ";
		$sql .= "value (\"$this->nombre\", \"$this->modelo\", \"$this->serie\", \"$this->tipo\", \"$this->fecha_fabricacion\", \"$this->fecha_compra\")";
		return Executor::doit($sql);
	}

	public function update_activo()
	{
		$sql = "update " . self::$tablename . " set nombre=\"$this->nombre\",modelo=\"$this->modelo\",serie=\"$this->serie\",tipo=\"$this->tipo\",fecha_fabricacion=\"$this->fecha_fabricacion\", fecha_compra=\"$this->fecha_compra\" where id=$this->id";

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
		$data = new ActivoData();

		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->nombre = $r['nombre'];
			$data->modelo = $r['modelo'];
			$data->serie = $r['serie'];
			$data->tipo = $r['tipo'];
			$data->fecha_fabricacion = $r['fecha_fabricacion'];
			$data->fecha_compra = $r['fecha_compra'];
			$found = $data;
			break;
		}

		return $found;
	}
}
?>