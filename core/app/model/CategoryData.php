<?php

#[AllowDynamicProperties]
class CategoryData
{
	public static $tablename = "category";
	public $id;
	public $name;
	public $description;
	public $created_at;
	public $status;
	public $image;
	public $email;

	public function CategoryData()
	{
		$this->name = "";
		$this->description = "";
		$this->email = "";
		$this->image = "";
		$this->created_at = "NOW()";
		$this->status = 1;
	}

	public function add()
	{
		$sql = "insert into category (name,description,created_at) ";
		$sql .= "value ('" . $this->name . "','" . $this->description . "','" . $this->created_at . "')";
		Executor::doit($sql);
	}

	// public static function delById($id){
	// 	$sql = "delete from ".self::$tablename." where id=$id";
	// 	Executor::doit($sql);
	// }

	// public function del(){
	// 	$sql = "delete from ".self::$tablename." where id=$this->id";
	// 	Executor::doit($sql);
	// }

	public function del()
	{
		$sql = "update " . self::$tablename . " set status = " . $this->status . " where id=$this->id";
		Executor::doit($sql);
	}

	// partiendo de que ya tenemos creado un objecto CategoryData previamente utilizamos el contexto
	public function update()
	{
		$sql = "update " . self::$tablename . " set name='" . $this->name . "', description='" . $this->description . "' where id=$this->id";
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
		$data = new CategoryData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->name = $r['name'];
			$data->description = $r['description'];
			$data->created_at = $r['created_at'];
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
			$array[$cnt] = new CategoryData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->description = $r['description'];
			$array[$cnt]->created_at = $r['created_at'];
			$array[$cnt]->status = $r['status'];
			$cnt++;
		}
		return $array;
	}


	public static function getLike($q)
	{
		$sql = "select * from " . self::$tablename . " where name like '%$q%'";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new CategoryData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->created_at = $r['created_at'];
			$cnt++;
		}
		return $array;
	}


}

?>