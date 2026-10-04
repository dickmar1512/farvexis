<?php

#[AllowDynamicProperties]
class DetalleOrdenData
{
	public static $tablename = "detalle_orden";

	public function DetalleOrdenData()
	{
		$this->id = "";
		$this->product_id = "";
		$this->q = "";
		$this->prec_alt = "";
		$this->orden_id = "";
		$this->created_at = "NOW()";
	}

	public function add()
	{
		$sql = "insert into " . self::$tablename . " (product_id, q, prec_alt, orden_id) ";
		$sql .= "value (\"$this->product_id\",\"$this->q\", $this->prec_alt, $this->orden_id)";
		return Executor::doit($sql);
	}

	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "select * from " . self::$tablename . " where id=$id_val";
		$query = Executor::doit($sql);
		return Model::one($query[0], new DetalleOrdenData());
	}

	public function getProduct()
	{
		return ProductData::getById($this->product_id);
	}

	public static function getCantidadbyOperation($product_id, $operation_type_id)
	{
		$sql = "SELECT sum(q) as Cantidad FROM " . self::$tablename . " WHERE product_id = $product_id AND operation_type_id = $operation_type_id";

		$query = Executor::doit($sql);
		return Model::one($query[0], new DetalleOrdenData());
	}

	public static function getAllByProductId($product_id)
	{
		$sql = "select * from " . self::$tablename . " where product_id=$product_id  order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new DetalleOrdenData());
	}

	public static function getAllProductsByOrdenId($orden_id)
	{
		$sql = "select * from " . self::$tablename . " where orden_id=$orden_id order by created_at desc";

		$query = Executor::doit($sql);
		return Model::many($query[0], new DetalleOrdenData());
	}

	public static function getAllProductsByOrdenIdGroup($orden_id)
	{
		$sql = "SELECT *, SUM(q) as cantidad FROM " . self::$tablename . " where orden_id = $orden_id
				GROUP BY product_id";

		$query = Executor::doit($sql);
		return Model::many($query[0], new DetalleOrdenData());
	}

	public function del()
	{
		$sql = "update " . self::$tablename . " set estado=0 where id=$this->id";

		Executor::doit($sql);
	}
}

?>