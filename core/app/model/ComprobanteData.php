<?php

#[AllowDynamicProperties]
class ComprobanteData
{
	public static $tablename = "operation";

	public function OperationData()
	{
		$this->id = "";
		$this->serie = "";
		$this->numero = "";
		$this->tipo = "";
		$this->sell_id = "";
	}

	public function add()
	{
		$sql = "insert into " . self::$tablename . " (serie, numero, tipo , sell_id) ";
		$sql .= "value (\"$this->serie\",\"$this->numero\",$this->tipo, $this->sell_id)";
		return Executor::doit($sql);
	}
}

?>