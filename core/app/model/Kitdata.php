<?php
/**
 * clase para realizar acciones en tabla paquete y detalle_paq 01/11/2020
 */

#[AllowDynamicProperties]
class KitData
{
	public static $tablename = "paquete";
	public $idpaquete;
	public $imagen;
	public $barcode;
	public $nombre;
	public $descripcion;
	public $precio;
	public $fecha_cre;
	public $fecha_fin;
	public $user_id;
	public $estado;

	function Kitdata()
	{
		$this->idpaquete = "";
		$this->imagen = "";
		$this->barcode = "";
		$this->nombre = "";
		$this->descripcion = "";
		$this->precio = "";
		$this->fecha_cre = "NOW()";
		$this->fecha_fin = "NOW()";
		$this->user_id = "";
		$this->estado = "";
	}

	public function add()
	{
		$sql = "insert into " . self::$tablename . " (barcode,nombre,descripcion,precio,fecha_cre,id_user) ";
		$sql .= "value (\"$this->barcode\",\"$this->nombre\",\"$this->descripcion\",\"$this->precio\",sysdate(),\"$this->user_id\")";
		return Executor::doit($sql);
	}

	public function add_with_image()
	{
		$sql = "insert into " . self::$tablename . " (imagen,barcode,nombre,descripcion,precio,fecha_cre,id_user) ";
		$sql .= "value (\"$this->imagen\",\"$this->barcode\",\"$this->nombre\",\"$this->descripcion\",\"$this->precio\",sysdate(),\"$this->user_id\")";
		return Executor::doit($sql);
	}

	public static function getAll()
	{
		$sql = "select * from " . self::$tablename;
		$query = Executor::doit($sql);
		return Model::many($query[0], new KitData());
	}

	public static function getAllByPage($start_from, $limit)
	{
		if (!is_numeric($start_from) || !is_numeric($limit)) {
			return array();
		}
		$start_val = intval($start_from);
		$limit_val = intval($limit);
		$sql = "select * from " . self::$tablename . " where idpaquete>=$start_val limit $limit_val";
		$query = Executor::doit($sql);
		return Model::many($query[0], new KitData());
	}

	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "select * from " . self::$tablename . " where idpaquete=$id_val";
		$query = Executor::doit($sql);
		return Model::one($query[0], new KitData());
	}

	public static function getLike($str)
	{
		$str_clean = addslashes($str);
		$sql = "select * from " . self::$tablename . " where barcode like '%$str_clean%' or nombre like '%$str_clean%' ";
		$query = Executor::doit($sql);
		return Model::many($query[0], new KitData());
	}

	public static function getBarcode()
	{
		$sql = "select concat('P',lpad(count(barcode)+1,4,0)) barcode from " . self::$tablename;
		$query = Executor::doit($sql);
		return Model::many($query[0], new KitData());
	}

	public function update()
	{
		$sql = "update " . self::$tablename . " set barcode=\"$this->barcode\",nombre=\"$this->nombre\",precio=\"$this->precio\",descripcion=\"$this->descripcion\",estado=\"$this->estado\", fecha_upd=sysdate() where idpaquete=$this->idpaquete";
		Executor::doit($sql);
	}

	public function updateestado()
	{
		$sql = "update " . self::$tablename . " set estado=\"$this->estado\", fecha_fin=\"$this->fecha_fin\" where idpaquete=$this->idpaquete";
		Executor::doit($sql);
	}

	public function update_image()
	{
		$sql = "update " . self::$tablename . " set imagen=\"$this->imagen\" where idpaquete=$this->idpaquete";
		Executor::doit($sql);
	}
}
?>