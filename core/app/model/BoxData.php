<?php

#[AllowDynamicProperties]
class BoxData
{
	public static $tablename = "box";
	public $id;
	public $name;
	public $created_at;
	public $user_id;
	public $user;
	/**parametros para el detalle */
	public $b200;
	public $b100;
	public $b50;
	public $b20;
	public $b10;
	public $m5;
	public $m2;
	public $m1;
	public $c50;
	public $c20;
	public $c10;
	public $lastname;
	public $email;
	public $image;
	public $password;

	public function BoxData()
	{
		$this->name = "";
		$this->lastname = "";
		$this->email = "";
		$this->image = "";
		$this->password = "";
		$this->created_at = date("Y-m-d H:i:s");
	}

	public function add()
	{
		$sql = "insert into " . self::$tablename . " (created_at,user_id) ";
		$sql .= "value ('$this->created_at',$this->user_id)";
		return Executor::doit($sql);
	}

	public function addDetalle()
	{
		$sql = "insert into boxDetalle (idbox,b200,b100,b50,b20,b10,m5,m2,m1,c50,c20,c10,created_at) ";
		$sql .= "value (" . $this->id . "," . $this->b200 . "," . $this->b100 . "," . $this->b50 . "," . $this->b20 . "," . $this->b10 . "," . $this->m5 . "," . $this->m2 . "," . $this->m1 . "," . $this->c50 . "," . $this->c20 . "," . $this->c10 . ",'" . $this->created_at . "')";
		return Executor::doit($sql);
	}

	public static function delById($id)
	{
		$sql = "update " . self::$tablename . " set estado=0 where id=$id";
		Executor::doit($sql);
	}
	public function del()
	{
		$sql = "update " . self::$tablename . " set estado=0 where id=$this->id";
		Executor::doit($sql);
	}

	// partiendo de que ya tenemos creado un objecto BoxData previamente utilizamos el contexto
	public function update()
	{
		$sql = "update " . self::$tablename . " set name=\"$this->name\" where id=$this->id";
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
		$data = new BoxData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->created_at = $r['created_at'];
			$found = $data;
			break;
		}
		return $found;
	}

	public static function getAll()
	{
		$sql = "select id,created_at,GetUserName(user_id) user from " . self::$tablename;
		$query = Executor::doit(sql: $sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new BoxData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->created_at = $r['created_at'];
			$array[$cnt]->user = $r['user'];
			$cnt++;
		}
		return $array;
	}

	public static function getAllByDate($inicio, $fin)
	{
		$sql = "select id,created_at,GetUserName(user_id) user from " . self::$tablename .
			" where  date(created_at) >= '$inicio' " .
			" and date(created_at) <= '$fin' " .
			" order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getByBoxIdDetalle($id)
	{
		$sql = "select * from boxDetalle where  idbox=$id";
		$query = Executor::doit($sql);
		return Model::many($query[0], new BoxData());
	}
}

?>