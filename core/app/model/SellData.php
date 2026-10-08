<?php

#[AllowDynamicProperties]
class SellData
{
	public static $tablename = "sell";
	public $id;
	public $person_id;
	public $user_id;
	public $total;
	public $cash;
	public $discount;
	public $created_at;
	public $tipo_comprobante;
	public $serie;
	public $comprobante;
	public $estado;
	public $tipo_pago;
	public $fecha_emi;
	public $box_id;
	public $descuento;
	public $importepp; //importe pago parcial
	public $numero_documento;
	public $name;
	public $address1;
	public $prod;
	public $vendedor;
	public $comp;
	public $tipo;
	public $observacion;
	public $forma_pago;
	public $estado_sunat;
	public $cdr_codigo;
	public $cdr_descripcion;
	public $codigo_hash;
	public $fecha_envio_sunat;
	public $fec_vencimiento;
	public $medico_id;
	public $paciente_id;

	public function SellData()
	{
		$this->created_at = "NOW()";
		$this->fecha_emi = "";
		$this->descuento = 0;
		$this->forma_pago = 1;
		$this->estado_sunat = "pendiente";
		$this->fec_vencimiento = null;
	}

	public function getPerson()
	{
		return PersonData::getById($this->person_id);
	}
	public function getUser()
	{
		return UserData::getById($this->user_id);
	}

	public function add()
	{
		$sql = "insert into " . self::$tablename . " (total, cash, discount,user_id,created_at) ";
		$sql .= "value ($this->total, $this->cash, $this->discount,$this->user_id,$this->created_at)";
		return Executor::doit($sql);
	}

	public function add2()
	{
		$forma_pago_val = !empty($this->forma_pago) ? (int)$this->forma_pago : 1;
		$fec_venc_val = (!empty($this->fec_vencimiento) && $this->fec_vencimiento !== '-') ? "'" . $this->fec_vencimiento . "'" : "NULL";
		$person_id_val = (!empty($this->person_id) && is_numeric($this->person_id)) ? intval($this->person_id) : "NULL";
		$tipo_pago_val = !empty($this->tipo_pago) ? "'" . addslashes($this->tipo_pago) . "'" : "'1'";
		$discount_val = is_numeric($this->discount) ? $this->discount : 0;
		$cash_val = is_numeric($this->cash) ? $this->cash : 0;
		$total_val = is_numeric($this->total) ? $this->total : 0;

		$sql = "insert into " . self::$tablename . " (user_id, tipo_comprobante, serie, comprobante, total, cash, discount, created_at, estado, person_id, tipo_pago, forma_pago, fec_vencimiento, medico_id, paciente_id) ";
		$sql .= "value ($this->user_id, $this->tipo_comprobante, '" . $this->serie . "', '" . $this->comprobante . "', $total_val, $cash_val, $discount_val, '" . $this->created_at . "', 1, $person_id_val, $tipo_pago_val, $forma_pago_val, $fec_venc_val, " . (!empty($this->medico_id) ? intval($this->medico_id) : "NULL") . ", " . (!empty($this->paciente_id) ? intval($this->paciente_id) : "NULL") . ")";
		return Executor::doit($sql);
	}

	public static function updateSunat($id, array $data)
	{
		$estado = $data['estado_sunat'] ?? 'pendiente';
		$codigo = isset($data['cdr_codigo']) ? "'" . addslashes($data['cdr_codigo']) . "'" : "NULL";
		$desc   = isset($data['cdr_descripcion']) ? "'" . addslashes($data['cdr_descripcion']) . "'" : "NULL";
		$hash   = isset($data['codigo_hash']) ? "'" . addslashes($data['codigo_hash']) . "'" : "NULL";
		$fecha  = isset($data['fecha_envio_sunat']) ? "'" . $data['fecha_envio_sunat'] . "'" : "NOW()";

		$sql = "UPDATE " . self::$tablename . " SET estado_sunat='$estado', cdr_codigo=$codigo, cdr_descripcion=$desc, codigo_hash=$hash, fecha_envio_sunat=$fecha WHERE id=$id";
		return Executor::doit($sql);
	}

	public function add_re()
	{
		$sql = "insert into " . self::$tablename . " (user_id,operation_type_id,created_at) ";
		$sql .= "value ($this->user_id,1,$this->created_at)";
		return Executor::doit($sql);
	}

	public function add_re2()
	{
		$sql = "insert into " . self::$tablename . " (person_id,tipo_comprobante, serie, comprobante,fecha_emi, user_id,operation_type_id,created_at,total,cash,discount) ";
		$sql .= "value ($this->person_id,$this->tipo_comprobante, '" . $this->serie . "', lpad($this->comprobante,8,0), STR_TO_DATE('" . $this->fecha_emi . "','%d/%m/%Y'), $this->user_id,1,$this->created_at,$this->total,$this->cash,$this->discount)";
		return Executor::doit($sql);
	}

	public function add_with_client()
	{
		return $this->add2();
	}

