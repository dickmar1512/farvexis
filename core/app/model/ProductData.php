<?php

#[AllowDynamicProperties]
class ProductData
{
	public static $tablename = "product";
	public $id;
	public $image;
	public $barcode;
	public $name;
	public $principio_activo;
	public $description;
	public $stock;
	public $is_stock;
	public $inventary_min;
	public $price_in;
	public $price_out;
	public $unit;
	public $presentation;
	public $laboratorio;
	public $reg_san;
	public $user_id;
	public $category_id;
	public $created_at;
	public $fecha_venc;
	public $is_active;
	public $price_may;
	public $anaquel;
	public $is_may;
	public $cod_dig;
	public $cod_digemid;
	public $is_oficial;

	public function ProductData()
	{
		$this->id = "";
		$this->cod_dig = "";
		$this->name = "";
		$this->price_in = "";
		$this->price_out = "";
		$this->price_may = "";
		$this->anaquel = "";
		$this->is_may = "";
		$this->unit = "";
		$this->user_id = "";
		$this->is_stock = "0";
		$this->stock = "0";
		$this->presentation = "0";
		$this->laboratorio = "";
		$this->created_at = "NOW()";
		$this->principio_activo = "";
		$this->fecha_venc = "";
		$this->category_id = "";
	}

	public function getCategory()
	{
		return CategoryData::getById($this->category_id);
	}

	public function add()
	{
		$sql = "insert into " . self::$tablename . " (barcode,name,description,price_in,price_may,price_out, stock, user_id,presentation,unit,category_id,inventary_min,created_at, is_stock,anaquel,principio_activo,fecha_venc,cod_digemid,laboratorio) ";
		$sql .= "value (\"$this->barcode\",\"".addslashes((string)$this->name)."\",\"".addslashes((string)$this->description)."\",\"$this->price_in\",\"$this->price_may\",\"$this->price_out\", \"$this->stock\", $this->user_id,\"".addslashes((string)$this->presentation)."\",\"$this->unit\",$this->category_id,$this->inventary_min,NOW(), $this->is_stock,\"".addslashes((string)$this->anaquel)."\",\"".addslashes((string)$this->principio_activo)."\",\"$this->fecha_venc\",\"$this->cod_digemid\",\"".addslashes((string)$this->laboratorio)."\")";
		return Executor::doit($sql);
	}

	public function add_with_image()
	{
		$sql = "insert into " . self::$tablename . " (barcode,image,name,description,price_in,price_may,price_out,stock, user_id,presentation,unit,category_id,inventary_min,created_at, is_stock,anaquel,principio_activo,fecha_venc,cod_digemid,laboratorio) ";
		$sql .= "value (\"$this->barcode\",\"$this->image\",\"".addslashes((string)$this->name)."\",\"".addslashes((string)$this->description)."\",\"$this->price_in\",\"$this->price_may\",\"$this->price_out\", \"$this->stock\", $this->user_id,\"".addslashes((string)$this->presentation)."\",\"$this->unit\",$this->category_id,$this->inventary_min,NOW(), $this->is_stock,\"".addslashes((string)$this->anaquel)."\",\"".addslashes((string)$this->principio_activo)."\",\"$this->fecha_venc\",\"$this->cod_digemid\",\"".addslashes((string)$this->laboratorio)."\")";
		return Executor::doit($sql);
	}

	public static function delById($id)
	{
		$sql = "update " . self::$tablename . " set is_active=0 where id=$id";
		Executor::doit($sql);
	}

	public function del()
	{
		$sql = "update " . self::$tablename . " set is_active=0 where id=$this->id";
		Executor::doit($sql);
	}

	// partiendo de que ya tenemos creado un objecto ProductData previamente utilizamos el contexto
	public function update()
	{
		$sql = "update " . self::$tablename . " set 
            barcode=\"$this->barcode\",
            name=\"".addslashes((string)$this->name)."\",
            price_in=\"$this->price_in\",
            price_may=\"$this->price_may\",
            price_out=\"$this->price_out\",
            unit=\"$this->unit\",
            presentation=\"".addslashes((string)$this->presentation)."\",
            category_id=$this->category_id,
            inventary_min=\"$this->inventary_min\",
            description=\"".addslashes((string)$this->description)."\",
            is_active=\"$this->is_active\", 
            stock=\"$this->stock\",
            anaquel=\"".addslashes((string)$this->anaquel)."\",
            is_may=\"$this->is_may\",
            principio_activo=\"".addslashes((string)$this->principio_activo)."\",
            fecha_venc=\"$this->fecha_venc\",
            cod_digemid=\"$this->cod_digemid\",
            laboratorio=\"".addslashes((string)$this->laboratorio)."\" 
            where id=$this->id";
		Executor::doit($sql);
	}

	public function update_stock()
	{
		$sql = "update " . self::$tablename . " set stock='" . $this->stock . "' where id=$this->id";
		Executor::doit($sql);
	}

	public function update_stock2($idprod)
	{
		$sql = "UPDATE product p
                SET p.stock = (
                    SELECT COALESCE(SUM(CASE 
                        WHEN op.operation_type_id = 1 THEN op.q 
                        WHEN op.operation_type_id = 2 THEN -op.q 
                        ELSE 0 
                    END), 0)
                    FROM operation op
                    WHERE op.product_id = p.id AND op.estado = 1
                )
                WHERE p.id = $idprod";
		return Executor::doit($sql);
	}

