<?php

#[AllowDynamicProperties]
class NotData
{
	public static $tablename = "nota";
	public $serieDocModifica;
	public $fecEmision;
	public $horEmision;
	public $id;
	public $codTipoNota;
	public $numDocUsuario;
	public $rznSocialUsuario;
	public $descMotivo;
	public $TIPO_DOC;
	public $RUC;
	public $TIPO;
	public $SERIE;
	public $COMPROBANTE;
	public $NOTA_SERIE;
	public $NOTA_COMPROBANTE;
	public $tipDocModifica;
	public $sumTotTributos;
	public $sumTotValVenta;
	public $sumPrecioVenta;
	public $sumDescTotal;
	public $sumOtrosCargos;
	public $sumTotalAnticipos;
	public $sumImpVenta;
	public $ublVersionId;
	public $customizationId;
	public $ID_TIPO_DOC;
	public $ESTADO;
	public $tipOperacion;
	public $codLocalEmisor;
	public $tipDocUsuario;
	public $tipMoneda;

	public function NotData()
	{
		//CABECERA CAB
		$this->RUC = "";
		$this->TIPO = "";
		$this->SERIE = "";
		$this->COMPROBANTE = "";
	}

	public static function get_notas_credito_factura_x_fecha($start, $end)
	{
		$sql = "SELECT n.*, f.SERIE AS NOTA_SERIE, f.COMPROBANTE AS NOTA_COMPROBANTE
				FROM nota AS n
				INNER JOIN factura AS f ON f.id = n.ID_TIPO_DOC
		 		WHERE date(n.fecEmision) >= \"$start\" and date(n.fecEmision) <= \"$end\"
		 		AND n.TIPO_DOC = 7 AND n.tipDocModifica = 1 ";

		$query = Executor::doit($sql);

		return Model::many($query[0], new NotData());
	}

	public static function get_notas_credito_boleta_x_fecha($start, $end)
	{
		$sql = "SELECT a.*, b.SERIE AS NOTA_SERIE, b.COMPROBANTE AS NOTA_COMPROBANTE
				FROM nota AS a
				INNER JOIN boleta AS b ON b.id = a.ID_TIPO_DOC
		WHERE date(a.fecEmision) >= \"$start\" and date(a.fecEmision) <= \"$end\"
		 AND a.TIPO_DOC = 7 AND a.tipDocModifica = 3 ";

		$query = Executor::doit($sql);

		return Model::many($query[0], new NotData());
	}


	public static function get_notas_debito_factura_x_fecha($start, $end)
	{
		$sql = "SELECT * FROM  nota  
		WHERE date(fecEmision) >= \"$start\" and date(fecEmision) <= \"$end\"
		 AND TIPO_DOC = 8 AND tipDocModifica = 1 ";

		$query = Executor::doit($sql);

		return Model::many($query[0], new NotData());
	}

	public static function get_notas_debito_boleta_x_fecha($start, $end)
	{
		$sql = "SELECT * FROM  nota  
		WHERE date(fecEmision) >= \"$start\" and date(fecEmision) <= \"$end\"
		 AND TIPO_DOC = 8 AND tipDocModifica = 3 ";

		$query = Executor::doit($sql);

		return Model::many($query[0], new NotData());
	}

