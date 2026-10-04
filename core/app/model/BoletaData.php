<?php

#[AllowDynamicProperties]
class BoletaData
{
	public static $tablename = "boleta";
	public $id;
	public $EXTRA1;
	public $RUC;
	public $SERIE;
	public $COMPROBANTE;
	public $TIPO_DOC;
	PUBLIC $TIPO;
	public $ID_TIPO_DOC;
	public $ESTADO;
	public $tipOperacion;
	public $fecEmision;
	public $horEmision;
	public $codLocalEmisor;
	public $tipDocUsuario;
	public $numDocUsuario;
	public $rznSocialUsuario;
	public $tipMoneda;
	public $codTipoNota;
	public $descMotivo;
	public $tipDocModifica;
	public $serieDocModifica;
	public $sumTotTributos;
	public $sumTotValVenta;
	public $sumPrecioVenta;
	public $sumDescTotal;
	public $sumOtrosCargos;
	public $sumTotalAnticipos;
	public $sumImpVenta;
	public $ublVersionId;
	public $customizationId;

	public function BoletaData()
	{
		$this->RUC = "";
		$this->TIPO = "";
		$this->SERIE = "";
		$this->COMPROBANTE = "";
		$this->fecEmision = "";
		$this->numDocUsuario = "";
		$this->rznSocialUsuario = "";
		$this->sumPrecioVenta = "";
	}


	public static function getBoletasSerie($serie)
	{
		$sql = "select * from " . self::$tablename . " where SERIE='$serie'";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new BoletaData();
			$array[$cnt]->id = $r['id'];
			$cnt++;
		}
		return $array;
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
		$data = new BoletaData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->RUC = $r['RUC'];
			$data->TIPO = $r['TIPO'];
			$data->SERIE = $r['SERIE'];
			$data->COMPROBANTE = $r['COMPROBANTE'];
			$data->EXTRA1 = $r['EXTRA1'];
			$found = $data;
			break;
		}
		return $found;
	}

	public static function getByExtra($id)
	{
		$sql = "select * from " . self::$tablename . " where EXTRA1=$id";
		$query = Executor::doit($sql);
		$found = null;
		$data = new BoletaData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->RUC = $r['RUC'];
			$data->TIPO = $r['TIPO'];
			$data->SERIE = $r['SERIE'];
			$data->COMPROBANTE = $r['COMPROBANTE'];
			$data->EXTRA1 = $r['EXTRA1'];
			$found = $data;
			break;
		}
		return $found;
	}

	public static function getByNumDoc($num)
	{
		$partes = explode('-', trim((string)$num), 2);
		$serie = addslashes($partes[0] ?? '');
		$comprobante = (int)preg_replace('/\D/', '', $partes[1] ?? '');
		$sql = "select * from " . self::$tablename . " where SERIE='$serie' AND CAST(COMPROBANTE AS UNSIGNED)=$comprobante";
		$query = Executor::doit($sql);
		$found = null;
		$data = new BoletaData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->RUC = $r['RUC'];
			$data->TIPO = $r['TIPO'];
			$data->SERIE = $r['SERIE'];
			$data->COMPROBANTE = $r['COMPROBANTE'];
			$data->EXTRA1 = $r['EXTRA1'];
			$found = $data;
			break;
		}
		return $found;
	}

	public static function get_boletas_x_fecha($fecha_inicio, $fecha_fin, $selSerie, $comprobante)
	{

		$condicion = "";

		if ($selSerie != "0") {
			$condicion .= "AND SERIE = '" . $selSerie . "'";
		}

		if ($comprobante != "") {
			$condicion .= "AND COMPROBANTE like '%" . $comprobante . "%'";
		}

		$sql = "SELECT bol.*, cab.fecEmision, cab.numDocUsuario, cab.rznSocialUsuario, cab.sumPrecioVenta
				FROM cab cab
				INNER JOIN boleta bol ON cab.ID_TIPO_DOC = bol.id
				WHERE cab.fecEmision BETWEEN '" . $fecha_inicio . "' AND '" . $fecha_fin . "' AND cab.TIPO_DOC = 3 $condicion";

		$query = Executor::doit($sql);

		return Model::many($query[0], new BoletaData());
	}
	public static function get_boletas_x_fecha2($fecha_inicio, $fecha_fin)
	{

		$condicion = "";

		$sql = "SELECT bol.*, cab.fecEmision, cab.numDocUsuario, cab.rznSocialUsuario, cab.sumPrecioVenta
				FROM cab cab
				INNER JOIN boleta bol ON cab.ID_TIPO_DOC = bol.id
				WHERE cab.fecEmision BETWEEN '" . $fecha_inicio . "' AND '" . $fecha_fin . "' AND cab.TIPO_DOC = 3 $condicion";

		$query = Executor::doit($sql);

		return Model::many($query[0], new BoletaData());
	}

	public static function get_notas_credito_x_fecha($fecha_inicio, $fecha_fin, $selSerie, $comprobante)
	{

		$condicion = "";

		if ($selSerie != "0") {
			$condicion .= "AND SERIE = '" . $selSerie . "'";
		}

		if ($comprobante != "") {
			$condicion .= "AND COMPROBANTE like '%" . $comprobante . "%'";
		}

		$sql = "SELECT notab.*, cab.fecEmision, cab.numDocUsuario, cab.rznSocialUsuario, cab.sumPrecioVenta, cab.serieDocModifica
				FROM not cab
				INNER JOIN boleta notab ON cab.ID_TIPO_DOC = notab.id
				WHERE cab.fecEmision BETWEEN '" . $fecha_inicio . "' AND '" . $fecha_fin . "' AND cab.TIPO_DOC = 7 $condicion";

		// echo $sql; exit();

		$query = Executor::doit($sql);

		return Model::many($query[0], new BoletaData());
	}

	public static function get_notas_debito_x_fecha($fecha_inicio, $fecha_fin, $selSerie, $comprobante)
	{

		$condicion = "";

		if ($selSerie != "0") {
			$condicion .= "AND SERIE = '" . $selSerie . "'";
		}

		if ($comprobante != "") {
			$condicion .= "AND COMPROBANTE like '%" . $comprobante . "%'";
		}

		$sql = "SELECT notab.*, cab.fecEmision, cab.numDocUsuario, cab.rznSocialUsuario, cab.sumPrecioVenta, cab.serieDocModifica
				FROM not cab
				INNER JOIN factura notab ON cab.ID_TIPO_DOC = notab.id
				WHERE cab.fecEmision BETWEEN '" . $fecha_inicio . "' AND '" . $fecha_fin . "' AND cab.TIPO_DOC = 8 $condicion";

		// echo $sql; exit();

		$query = Executor::doit($sql);

		return Model::many($query[0], new BoletaData());
	}

	public static function get_series()
	{

		$sql = "SELECT SERIE FROM " . self::$tablename . " GROUP BY SERIE AND TIPO = '03'";

		$query = Executor::doit($sql);

		return Model::many($query[0], new BoletaData());
	}

	public static function get_series_notas_credito()
	{

		$sql = "SELECT SERIE FROM " . self::$tablename . " GROUP BY SERIE AND TIPO = '07'";

		$query = Executor::doit($sql);

		return Model::many($query[0], new BoletaData());
	}

	public static function get_series_notas_debito()
	{

		$sql = "SELECT SERIE FROM " . self::$tablename . " GROUP BY SERIE AND TIPO = '08'";

		$query = Executor::doit($sql);

		return Model::many($query[0], new BoletaData());
	}


}

?>
