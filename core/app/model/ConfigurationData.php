<?php

#[AllowDynamicProperties]
class ConfigurationData
{
	public static $tablename = "configuration";
	public $id;
	public $name;
	public $lastname;
	public $email;
	public $image;
	public $password;
	public $created_at;
	public $val;
	public $short;
	public $kind;
	public $mail;

	public function ConfigurationData()
	{
		$this->name = "";
		$this->lastname = "";
		$this->email = "";
		$this->image = "";
		$this->password = "";
		$this->created_at = "NOW()";
	}

	public function add()
	{
		$sql = "insert into user (name,lastname,email,password,created_at) ";
		$sql .= "value (\"$this->name\",\"$this->lastname\",\"$this->email\",\"$this->password\",$this->created_at)";
		Executor::doit($sql);
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

	// partiendo de que ya tenemos creado un objecto ConfigurationData previamente utilizamos el contexto
	public function update()
	{
		$sql = "update " . self::$tablename . " set val=\"$this->val\" where id=$this->id";
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
		$data = new ConfigurationData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->name = $r['name'];
			$data->lastname = $r['lastname'];
			$data->email = $r['email'];
			$data->password = $r['password'];
			$data->created_at = $r['created_at'];
			$found = $data;
			break;
		}
		return $found;
	}

	public static function getByMail($mail)
	{
		$sql = "select * from " . self::$tablename . " where email=\"$mail\"";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new ConfigurationData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->lastname = $r['lastname'];
			$array[$cnt]->email = $r['email'];
			$array[$cnt]->password = $r['password'];
			$array[$cnt]->created_at = $r['created_at'];
			$cnt++;
		}
		return $array;
	}


	public static function getAll()
	{
		$sql = "select * from " . self::$tablename;
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new ConfigurationData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->short = $r['name'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->kind = $r['kind'];
			$array[$cnt]->val = $r['val'];
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
			$array[$cnt] = new ConfigurationData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->mail = $r['mail'];
			$array[$cnt]->password = $r['password'];
			$array[$cnt]->created_at = $r['created_at'];
			$cnt++;
		}
		return $array;
	}


}

?>