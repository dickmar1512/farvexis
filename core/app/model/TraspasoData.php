<?php
#[AllowDynamicProperties]
class TraspasoData {
	public static $tablename = "traspaso";
	public $id;
	public $sucursal_origen_id;
	public $sucursal_destino_id;
	public $user_id;
	public $user_recepcion_id;
	public $fecha_envio;
	public $fecha_recepcion;
	public $estado;
	public $serie;
	public $comprobante;

	public function TraspasoData(){
		$this->fecha_envio = "NOW()";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (sucursal_origen_id, sucursal_destino_id, user_id, fecha_envio, estado, serie, comprobante) ";
		$sql .= "value ($this->sucursal_origen_id, $this->sucursal_destino_id, $this->user_id, $this->fecha_envio, 1, \"$this->serie\", \"$this->comprobante\")";
		return Executor::doit($sql);
	}

	public function recepcionar(){
		$sql = "update ".self::$tablename." set estado=2, user_recepcion_id=$this->user_recepcion_id, fecha_recepcion=NOW() where id=$this->id";
		return Executor::doit($sql);
	}

	public function anular(){
		$sql = "update ".self::$tablename." set estado=0 where id=$this->id";
		return Executor::doit($sql);
	}

	public static function getById($id){
		$sql = "select * from ".self::$tablename." where id=$id";
		$query = Executor::doit($sql);
		return Model::one($query[0],new TraspasoData());
	}

	public static function getAllByOrigen($sucursal_id){
		$sql = "select * from ".self::$tablename." where sucursal_origen_id=$sucursal_id order by fecha_envio desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new TraspasoData());
	}

	public static function getAllByDestino($sucursal_id){
		$sql = "select * from ".self::$tablename." where sucursal_destino_id=$sucursal_id order by fecha_envio desc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new TraspasoData());
	}

    public function getOrigen(){ return SucursalData::getById($this->sucursal_origen_id); }
    public function getDestino(){ return SucursalData::getById($this->sucursal_destino_id); }
    public function getUser(){ return UserData::getById($this->user_id); }
    public function getUserRecepcion(){ return UserData::getById($this->user_recepcion_id); }
}
?>
