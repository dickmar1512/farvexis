<?php

#[AllowDynamicProperties]
class DetData
{
	public static $tablename = "det";
	public $id;
	public $TIPO_DOC;
	public $ID_TIPO_DOC;
	public $codUnidadMedida;
	public $ctdUnidadItem;
	public $codProducto;
	public $codProductoSUNAT;
	public $desItem;
	public $mtoValorUnitario;
	public $sumTotTributosItem;
	public $codTriIGV;
	public $mtoIgvItem;
	public $mtoBaseIgvItem;
	public $nomTributoIgvItem;
	public $codTipTributoIgvItem;
	public $tipAfeIGV;
	public $porIgvItem;
	public $codTriISC;
	public $mtoIscItem;
	public $mtoBaseIscItem;
	public $nomTributoIscItem;
	public $codTipTributoIscItem;
	public $tipSisISC;
	public $porIscItem;
	public $codTriOtroItem;
	public $mtoTriOtroItem;
	public $mtoBaseTriOtroItem;
	public $nomTributoIOtroItem;
	public $codTipTributoIOtroItem;
	public $porTriOtroItem;
	public $mtoPrecioVentaUnitario;
	public $mtoValorVentaItem;
	public $mtoValorReferencialUnitario;

	public function DetData()
	{
		//CABECERA CAB
		$this->codUnidadMedida = "";
		$this->ctdUnidadItem = "";
		$this->codProducto = "";
		$this->codProductoSUNAT = "";
		$this->desItem = "";
		$this->mtoValorUnitario = "";
		$this->sumTotTributosItem = "";
		$this->codTriIGV = "";
		$this->mtoIgvItem = "";
		$this->mtoBaseIgvItem = "";
		$this->nomTributoIgvItem = "";
		$this->codTipTributoIgvItem = "";
		$this->tipAfeIGV = "";
		$this->porIgvItem = "";
		$this->codTriISC = "";
		$this->mtoIscItem = "";
		$this->mtoBaseIscItem = "";
		$this->nomTributoIscItem = "";
		$this->codTipTributoIscItem = "";
		$this->tipSisISC = "";
		$this->porIscItem = "";
		$this->codTriOtroItem = "";
		$this->mtoTriOtroItem = "";
		$this->mtoBaseTriOtroItem = "";
		$this->nomTributoIOtroItem = "";
		$this->codTipTributoIOtroItem = "";
		$this->porTriOtroItem = "";
		$this->mtoPrecioVentaUnitario = "";
		$this->mtoValorVentaItem = "";
		$this->mtoValorReferencialUnitario = "";
	}

	public static function getById($id, $tipo)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id) || $tipo === null || $tipo === "" || $tipo === "NULL" || !is_numeric($tipo)) {
			return array();
		}
		$id_val = intval($id);
		$tipo_val = intval($tipo);
		$sql = "SELECT ctdUnidadItem, mtoValorUnitario, desItem, mtoValorVentaItem, codTriIGV, mtoIgvItem 
			FROM " . self::$tablename . "
			WHERE ID_TIPO_DOC=$id_val and TIPO_DOC=$tipo_val";

		$query = Executor::doit($sql);

		return Model::many($query[0], new DetData());
	}

	public static function getByIdNota($id, $tipo)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id) || $tipo === null || $tipo === "" || $tipo === "NULL" || !is_numeric($tipo)) {
			return array();
		}
		$id_val = intval($id);
		$tipo_val = intval($tipo);
		$sql = "SELECT * FROM " . self::$tablename . "
			WHERE ID_TIPO_DOC=$id_val and TIPO_DOC=$tipo_val";

		$query = Executor::doit($sql);

		return Model::many($query[0], new DetData());
	}

	public static function getByNroDoc($nro)
	{
		$nro_clean = preg_replace('/[^A-Za-z0-9\-]/', '', $nro);
		$sql = "SELECT d.* FROM " . self::$tablename . " d
			INNER JOIN 1_2_factura_yaqha f ON d.ID_TIPO_DOC=f.id
			WHERE CONCAT(f.SERIE,'-',f.COMPROBANTE)='$nro_clean'";

		$query = Executor::doit($sql);

		return Model::many($query[0], new DetData());
	}
}

?>
