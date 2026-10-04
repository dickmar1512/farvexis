<?php

#[AllowDynamicProperties]
class LeyData
{
	public static $tablename = "ley";
	public $codLeyenda;
	public $desLeyenda;
	public $id;
	public $TIPO_DOC;
	public $ID_TIPO_DOC;
	public $RUC;
	public $TIPO;
	public $SERIE;
	public $COMPROBANTE;

	public function LeyData()
	{
		//CABECERA CAB
		$this->RUC = "";
		$this->TIPO = "";
		$this->SERIE = "";
		$this->COMPROBANTE = "";
	}

	public static function getById($id, $tipo)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id) || $tipo === null || $tipo === "" || $tipo === "NULL" || !is_numeric($tipo)) {
			return null;
		}
		$id_val = intval($id);
		$tipo_val = intval($tipo);
		$sql = "select * from " . self::$tablename . " where ID_TIPO_DOC=$id_val and TIPO_DOC=$tipo_val";
		$query = Executor::doit($sql);
		$found = null;
		$data = new LeyData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->TIPO_DOC = $r['TIPO_DOC'];
			$data->ID_TIPO_DOC = $r['ID_TIPO_DOC'];
			$data->codLeyenda = $r['codLeyenda'];
			$data->desLeyenda = $r['desLeyenda'];
			$found = $data;
			break;
		}
		return $found;
	}
}

?>