	public function add_re_with_client()
	{
		$sql = "insert into " . self::$tablename . " (person_id,operation_type_id,user_id,created_at,tipo_comprobante,serie,comprobante,fecha_emi,total,cash,discount) ";
		$sql .= "value ($this->person_id,1,$this->user_id,NOW(),$this->tipo_comprobante,'" . $this->serie . "',lpad($this->comprobante,8,0), STR_TO_DATE('" . $this->fecha_emi . "','%d/%m/%Y'),$this->total,$this->cash,0)";
		return Executor::doit($sql);
	}

	public function addPagoParcial()
	{
		$sql = "insert into pago_parcial (sellid, tipoPago, importe) ";
		$sql .= "value ($this->id,1, $this->importepp)";
		return Executor::doit($sql);
	}

	public static function delById($id)
	{
		// 1. Marcar venta como anulada
		$sql = "update " . self::$tablename . " set estado=0 where id=$id";
		Executor::doit($sql);
		
		// 2. Obtener productos involucrados antes de anular operaciones
		$ops_sql = "select product_id from operation where sell_id=$id";
		$query = Executor::doit($ops_sql);
		
		// 3. Anular operaciones en cascada
		$sql_op = "update operation set estado=0 where sell_id=$id";
		Executor::doit($sql_op);

		// 4. Sincronizar stock de los productos afectados
		if($query[0]->num_rows > 0){
			$p = new ProductData();
			while($row = $query[0]->fetch_array()){
				$p->update_stock2($row['product_id']);
			}
		}
	}

	public static function getLastBySerie($serie, $tipo = null)
	{
		$sql = "select * from " . self::$tablename . " where serie=\"$serie\"";
		if($tipo != null) { $sql .= " AND tipo_comprobante=\"$tipo\""; }
		$sql .= " order by id desc limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new SellData());
	}

	public function add_sd()
	{
		$sql = "insert into " . self::$tablename . " (user_id, tipo_comprobante, serie, comprobante, total, cash, discount, created_at, estado, person_id, tipo_pago, observacion, operation_type_id) ";
		$sql .= "value ($this->user_id, $this->tipo_comprobante, '" . $this->serie . "', '" . $this->comprobante . "', $this->total, $this->cash, $this->discount, '" . $this->created_at . "', 1, NULL, $this->tipo_pago, \"$this->observacion\", 2)";
		return Executor::doit($sql);
	}

	public function del()
	{
		self::delById($this->id);
	}

	public function cancel()
	{
		self::delById($this->id);
	}

	public function update_box()
	{
		$sql = "update " . self::$tablename . " set box_id=$this->box_id where id=$this->id";
		Executor::doit($sql);

		$upd = "update pago_parcial set boxid = $this->box_id where boxid is null ";
		Executor::doit($upd);
	}

	public function update_tipoPago()
	{
		$sql = "update " . self::$tablename . " set tipo_pago=$this->tipo_pago where id=$this->id";
		Executor::doit($sql);
	}
	public function update_pagoParcial()
	{
		$sql_check = "select * from pago_parcial where sellid=$this->id";
		$query = Executor::doit($sql_check);
		if ($query[0]->num_rows > 0) {
			$sql = "update pago_parcial set importe=$this->importepp where sellid=$this->id";
			Executor::doit($sql);
		} else {
			$sql = "insert into pago_parcial (sellid, tipoPago, importe) value ($this->id, 1, $this->importepp)";
			Executor::doit($sql);
		}
	}

