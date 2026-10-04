<?php

#[AllowDynamicProperties]
class AcaData
{
	public static $tablename = "aca";
	public $desDireccionCliente;
	public $id;
	public $RUC;
	public $TIPO;
	public $SERIE;
	public $COMPROBANTE;

	public function AcaData()
	{
		$this->RUC = "";
		$this->TIPO = "";
		$this->SERIE = "";
		$this->COMPROBANTE = "";
		$this->desDireccionCliente = "";
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
		$data = new AcaData();

		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->desDireccionCliente = $r['desDireccionCliente'];
			$found = $data;
			break;
		}

		return $found;
	}
}

?>