	/***************Update reabastecer*****************/
	public function update_ProductReab($updQArr, $updQOri, $updPriceIn)
	{
		if ($updQArr == $updQOri) {
			$sql = "update product set price_in = " . $updPriceIn . " where id = " . $this->id;
		} else {
			$sql = "update product set stock = stock + " . $updQArr . " - " . $updQOri . " where id = " . $this->id;
		}
		Executor::doit($sql);
	}
	/******************************************/

	public function sumar_stock()
	{
		$sql = "UPDATE " . self::$tablename . " set stock = $this->stock + stock, fecha_venc='" . $this->fecha_venc . "' WHERE id=$this->id";

		Executor::doit($sql);
	}

	public function sumar_stock_name()
	{
		$sql = "UPDATE " . self::$tablename . " set stock = $this->stock + stock WHERE name= '" . $this->name . "'";

		Executor::doit($sql);
	}

	public function restar_stock()
	{
		$sql = "UPDATE " . self::$tablename . " set stock = stock - $this->stock WHERE id=$this->id";
		Executor::doit($sql);
	}

	public function sumar_stock_id()
	{
		$sql = "UPDATE " . self::$tablename . " set stock = $this->stock + stock WHERE name=$this->id";

		Executor::doit($sql);
	}

	public function restar_stock_name()
	{
		$sql = "UPDATE " . self::$tablename . " set stock =  stock - $this->stock WHERE name= '" . $this->name . "'";
		Executor::doit($sql);
	}

	public function del_category()
	{
		$sql = "update " . self::$tablename . " set category_id=NULL where id=$this->id";
		Executor::doit($sql);
	}


	public function update_image()
	{
		$sql = "update " . self::$tablename . " set image=\"$this->image\" where id=$this->id";
		Executor::doit($sql);
	}

	public function update_cu()
	{
		$sql = "update " . self::$tablename . " set price_in =\"$this->price_in\",laboratorio='" . $this->laboratorio . "',reg_san='" . $this->reg_san . "' where id=$this->id";
		Executor::doit($sql);
	}

	public static function getByDuplicate($cod_digemid, $barcode, $name)
	{
		$sql = "select * from " . self::$tablename . " where (cod_digemid <> '' AND cod_digemid = \"$cod_digemid\") OR (barcode <> '' AND barcode = \"$barcode\") OR name = \"$name\" limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new ProductData());
	}

	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "select id,cod_digemid,image,barcode,name,principio_activo,description,stock,is_stock,inventary_min,price_in,price_out,unit,presentation,laboratorio,reg_san,user_id,category_id,created_at,fecha_venc,is_active,price_may,anaquel,is_may from " . self::$tablename . " where id=$id_val";
		$query = Executor::doit($sql);
		return Model::one($query[0], new ProductData());
	}

	public static function getAlertasInventario()
	{
		$sql = "SELECT * 
				FROM " . self::$tablename . "
				WHERE stock <= inventary_min 
				and is_stock =1 
				and length(barcode) > 6 
				and is_active=1
				and id in (select product_id from operation where operation_type_id = 1 group by product_id) 
				order  by stock asc ";
		$query = Executor::doit($sql);
		return Model::many($query[0], new ProductData());
	}

	public static function getAll()
	{
		$sql = "select * from " . self::$tablename . " where id in (select product_id from operation where operation_type_id = 1 group by product_id) order by stock desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new ProductData());
	}

	public static function getBarcode()
	{
		$sql = "select lpad(count(*)+1,6,0) barcode from " . self::$tablename;
		$query = Executor::doit($sql);
		return Model::many($query[0], new ProductData());
	}

	public static function getAll2()
	{
		$sql = "select * from " . self::$tablename . " where stock > 0 and is_active = 1 order by id asc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new ProductData());
	}

	public static function getAllByPage($start_from, $limit)
	{
		$sql = "select * from " . self::$tablename . " where stock > 0 and id>=$start_from ORDER BY price_out limit $limit ";
		$query = Executor::doit($sql);
		return Model::many($query[0], new ProductData());
	}

	public static function getLike($p)
	{
		$sql = "select * from " . self::$tablename . " where is_active = 1 and (barcode like '%$p%' or name like '%$p%' or id like '%$p%' or principio_activo like '%$p%')";
		$query = Executor::doit($sql);
		return Model::many($query[0], new ProductData());
	}

	public static function getLikeSinStock($p)
	{
		$sql = "select * from " . self::$tablename . " where 
		is_stock = 1 and is_active=1 and (
		barcode like '%$p%' or 
		name like '%$p%' or 
		id like '%$p%' )

		";
		$query = Executor::doit($sql);
		return Model::many($query[0], new ProductData());
	}

	public static function getAllByUserId($user_id)
	{
		$sql = "select * from " . self::$tablename . " where user_id=$user_id order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new ProductData());
	}

	public static function getAllByCategoryId($category_id)
	{
		$sql = "select * from " . self::$tablename . " where category_id=$category_id order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new ProductData());
	}
}

?>