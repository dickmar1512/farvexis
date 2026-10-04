<?php
#[AllowDynamicProperties]
class UnidadMedidaData {
	public static $tablename = "unidad_medida";
	public $id;
	public $name;
	public $sigla;
	public $unidades;

	public function UnidadMedidaData(){
		$this->name = "";
		$this->sigla = "";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (name,sigla) ";
		$sql .= "value (\"$this->name\",\"$this->sigla\")";
		Executor::doit($sql);
	}

	public function update(){
		$sql = "update ".self::$tablename." set 
		name=\"$this->name\",
		sigla=\"$this->sigla\"
		where id=$this->id";
		Executor::doit($sql);
	}

	public function update_unidades(){
		$sql = "update ".self::$tablename." set unidades=\"$this->unidades\" where id=$this->id";
		Executor::doit($sql);
	}

	public static function delById($id){
		$sql = "update ".self::$tablename." set is_active=0 where id=$id";
		Executor::doit($sql);
	}

	public function del(){
		$sql = "update ".self::$tablename." set is_active=0 where id=$this->id";
		Executor::doit($sql);
	}

	public function hide(){
		$sql = "update ".self::$tablename." set is_active=0 where id=$this->id";
		Executor::doit($sql);
	}

	public function active(){
		$sql = "update ".self::$tablename." set is_active=1 where id=$this->id";
		Executor::doit($sql);
	}


	public static function getById($id){
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "select * from ".self::$tablename." where id=$id_val";
		$query = Executor::doit($sql);
		$found = null;
		$data = new UnidadMedidaData();
		while($r = $query[0]->fetch_array()){
			$data->id = $r['id'];
			$data->name = $r['name'];
			$data->sigla = $r['sigla'];
			$found = $data;
			break;
		}
		return $found;
	}

	public static function getByName($name){
		 $sql = "select * from ".self::$tablename." where name=\"$name\"";
		$query = Executor::doit($sql);
		$found = null;
		$data = new UnidadMedidaData();
		while($r = $query[0]->fetch_array()){
			$data->id = $r['id'];
			$data->name = $r['name'];
			$data->unidades = $r['unidades'];
			$found = $data;
			break;
		}
		return $found;
	}


	public static function getAll(){
		$sql = "select * from ".self::$tablename."";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while($r = $query[0]->fetch_array()){
			$array[$cnt] = new UnidadMedidaData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->sigla = $r['sigla'];
			$cnt++;
		}
		return $array;
	}

	public static function getAllActive(){
		$sql = "select * from ".self::$tablename." where is_active=1";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while($r = $query[0]->fetch_array()){
			$array[$cnt] = new UnidadMedidaData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->unidades = $r['unidades'];
			$cnt++;
		}
		return $array;
	}

	public static function getAllUnActive(){
		$sql = "select * from ".self::$tablename." where is_active=0";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while($r = $query[0]->fetch_array()){
			$array[$cnt] = new UnidadMedidaData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$cnt++;
		}
		return $array;
	}


}

?>
