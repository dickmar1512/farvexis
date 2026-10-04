<?php

#[AllowDynamicProperties]
class Det_IngGas
{
	public static $tablename = "ingresos_pagos";

	function Det_IngGas()
	{
		$this->codigo = "";
		$this->descripcion = "";
		$this->importe = "";
		$this->tipo = "";
	}

	public function add()
	{
		$sql = "insert into " . self::$tablename . " (codigo,descripcion,importe,tipo,id_usuario,fecha_cre) values (\"$this->codigo\",\"$this->descripcion\",\"$this->importe\",\"$this->tipo\",\"$this->id_usuario\",sysdate())";
		return Executor::doit($sql);
	}

	public function delId($id)
	{
		$sql = "update " . self::$tablename . " set estado=0 where id = " . $id;
		return Executor::doit($sql);
	}
}
?>