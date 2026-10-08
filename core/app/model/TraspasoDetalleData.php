<?php
#[AllowDynamicProperties]
class TraspasoDetalleData {
	public static $tablename = "traspaso_detalle";
	public $id;
	public $traspaso_id;
	public $product_id_origen;
	public $product_id_destino;
	public $q;
	public $operation_id_origen;
	public $operation_id_destino;

	public function TraspasoDetalleData(){
		$this->product_id_destino = "NULL";
		$this->operation_id_origen = "NULL";
		$this->operation_id_destino = "NULL";
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (traspaso_id, product_id_origen, product_id_destino, q, operation_id_origen, operation_id_destino) ";
		$sql .= "value ($this->traspaso_id, $this->product_id_origen, $this->product_id_destino, $this->q, $this->operation_id_origen, $this->operation_id_destino)";
		return Executor::doit($sql);
	}

	public function update_destino(){
		$sql = "update ".self::$tablename." set product_id_destino=$this->product_id_destino, operation_id_destino=$this->operation_id_destino where id=$this->id";
		return Executor::doit($sql);
	}

	public static function getAllByTraspasoId($traspaso_id){
		$sql = "select * from ".self::$tablename." where traspaso_id=$traspaso_id";
		$query = Executor::doit($sql);
		return Model::many($query[0],new TraspasoDetalleData());
	}

    public function getProductOrigen(){ return ProductData::getById($this->product_id_origen); }
    public function getProductDestino(){ return ProductData::getById($this->product_id_destino); }
}
?>
