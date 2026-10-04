<?php

#[AllowDynamicProperties]
class Det_kit
{
	public static $tablename = "detalle_paq";
	public $iddetalle;
	public $idpaquete;
	public $idprod;
	public $precio;
	public $descuento;
	public $cantidad;
	
	function Det_kit()
	{
		$this->iddetalle = "";
		$this->idpaquete = "";
		$this->idprod = "";
		$this->precio = "";
		$this->descuento = "";
		$this->cantidad = "";
	}

	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return array();
		}
		$id_val = intval($id);
		$sql = "select a.iddetalle,a.idpaquete,a.idprod,b.barcode,b.name,a.precio,a.descuento,a.cantidad from " . self::$tablename . " a, product b where a.idprod = b.id and a.idpaquete=$id_val and a.estado=1";
		$query = Executor::doit($sql);
		return Model::many($query[0], new Det_kit());

	}

	public function add()
	{
		$sql = "insert into " . self::$tablename . " (idpaquete,idprod,fecha_ing,precio,descuento,cantidad,estado) ";
		$sql .= "value (\"$this->idpaquete\",\"$this->idprod\",sysdate(),\"$this->precio\",\"$this->descuento\",\"$this->cantidad\",1)";
		return Executor::doit($sql);
	}

	public static function delId($iddet)
	{
		$sql = "update detalle_paq set estado=0 where iddetalle = $iddet";
		return Executor::doit($sql);
	}

}
?>