	public static function getByIdComprobado($m)
	{
		$m_clean = preg_replace('/[^A-Za-z0-9\-]/', '', $m);
		if (empty($m_clean) || strpos($m_clean, '-') === false) {
			return null;
		}
		$sql = "SELECT NC.*,
					COALESCE(F.SERIE, B.SERIE) AS NOTA_SERIE,
					COALESCE(F.COMPROBANTE, B.COMPROBANTE) AS NOTA_COMPROBANTE
				FROM " . self::$tablename . " AS NC
				LEFT JOIN factura AS F ON F.id = NC.ID_TIPO_DOC
					AND NC.tipDocModifica IN ('01', '1') AND F.ESTADO = 1
				LEFT JOIN boleta AS B ON B.id = NC.ID_TIPO_DOC
					AND NC.tipDocModifica IN ('03', '3') AND B.ESTADO = 1
				WHERE NC.serieDocModifica='$m_clean' AND NC.TIPO_DOC = 7 AND NC.ESTADO = 1
					AND (F.id IS NOT NULL OR B.id IS NOT NULL)";
		$query = Executor::doit($sql);
		$found = null;
		$data = new NotData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->TIPO_DOC = $r['TIPO_DOC'];
			$data->ID_TIPO_DOC = $r['ID_TIPO_DOC'];
			$data->ESTADO = $r['ESTADO'];
			$data->tipOperacion = $r['tipOperacion'];
			$data->fecEmision = $r['fecEmision'];
			$data->horEmision = $r['horEmision'];
			$data->codLocalEmisor = $r['codLocalEmisor'];
			$data->tipDocUsuario = $r['tipDocUsuario'];
			$data->numDocUsuario = $r['numDocUsuario'];
			$data->rznSocialUsuario = $r['rznSocialUsuario'];
			$data->tipMoneda = $r['tipMoneda'];
			$data->codTipoNota = $r['codTipoNota'];
			$data->descMotivo = $r['descMotivo'];
			$data->tipDocModifica = $r['tipDocModifica'];
			$data->serieDocModifica = $r['serieDocModifica'];
			$data->sumTotTributos = $r['sumTotTributos'];
			$data->sumTotValVenta = $r['sumTotValVenta'];
			$data->sumPrecioVenta = $r['sumPrecioVenta'];
			$data->sumDescTotal = $r['sumDescTotal'];
			$data->sumOtrosCargos = $r['sumOtrosCargos'];
			$data->sumTotalAnticipos = $r['sumTotalAnticipos'];
			$data->sumImpVenta = $r['sumImpVenta'];
			$data->ublVersionId = $r['ublVersionId'];
			$data->customizationId = $r['customizationId'];
			$data->estado_sunat = $r['estado_sunat'] ?? 'pendiente';
			$data->cdr_codigo = $r['cdr_codigo'] ?? null;
			$data->cdr_descripcion = $r['cdr_descripcion'] ?? null;
			$data->codigo_hash = $r['codigo_hash'] ?? null;
			$data->NOTA_SERIE = $r['NOTA_SERIE'];
			$data->NOTA_COMPROBANTE = $r['NOTA_COMPROBANTE'];
			$data->SERIE = $r['NOTA_SERIE'];
			$data->COMPROBANTE = $r['NOTA_COMPROBANTE'];

			$found = $data;
			break;
		}
		return $found;
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
		$data = new NotData();

		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->TIPO_DOC = $r['TIPO_DOC'];
			$data->ID_TIPO_DOC = $r['ID_TIPO_DOC'];
			$data->ESTADO = $r['ESTADO'];
			$data->tipOperacion = $r['tipOperacion'];
			$data->fecEmision = $r['fecEmision'];
			$data->horEmision = $r['horEmision'];
			$data->codLocalEmisor = $r['codLocalEmisor'];
			$data->tipDocUsuario = $r['tipDocUsuario'];
			$data->numDocUsuario = $r['numDocUsuario'];
			$data->rznSocialUsuario = $r['rznSocialUsuario'];
			$data->tipMoneda = $r['tipMoneda'];
			$data->codTipoNota = $r['codTipoNota'];
			$data->descMotivo = $r['descMotivo'];
			$data->tipDocModifica = $r['tipDocModifica'];
			$data->serieDocModifica = $r['serieDocModifica'];
			$data->sumTotTributos = $r['sumTotTributos'];
			$data->sumTotValVenta = $r['sumTotValVenta'];
			$data->sumPrecioVenta = $r['sumPrecioVenta'];
			$data->sumDescTotal = $r['sumDescTotal'];
			$data->sumOtrosCargos = $r['sumOtrosCargos'];
			$data->sumTotalAnticipos = $r['sumTotalAnticipos'];
			$data->sumImpVenta = $r['sumImpVenta'];
			$data->ublVersionId = $r['ublVersionId'];
			$data->customizationId = $r['customizationId'];
			$data->estado_sunat = $r['estado_sunat'] ?? 'pendiente';
			$data->cdr_codigo = $r['cdr_codigo'] ?? null;
			$data->cdr_descripcion = $r['cdr_descripcion'] ?? null;
			$data->codigo_hash = $r['codigo_hash'] ?? null;

			$found = $data;
			break;
		}

		return $found;
	}
}

?>