	public function update_proforma_venta()
	{
		$sql = "update " . self::$tablename . " set estado = 1 where id=$this->id";
		return Executor::doit($sql);
	}

	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "select * from " . self::$tablename . " where id=$id_val";
		$query = Executor::doit($sql);
		return Model::one($query[0], new SellData());
	}

	public static function getByNroDoc($num)
	{
		$sql = "select * from " . self::$tablename . " where CONCAT(SERIE,'-',COMPROBANTE)='$num'";
		$query = Executor::doit($sql);
		return Model::one($query[0], new SellData());
	}

	public static function getByNumDoc($num)
	{
		$partes = explode('-', trim((string)$num), 2);
		$serie = addslashes($partes[0] ?? '');
		$comprobante = (int)preg_replace('/\D/', '', $partes[1] ?? '');
		if (empty($serie) || $comprobante <= 0) {
			return null;
		}
		$sql = "select * from " . self::$tablename . " where serie='$serie' AND CAST(comprobante AS UNSIGNED)=$comprobante limit 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new SellData());
	}

	public static function getSells($inicio, $fin, $user_id)
	{
		$sql = "select * from " . self::$tablename . " where operation_type_id=2 AND tipo_comprobante NOT IN (60, 65, 70) AND estado = 1 ";

		if ($user_id != 0) {
			$sql .= " and user_id = $user_id ";
		}

		if ($inicio == "" && $fin == "") {
			$inicio = date('Y-m-d H:i:s');
			$fin = date('Y-m-d H:i:s');
		}

		$sql .= " and  date(created_at) >= '" . $inicio . "' and date(created_at) <= '" . $fin . "' order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getSellsProcedimento($inicio, $fin, $user_id)
	{
		// Validar y escapar parámetros
		$fechaInicio = self::validateAndFormatDate($inicio);
		$fechaFin = self::validateAndFormatDate($fin);
		$userId = (int) $user_id;

		// Escapar fechas para prevenir SQL injection
		$fechaInicio = addslashes($fechaInicio);
		$fechaFin = addslashes($fechaFin);

		$sql = "CALL GetSells('$fechaInicio', '$fechaFin', $userId)";

		try {
			$query = Executor::doit($sql);
			return Model::many($query[0], new SellData());
		} catch (Exception $e) {
			error_log("Error en getSells: " . $e->getMessage());
			throw new Exception("Error al obtener las ventas: " . $e->getMessage());
		}
	}

	/**
	 * Método auxiliar para validar y formatear fechas
	 */
	private static function validateAndFormatDate($fecha)
	{
		if (empty($fecha) || $fecha === '') {
			return date('Y-m-d');
		}

		// Si viene en formato datetime, extraer solo la fecha
		if (strpos($fecha, ' ') !== false) {
			$fecha = substr($fecha, 0, 10);
		}

		// Validar formato de fecha
		$dateObj = DateTime::createFromFormat('Y-m-d', $fecha);
		if ($dateObj && $dateObj->format('Y-m-d') === $fecha) {
			return $fecha;
		}

		// Si no es válida, usar fecha actual
		return date('Y-m-d');
	}

	public static function getSellsOv()
	{
		$sql = "select * from " . self::$tablename . " where operation_type_id=2 AND tipo_comprobante=70  AND estado = 1 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getSellsXfechaOv($inicio, $fin)
	{
		$sql = "select * from " . self::$tablename .
			" where  date(created_at) >= '$inicio' " .
			"and date(created_at) <= '$fin' " .
			"and operation_type_id=2 AND tipo_comprobante =70 AND estado = 1 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getSellsXfechaUsuarioOv($inicio, $fin, $user_id)
	{
		$sql = "select * from " . self::$tablename .
			" where  date(created_at) >= '$inicio' " .
			"and date(created_at) <= '$fin' " .
			"and operation_type_id=2 AND tipo_comprobante=70 AND estado = 1 and user_id = '$user_id' order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getProformas()
	{
		$sql = "select * from " . self::$tablename . " where operation_type_id=2 AND estado = 2 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getSellsUnBoxed()
	{
		$sql = "select id, serie, comprobante, total, created_at, estado, tipo_pago, tipo_comprobante, GetUserName(user_id) as user from " . self::$tablename . " where estado = 1 and operation_type_id=2 AND tipo_comprobante NOT IN (60,65,70) and box_id is NULL order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getByBoxId($id)
	{
		$sql = "select id, serie, comprobante, total, created_at, estado, tipo_pago, tipo_comprobante, GetUserName(user_id) as user from " . self::$tablename . " where estado = 1 and operation_type_id=2 AND tipo_comprobante NOT IN (60,65,70) and box_id=$id order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getRes()
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and operation_type_id=1 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getResAnuladas($start = null, $end = null, $user_id = 0)
	{
		$sql = "select * from " . self::$tablename . " where estado=0 and operation_type_id=1 ";

		if ($start != null && $end != null) {
			$sql .= " and (date(created_at) >= \"$start\" and date(created_at) <= \"$end\") ";
		}

		if ($user_id != 0) {
			$sql .= " and user_id = $user_id ";
		}

		$sql .= " order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getAllByPage($start_from, $limit)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and id<=$start_from limit $limit";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());

	}

	public static function get_ventas_x_id($id)
	{
		$sql = "SELECT sel.id, tipo_comprobante, serie, person_id, comprobante, total, per.numero_documento, CONCAT(per.name, ' ', per.lastname) as name, per.address1, sel.created_at
				FROM sell sel
				LEFT JOIN person per ON per.id = sel.person_id
				WHERE sel.id = $id LIMIT 1";
		// echo $sql; exit;
		$query = Executor::doit($sql);
		return Model::one($query[0], new SellData());
	}

	public static function getVentasOtroTipoPago($box_id, $tipopago, $fechaSd = '', $fechaEd = '', $userid = 0)
	{
		$sql = "SELECT ifnull(sum(sel.total - ifnull(pp.importe,0)),0) as total
				FROM sell sel
				LEFT JOIN pago_parcial pp ON(sel.id = pp.sellid)
				WHERE sel.tipo_comprobante in(3,1) and sel.estado = 1 and sel.operation_type_id = 2 and tipo_pago = " . $tipopago;

		if ($box_id != 0 && $box_id != 'X'):
			$sql .= " and sel.box_id = " . $box_id;
		elseif ($box_id == 0):
			$sql .= " and sel.box_id is null ";
		endif;

		if ($fechaSd != '' && $fechaEd != '') {
			$sql .= " and date(sel.created_at) between \"$fechaSd\" and  \"$fechaEd\" ";
		}

		if ($userid != 0) {
			$sql .= " and sel.user_id = " . $userid;
		}

		$query = Executor::doit($sql);
		return Model::one($query[0], new SellData());
	}

	public static function getAllByDateOp($start, $end, $op)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and date(created_at) >= \"$start\" and date(created_at) <= \"$end\" and operation_type_id=$op order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());

	}

	public static function getAllByDateBCOp($clientid, $start, $end, $op)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and date(created_at) >= \"$start\" and date(created_at) <= \"$end\" and person_id=$clientid  and operation_type_id=$op order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());

	}

	public static function getAllByDateOpProductos($start, $end, $op, $user_id)
	{
		$sql = "select a.serie,a.comprobante,a.total, b.q, b.product_id, GetFullNameProduct(b.product_id) prod,b.prec_alt, b.created_at, a.user_id, GetFullNameUser(a.user_id) AS vendedor
         from " . self::$tablename . " a, operation b 
         where a.estado=1 and b.estado=1 and date(a.created_at) >= \"$start\" 
         and date(a.created_at) <= \"$end\" 
          and a.operation_type_id=$op 
         AND a.id = b.sell_id ";

		if ($user_id <> 0) {
			$sql .= "and a.user_id = \"$user_id\" ";
		}
		$sql .= "order by a.created_at desc";

		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());

	}

	/*****Agregue esto para kardex*****/
	public static function getAllByKardexProd($idprod, $start, $end)
	{
		$sql = "select GetFullNameProduct(a.product_id) prod, GetFullNamePerson(b.person_id) nombre, " .
			"CONCAT(b.serie,'-',b.comprobante) comp, " .
			"(CASE a.operation_type_id WHEN 2 THEN 'VENTA' ELSE 'COMPRA' END) tipo, " .
			"a.q,a.prec_alt,a.descuento,a.cu,a.created_at " .
			"from operation a, " . self::$tablename . " b " .
			"where a.sell_id = b.id and a.estado=1 and b.estado=1 " .
			"and date(a.created_at) >= '$start' " .
			"and date(a.created_at) <= '$end' " .
			"and a.product_id='$idprod'  " .
			"order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	public static function getAllByKardex($start, $end)
	{
		$sql = "select GetFullNameProduct(a.product_id) prod, GetFullNamePerson(b.person_id) nombre, " .
			"CONCAT(b.serie,'-',b.comprobante) comp, " .
			"(CASE a.operation_type_id WHEN 2 THEN 'VENTA' ELSE 'COMPRA' END) tipo, " .
			"a.q,a.prec_alt,a.descuento,a.cu,a.created_at " .
			"from operation a, " . self::$tablename . " b " .
			"where a.sell_id = b.id " .
			"and date(a.created_at) >= '$start' " .
			"and date(a.created_at) <= '$end' " .
			"order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new SellData());
	}

	/**********************************/

	public function update_created_at()
	{
		$sql = "update " . self::$tablename . " set created_at=$this->created_at where id=$this->id";
		Executor::doit($sql);
	}

	public function updateTotalReab($sell_id)
	{
		$sql = "update " . self::$tablename . " set total = (select sum(q*prec_alt)  from operation where sell_id = " . $sell_id . "), cash = (select sum(q*prec_alt) from operation where sell_id = " . $sell_id . ") where id = " . $sell_id;
		Executor::doit($sql);
	}

	public static function getAllUbigeo()
	{
		$sql = "select * from ubigeo";
		$query = Executor::doit($sql);
		return Model::many($query[0], new UserData());
	}
	public static function getAllTipoDoc()
	{
		$sql = "select * from tipo_documento";
		$query = Executor::doit($sql);
		return Model::many($query[0], new UserData());
	}

	public static function getImportePagoParcial($id)
	{
		$sql = "select sellid, ifnull(importe,0) as importe from pago_parcial where sellid = $id";
		$query = Executor::doit($sql);
		//return Model::one($query[0],new SellData());
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new SellData();
			$array[$cnt]->id = $r['sellid'];
			$array[$cnt]->importepp = $r['importe'];
			$cnt++;
		}
		return $array;
	}

	public static function getMonthlySalesGrowth($year = null, $user_id = 0)
	{
		if ($year === null) {
			$year = date('Y');
		}

		// Obtener ventas mensuales del año actual y anterior
		$sql = "SELECT 
					YEAR(s.created_at) as year,
					MONTH(s.created_at) as month,
					SUM(s.total) as total_mes
				FROM " . self::$tablename . " s
				WHERE s.operation_type_id = 2 
					AND s.tipo_comprobante != 70 
					AND s.estado = 1
					AND YEAR(s.created_at) IN (" . ($year - 1) . ", " . $year . ")";

		if ($user_id != 0) {
			$sql .= " AND s.user_id = " . intval($user_id);
		}

		$sql .= " GROUP BY YEAR(s.created_at), MONTH(s.created_at)
				ORDER BY year DESC, month DESC";

		$query = Executor::doit($sql);
		$results = Model::many($query[0], new SellData());

		// Procesar los datos para calcular crecimiento
		$monthlyData = [];
		$currentYearData = [];
		$previousYearData = [];

		// Separar datos por año
		foreach ($results as $row) {
			if ($row->year == $year) {
				$currentYearData[$row->month] = floatval($row->total_mes);
			} elseif ($row->year == ($year - 1)) {
				$previousYearData[$row->month] = floatval($row->total_mes);
			}
		}

		// Generar el cuadro mensual
		$cuadro = [];
		$meses = [
			1 => 'ENERO',
			2 => 'FEBRERO',
			3 => 'MARZO',
			4 => 'ABRIL',
			5 => 'MAYO',
			6 => 'JUNIO',
			7 => 'JULIO',
			8 => 'AGOSTO',
			9 => 'SETIEMBRE',
			10 => 'OCTUBRE',
			11 => 'NOVIEMBRE',
			12 => 'DICIEMBRE'
		];

		for ($mes = 12; $mes >= 1; $mes--) {
			$venta_actual = isset($currentYearData[$mes]) ? $currentYearData[$mes] : 0;
			$venta_anterior = isset($previousYearData[$mes]) ? $previousYearData[$mes] : 0;

			// Calcular crecimiento en soles
			$crecimiento_soles = $venta_anterior > 0 ? $venta_actual - $venta_anterior : 0;

			// Calcular crecimiento porcentual
			$crecimiento_porcentaje = 0;
			if ($venta_anterior > 0) {
				$crecimiento_porcentaje = (($venta_actual - $venta_anterior) / $venta_anterior) * 100;
			} elseif ($venta_actual > 0) {
				$crecimiento_porcentaje = 100; // Si no había ventas el año anterior
			}

			$cuadro[] = [
				'mes' => $meses[$mes],
				'ventas_soles' => number_format($venta_actual, 2),
				'crecimiento_soles' => number_format($crecimiento_soles, 2),
				'crecimiento_porcentaje' => round($crecimiento_porcentaje, 2) . '%'
			];
		}

		return array_reverse($cuadro); // Ordenar de enero a diciembre
	}

	public static function getMonthlySalesComparison($year = null, $user_id = 0)
	{
		if ($year === null) {
			$year = date('Y');
		}

		// Obtener ventas mensuales del año especificado
		$sql = "SELECT 
					MONTH(s.created_at) as month,
					SUM(s.total) as total_mes
				FROM " . self::$tablename . " s
				WHERE s.operation_type_id = 2 
					AND s.tipo_comprobante != 70 
					AND s.estado = 1
					AND YEAR(s.created_at) = " . intval($year);

		if ($user_id != 0) {
			$sql .= " AND s.user_id = " . intval($user_id);
		}

		$sql .= " GROUP BY MONTH(s.created_at)
				ORDER BY month DESC";

		$query = Executor::doit($sql);
		$results = Model::many($query[0], new SellData());

		// Organizar datos por mes
		$monthlyData = [];
		foreach ($results as $row) {
			$monthlyData[$row->month] = floatval($row->total_mes);
		}

		// Generar el cuadro con comparación mes anterior
		$cuadro = [];
		$meses = [
			1 => 'ENERO',
			2 => 'FEBRERO',
			3 => 'MARZO',
			4 => 'ABRIL',
			5 => 'MAYO',
			6 => 'JUNIO',
			7 => 'JULIO',
			8 => 'AGOSTO',
			9 => 'SETIEMBRE',
			10 => 'OCTUBRE',
			11 => 'NOVIEMBRE',
			12 => 'DICIEMBRE'
		];

		for ($mes = 12; $mes >= 1; $mes--) {
			$venta_actual = isset($monthlyData[$mes]) ? $monthlyData[$mes] : 0;
			$venta_anterior = isset($monthlyData[$mes - 1]) ? $monthlyData[$mes - 1] : 0;

			// Calcular crecimiento en soles
			$crecimiento_soles = $venta_anterior > 0 ? $venta_actual - $venta_anterior : 0;

			// Calcular crecimiento porcentual
			$crecimiento_porcentaje = 0;
			if ($venta_anterior > 0) {
				$crecimiento_porcentaje = (($venta_actual - $venta_anterior) / $venta_anterior) * 100;
			} elseif ($venta_actual > 0) {
				$crecimiento_porcentaje = 0; // Si no había ventas el mes anterior
			}

			$cuadro[] = [
				'mes' => $meses[$mes],
				'ventas_soles' => 'S/ ' . number_format($venta_actual, 2),
				'crecimiento_soles' => $crecimiento_soles != 0 ? 'S/ ' . number_format($crecimiento_soles, 2) : 'S/ -',
				'crecimiento_porcentaje' => $crecimiento_porcentaje != 0 ? round($crecimiento_porcentaje, 2) . '%' : '0 %'
			];
		}

		return array_reverse($cuadro);
	}

	public static function getSystemLog()
	{
		$sql = "SELECT s.id, s.created_at, u.name, u.lastname, s.operation_type_id, s.estado, s.total, s.tipo_comprobante, s.serie, s.comprobante 
				FROM " . self::$tablename . " s
				LEFT JOIN user u ON s.user_id = u.id
				ORDER BY s.created_at DESC LIMIT 1000";
		$query = Executor::doit($sql);
		
		$data = [];
		while($r = $query[0]->fetch_array()){
			$data[] = $r;
		}
		return $data;
	}

	// ============================================
	// FUNCIÓN PRINCIPAL DE PROCESAMIENTO
	// ============================================
	public static function generarDatosRotacionInventario($registros, $fechaInicio = null, $fechaFin = null)
	{
		// Convertir objeto a array si es necesario
		if (is_object($registros)) {
			$registros = json_decode(json_encode($registros), true);
		}

		if (empty($registros)) {
			return [];
		}

		// Asegurar que cada registro sea un array y filtrar nulls
		$registros = array_map(function ($registro) {
			if (is_object($registro)) {
				$registroArray = [];
				foreach ($registro as $key => $value) {
					if ($value !== null) {
						$registroArray[$key] = $value;
					}
				}
				return $registroArray;
			}
			return array_filter((array) $registro, fn($v) => $v !== null);
		}, $registros);

		// Determinar rango de fechas
		if (!$fechaInicio || !$fechaFin) {
			$fechaInicio = date('Y-01-01');
			$fechaFin = date('Y-12-31');
		}

		// AGRUPAR REGISTROS POR PRODUCTO
		$productoAgrupado = [];
		foreach ($registros as $registro) {
			$productId = $registro['product_id'] ?? 0;
			if (!isset($productoAgrupado[$productId])) {
				$productoAgrupado[$productId] = [];
			}
			$productoAgrupado[$productId][] = $registro;
		}

		// PROCESAR CADA PRODUCTO
		$resultados = [];

		foreach ($productoAgrupado as $productId => $registrosProducto) {
			$resultado = [
				'producto' => '',
				'product_id' => $productId,
				'meses' => [],
				'resumen' => []
			];

			// Obtener información del producto
			$resultado['producto'] = $registrosProducto[0]['name'] ?? '';

			// Todos los meses del año
			$mesesAnalizar = [
				'01' => 'Enero',
				'02' => 'Febrero',
				'03' => 'Marzo',
				'04' => 'Abril',
				'05' => 'Mayo',
				'06' => 'Junio',
				'07' => 'Julio',
				'08' => 'Agosto',
				'09' => 'Septiembre',
				'10' => 'Octubre',
				'11' => 'Noviembre',
				'12' => 'Diciembre'
			];

			// Inicializar estructura por mes
			foreach ($mesesAnalizar as $numMes => $nombreMes) {
				$resultado['meses'][$numMes] = [
					'nombre' => $nombreMes,
					'stock_inicial' => 0,
					'cant_vendida' => 0,
					'venta_soles' => 0.00,
					'cant_comprada' => 0
				];
			}

			// Variables de control
			$stockActual = 0;
			$mesAnterior = null;

			// Procesar registros del producto
			foreach ($registrosProducto as $registro) {
				$fecha = DateTime::createFromFormat('d/m/Y H:i', $registro['created_at'] ?? '');

				if (!$fecha) {
					continue;
				}

				$mes = $fecha->format('m');
				$anio = $fecha->format('Y');
				$fechaRegistro = $fecha->format('Y-m-d');

				// Filtrar por rango de fechas
				if ($fechaRegistro < $fechaInicio || $fechaRegistro > $fechaFin) {
					continue;
				}

				if (!isset($mesesAnalizar[$mes])) {
					continue;
				}

				$operationType = $registro['operation_type_id'] ?? null;
				$cantidad = $registro['q'] ?? 0;
				$precio = $registro['prec_alt'] ?? 0;

				// Si cambiamos de mes, actualizar stock inicial del nuevo mes
				if ($mesAnterior !== null && $mes != $mesAnterior) {
					$resultado['meses'][$mes]['stock_inicial'] = $stockActual;
				}

				if ($operationType == 1) {
					// Compra/Entrada
					if ($mesAnterior === null) {
						$resultado['meses'][$mes]['stock_inicial'] = $stockActual;
					}
					$stockActual += $cantidad;
					$resultado['meses'][$mes]['cant_comprada'] += $cantidad;
				} else if ($operationType == 2) {
					// Venta/Salida
					if ($mesAnterior === null) {
						$resultado['meses'][$mes]['stock_inicial'] = $stockActual;
					}
					$resultado['meses'][$mes]['cant_vendida'] += $cantidad;
					$resultado['meses'][$mes]['venta_soles'] += ($cantidad * $precio);
					$stockActual -= $cantidad;
				}

				$mesAnterior = $mes;
			}

			// Propagar stock inicial entre meses
			$stockPropagado = 0;
			$primerMesConDatos = true;

			foreach ($mesesAnalizar as $numMes => $nombreMes) {
				if ($primerMesConDatos && ($resultado['meses'][$numMes]['cant_vendida'] > 0 || $resultado['meses'][$numMes]['cant_comprada'] > 0)) {
					$stockPropagado = $resultado['meses'][$numMes]['stock_inicial'];
					$primerMesConDatos = false;
				} else if (!$primerMesConDatos && $resultado['meses'][$numMes]['stock_inicial'] == 0) {
					$resultado['meses'][$numMes]['stock_inicial'] = $stockPropagado;
				}

				// Actualizar stock propagado
				$stockPropagado = $resultado['meses'][$numMes]['stock_inicial']
					+ $resultado['meses'][$numMes]['cant_comprada']
					- $resultado['meses'][$numMes]['cant_vendida'];
			}

			// Calcular RESUMEN
			$totalCantVendida = 0;
			$totalVentaSoles = 0;
			$mesesConVentas = 0;

			foreach ($resultado['meses'] as $mes) {
				$totalCantVendida += $mes['cant_vendida'];
				$totalVentaSoles += $mes['venta_soles'];
				if ($mes['cant_vendida'] > 0) {
					$mesesConVentas++;
				}
			}

			$promedioCantVendida = $mesesConVentas > 0 ? $totalCantVendida / $mesesConVentas : 0;
			$promedioVentasMes = $mesesConVentas > 0 ? $totalVentaSoles / $mesesConVentas : 0;

			// Determinar rotación
			$rotacion = 'SIN MOVIMIENTO';
			$claseRotacion = 'sin-movimiento';

			if ($promedioCantVendida >= 20) {
				$rotacion = '(A)-ALTA ROTACION';
				$claseRotacion = 'alta-rotacion';
			} else if ($promedioCantVendida >= 10) {
				$rotacion = '(M)-MEDIA ROTACION';
				$claseRotacion = 'media-rotacion';
			} else if ($promedioCantVendida > 0) {
				$rotacion = '(B)-BAJA ROTACION';
				$claseRotacion = 'baja-rotacion';
			}

			$resultado['resumen'] = [
				'stock_actual' => $stockActual,
				'promedio_cant_vendida' => round($promedioCantVendida, 2),
				'total_cant_vendida' => $totalCantVendida,
				'estado' => 'ACTIVO',
				'rotacion' => $rotacion,
				'clase_rotacion' => $claseRotacion,
				'promedio_ventas_mes' => round($promedioVentasMes, 2),
				'subtotal_ventas' => round($totalVentaSoles, 2),
				'meses_con_ventas' => $mesesConVentas
			];

			// Agregar resultado del producto al array de resultados
			$resultados[] = $resultado;
		}

		return $resultados;
	}

	public static function generarReporteRotacionVentas($datos)
	{
		// Meses a mostrar (mayo a setiembre)
		$meses = [
			1 => 'Enero',
			2 => 'Febrero',
			3 => 'Marzo',
			4 => 'Abril',
			5 => 'Mayo',
			6 => 'Junio',
			7 => 'Julio',
			8 => 'Agosto',
			9 => 'Setiembre',
			10 => 'Octubre',
			11 => 'Noviembre',
			12 => 'Diciembre'
		];

		// Agrupar datos por producto
		$productos = [];
		foreach ($datos as $fila) {
			$id = $fila['product_id'];
			if (!isset($productos[$id])) {
				$productos[$id] = [
					'name' => $fila['name'],
					'operaciones' => []
				];
			}
			$productos[$id]['operaciones'][] = $fila;
		}

		$resultado = [];

		foreach ($productos as $id => $prod) {
			// Ordenar operaciones por fecha (aunque ya lo estén)
			usort($prod['operaciones'], fn($a, $b) => strtotime($a['created_at']) <=> strtotime($b['created_at']));

			// Inicializar acumuladores mensuales
			$mesData = [];
			foreach ($meses as $num => $nombre) {
				$mesData[$num] = [
					'entradas' => 0,
					'salidas' => 0,
					'ventas_soles' => 0.0
				];
			}

			// Procesar cada operación
			foreach ($prod['operaciones'] as $op) {
				$fecha = new DateTime($op['created_at']);
				$mes = (int) $fecha->format('n'); // 1-12
				$anio = (int) $fecha->format('Y');
				$q = (int) $op['q'];
				$precio = (float) $op['prec_alt'];
				$tipo = (int) $op['operation_type_id'];

				// Solo considerar operaciones dentro de mayo-setiembre 2025
				if ($anio !== 2025 || !isset($mesData[$mes]))
					continue;

				if ($tipo === 1) {
					$mesData[$mes]['entradas'] += $q;
				} elseif ($tipo === 2) {
					$mesData[$mes]['salidas'] += $q;
					$mesData[$mes]['ventas_soles'] += $q * $precio;
				}
			}

			// Calcular stock inicial por mes
			$stock = 0;
			$totalVendido = 0;
			$totalVentas = 0.0;
			$mesesConVenta = 0;

			$fila = [
				'name' => $prod['name'],
				'meses' => []
			];

			foreach ($meses as $num => $nombre) {
				$stock_inicial = $stock;
				$vendido = $mesData[$num]['salidas'];
				$ventas_soles = $mesData[$num]['ventas_soles'];

				$fila['meses'][$num] = [
					'stock_inicial' => $stock_inicial,
					'cant_vendida' => $vendido,
					'venta_soles' => round($ventas_soles, 2)
				];

				// Actualizar stock: entradas - salidas
				$stock = $stock + $mesData[$num]['entradas'] - $vendido;

				$totalVendido += $vendido;
				$totalVentas += $ventas_soles;
				if ($vendido > 0)
					$mesesConVenta++;
			}

			// Calcular métricas finales
			$promedioCantVendida = count($meses) > 0 ? round($totalVendido / count($meses), 2) : 0;
			$promedioVentas = count($meses) > 0 ? round($totalVentas / count($meses), 2) : 0;

			// Estado y rotación
			$estado = 'ACTIVO';
			if ($totalVendido === 0) {
				$rotacion = '(I)-INACTIVO';
			} elseif ($mesesConVenta >= 4) {
				$rotacion = '(A)-ALTA ROTACION';
			} elseif ($mesesConVenta >= 2) {
				$rotacion = '(M)-MEDIA ROTACION';
			} else {
				$rotacion = '(B)-BAJA ROTACION';
			}

			$fila['resumen'] = [
				'stock_actual' => $stock,
				'promedio_cant_vendida' => $promedioCantVendida,
				'total_cant_vendida' => $totalVendido,
				'estado' => $estado,
				'rotacion' => $rotacion,
				'promedio_ventas' => $promedioVentas,
				'subtotal_ventas' => round($totalVentas, 2)
			];

			$resultado[] = $fila;
		}

		return $resultado;
	}

	public static function generarReporteRotacionVentas2($datos)
	{
		$meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
		$productos = [];

		foreach ($datos as $fila) {
			$id = $fila->product_id; // <-- Usar -> en lugar de ['']
			if (!isset($productos[$id])) {
				$productos[$id] = [
					'name' => $fila->name,
					'operaciones' => []
				];
			}
			$productos[$id]['operaciones'][] = $fila;
		}

		$resultado = [];
		foreach ($productos as $id => $prod) {
			// Ordenar operaciones por fecha (aunque ya lo estén)
			usort($prod['operaciones'], fn($a, $b) => strtotime($a['created_at']) <=> strtotime($b['created_at']));
			// Inicializar acumuladores mensuales
			$mesData = [];
			foreach ($meses as $num => $nombre) {
				$mesData[$num] = ['entradas' => 0, 'salidas' => 0, 'ventas_soles' => 0.0];
			}

			foreach ($prod['operaciones'] as $op) {
				$op = (object) $op;
				$fecha = new DateTime($op->created_at);
				$mes = (int) $fecha->format('n');
				//$anio = (int)$fecha->format('Y');
				$q = (int) $op->q;
				$precio = (float) $op->prec_alt;
				$tipo = (int) $op->operation_type_id;

				if ($tipo === 1) {
					$mesData[$mes]['entradas'] += $q;
				} elseif ($tipo === 2) {
					$mesData[$mes]['salidas'] += $q;
					$mesData[$mes]['ventas_soles'] += $q * $precio;
				}
			}
			//return $mesData;
			$stock = 0;
			$totalVendido = 0;
			$totalVentas = 0.0;
			$mesesConVenta = 0;
			$filaResultado = ['name' => $prod['name'], 'meses' => []];

			foreach ($meses as $num => $nombre) {
				$stock_inicial = $stock;
				$vendido = $mesData[$num]['salidas'];
				$ventas_soles = $mesData[$num]['ventas_soles'];

				$filaResultado['meses'][$num] = [
					'stock_inicial' => $stock_inicial,
					'cant_vendida' => $vendido,
					'venta_soles' => round($ventas_soles, 2)
				];

				$stock += $mesData[$num]['entradas'] - $vendido;
				$totalVendido += $vendido;
				$totalVentas += $ventas_soles;
				if ($vendido > 0)
					$mesesConVenta++;
			}

			$promedioCantVendida = count($meses) > 0 ? round($totalVendido / count($meses), 2) : 0;
			$promedioVentas = count($meses) > 0 ? round($totalVentas / count($meses), 2) : 0;

			$rotacion = match (true) {
				$totalVendido === 0 => '(I)-INACTIVO',
				$mesesConVenta >= 4 => '(A)-ALTA ROTACION',
				$mesesConVenta >= 2 => '(M)-MEDIA ROTACION',
				default => '(B)-BAJA ROTACION'
			};

			$filaResultado['resumen'] = [
				'stock_actual' => $stock,
				'promedio_cant_vendida' => $promedioCantVendida,
				'total_cant_vendida' => $totalVendido,
				'estado' => 'ACTIVO',
				'rotacion' => $rotacion,
				'promedio_ventas' => $promedioVentas,
				'subtotal_ventas' => round($totalVentas, 2)
			];

			$resultado[] = $filaResultado;
		}

		return $resultado;
	}
	// ============================================
	// FUNCIÓN PARA OBTENER DATOS DE LA BD
	// ============================================

	public static function obtenerDatosRotacion($productId = null, $fechaInicio = null, $fechaFin = null, $userId = null)
	{
		$sql = "SELECT O.product_id, P.name, O.q, O.prec_alt, O.operation_type_id, 
					O.created_at, O.sell_id
				FROM dbcarepharm.operation AS O
			    INNER JOIN dbcarepharm.product AS P ON (P.id = O.product_id AND P.is_active = 1)
				WHERE O.sell_id <> 0
				AND O.product_id =6355 ";


		if ($productId) {
			$sql .= " AND O.product_id = $productId ";
		}

		if ($fechaInicio && $fechaFin) {
			$sql .= " AND DATE(O.created_at) BETWEEN '" . $fechaInicio . "' AND '" . $fechaFin . "' ";
		}

		// if ($userId) {
		// 	$sql .= " AND O.user_id = $userId";
		// }

		$sql .= " ORDER BY O.product_id, O.created_at ASC";

		$query = Executor::doit($sql);
		$results = Model::many($query[0], new SellData());

		$registros = [];
		foreach ($results as $row) {
			//$registros[] = $row;
			$registros[] = (object) array_filter((array) $row, fn($v) => $v !== null);
		}

		return $registros;
	}
}

?>