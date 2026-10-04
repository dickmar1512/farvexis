<?php

#[AllowDynamicProperties]
class TriData
{
	public static $tablename = "tri";
	public $id;
	public $TIPO_DOC;
	public $ID_TIPO_DOC;
	public $RUC;
	public $TIPO;
	public $SERIE;
	public $COMPROBANTE;
	public $ideTributo;
	public $nomTributo;
	public $codTipTributo;
	public $mtoBaseImponible;
	public $mtoTributo;

	public function TriData()
	{
		//CABECERA CAB
		$this->RUC = "";
		$this->TIPO = "";
		$this->SERIE = "";
		$this->COMPROBANTE = "";
		$this->ideTributo = "";
		$this->id = "";
		$this->nomTributo = "";
		$this->codTipTributo = "";
		$this->mtoBaseImponible = "";
		$this->mtoTributo = "";
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
		$data = new TriData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->TIPO_DOC = $r['TIPO_DOC'];
			$data->ID_TIPO_DOC = $r['ID_TIPO_DOC'];
			$data->codTipTributo = $r['codTipTributo'];
			$data->mtoBaseImponible = $r['mtoBaseImponible'];
			$data->ideTributo = $r['ideTributo'];
			$data->nomTributo = $r['nomTributo'];
			$data->mtoBaseImponible = $r['mtoBaseImponible'];
			$data->mtoTributo = $r['mtoTributo'];
			$found = $data;
			break;
		}
		return $found;
	}
}

?>
