<?php
class OperationData
{
	public static $tablename = "operation";
	public $id;
	public $product_id;
	public $q;
	public $prec_alt;
	public $descuento;
	public $operation_type_id;
	public $sell_id;
	public $descripcion;
	public $idpaquete;
	public $created_at;
	public $cu;
	public $name;
	public $cut_id;
	public $VENTAS;
	public $stock_in;
	public $importe_in;
	public $stock_out;
	public $importe_out;
	public $stock_inv;
	public $importe_inv;
	public $Paquete;
	public $barcode;
	public $is_oficial;
	public $estado;
	public $fecha;
	public $producto;
	public $estado_op;
	public $tipo_comprobante_id;
	public $serie;
	public $comprobante;
	public $usuario;
	public $saldo;
	public $laboratorio;
	public $igv_tipo;

	// Dynamic properties to prevent PHP 8.2+ dynamic property deprecation warnings
	public $image;
	public $inventary_min;
	public $is_stock;
	public $stock_tab;
	public $stock_real;
	public $price_in;
	public $price_may;
	public $price_out;
	public $anaquel;
	public $is_active;
	public $stock;
	public $total_qty;
	public $total_amount;
	public $months_list;
	public $total_months;
	public $avg_qty_month;
	public $anio;
	public $mes;
	public $cantidad_total;
	public $total_venta;
	public $stock_actual;
	public $total_sold;
	public $cod_digemid;
	public $fecha_venc;

	public function OperationData()
	{
		$this->name = "";
		$this->product_id = "";
		$this->q = "";
		$this->cu = 0;
		$this->cut_id = "";
		$this->operation_type_id = "";
		$this->descripcion = "";
		$this->idpaquete = "";
		$this->estado = 1;
		$this->created_at = "NOW()";
	}

	public function add()
	{
		$cu_val = ($this->cu !== "" && $this->cu !== null) ? $this->cu : 0;
		$prec_alt_val = ($this->prec_alt !== "" && $this->prec_alt !== null) ? $this->prec_alt : 0;
		$descuento_val = ($this->descuento !== "" && $this->descuento !== null) ? $this->descuento : 0;
		$created_at_val = ($this->created_at === "NOW()" || $this->created_at === null || $this->created_at === "") ? date("Y-m-d H:i:s") : $this->created_at;
		$igv_tipo_val = !empty($this->igv_tipo) ? $this->igv_tipo : "20";

		$sql = "insert into " . self::$tablename . " (product_id,q,cu, prec_alt,descuento, operation_type_id,sell_id,created_at, descripcion,idpaquete,estado,igv_tipo) ";
		$sql .= "value (\"$this->product_id\",\"$this->q\", $cu_val,$prec_alt_val,$descuento_val, $this->operation_type_id,$this->sell_id,\"$created_at_val\", \"$this->descripcion\",\"$this->idpaquete\",1,\"$igv_tipo_val\")";
		$res = Executor::doit($sql);
		
		// Sincronización en tiempo real del stock del producto
		if($res[0]){
			$p = new ProductData();
			$p->update_stock2($this->product_id);
		}
		
		return $res;
	}

	public static function delById($id)
	{
		// Obtenemos el product_id antes de borrar para poder sincronizar
		$sql_get = "SELECT product_id FROM " . self::$tablename . " WHERE id=$id";
		$query = Executor::doit($sql_get);
		$product_id = null;
		if($query[0]->num_rows > 0){
			$row = $query[0]->fetch_array();
			$product_id = $row['product_id'];
		}

		LoteData::reverseForOperation($id, (int)($_SESSION['user_id'] ?? 0));
		$sql = "update " . self::$tablename . " set estado=0 where id=$id";
		$res = Executor::doit($sql);

		if($product_id){
			$p = new ProductData();
			$p->update_stock2($product_id);
		}
		return $res;
	}

	public function del()
	{
		LoteData::reverseForOperation($this->id, (int)($_SESSION['user_id'] ?? 0));
		$sql = "update " . self::$tablename . " set estado=0 where id=$this->id";
		$res = Executor::doit($sql);
		
		if($this->product_id){
			$p = new ProductData();
			$p->update_stock2($this->product_id);
		}
		return $res;
	}

	public function cancel()
	{
		LoteData::reverseForOperation($this->id, (int)($_SESSION['user_id'] ?? 0));
		$sql = "update " . self::$tablename . " set estado=0 where id=$this->id";
		$res = Executor::doit($sql);
		if($this->product_id){
			$p = new ProductData();
			$p->update_stock2($this->product_id);
		}
		return $res;
	}

	// partiendo de que ya tenemos creado un objecto OperationData previamente utilizamos el contexto
	public function update()
	{
		$sql = "update " . self::$tablename . " set product_id=\"$this->product_id\",q=\"$this->q\" where id=$this->id";
		Executor::doit($sql);
	}

	public function updateReab($celda, $valor)
	{
		if ($celda == "q"):
			$sql = "update " . self::$tablename . " set " . $celda . "=" . $valor . " where sell_id=$this->sell_id and product_id = $this->product_id";
		else:
			$sql = "update " . self::$tablename . " set " . $celda . "=" . $valor . ", cu=" . $valor . " where sell_id=$this->sell_id and product_id = $this->product_id";
		endif;
		$query = Executor::doit($sql);

		return $query[0];
	}

	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "select * from " . self::$tablename . " where id=$id_val";
		$query = Executor::doit($sql);
		return Model::one($query[0], new OperationData());
	}


	public static function getAll()
	{
		$sql = "select * from " . self::$tablename . " where estado=1";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}


	public static function getAllByDateOfficial($start, $end)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and date(created_at) >= \"$start\" and date(created_at) <= \"$end\" order by created_at desc";

		if ($start == $end) {
			$sql = "select * from " . self::$tablename . " where estado=1 and date(created_at) = \"$start\" order by created_at desc";
		}

		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getAllByDateOfficialBP($product, $start, $end)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and date(created_at) >= \"$start\" and date(created_at) <= \"$end\" and product_id=$product order by created_at desc";

		if ($start == $end) {
			$sql = "select * from " . self::$tablename . " where estado=1 and date(created_at) = \"$start\" order by created_at desc";
		}

		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getAllMovByDateProductId($product_id, $start, $end)
	{
		$sql = "select op.product_id,
					SUM(CASE WHEN op.operation_type_id = 1 THEN op.q ELSE 0 END) AS stock_in,
					SUM(CASE WHEN op.operation_type_id = 1 THEN op.q * op.prec_alt ELSE 0 END) AS importe_in,
					SUM(CASE WHEN op.operation_type_id = 2 THEN op.q ELSE 0 END) AS stock_out,
					SUM(CASE WHEN op.operation_type_id = 2 THEN op.q * op.prec_alt ELSE 0 END) AS importe_out,
					(SUM(CASE WHEN op.operation_type_id = 1 THEN op.q ELSE 0 END) - 
					SUM(CASE WHEN op.operation_type_id = 2 THEN op.q ELSE 0 END)) AS stock_inv,
					((SUM(CASE WHEN op.operation_type_id = 1 THEN op.q ELSE 0 END) - 
					SUM(CASE WHEN op.operation_type_id = 2 THEN op.q ELSE 0 END)) * 
					(SUM(CASE WHEN op.operation_type_id = 1 THEN op.q * op.prec_alt ELSE 0 END) / 
					NULLIF(SUM(CASE WHEN op.operation_type_id = 1 THEN op.q ELSE 0 END), 0))) AS importe_inv 
	  			from " . self::$tablename . " as op where op.estado=1 and date(op.created_at) between '" . $start . "' and '" . $end . "'  ";

		if ($product_id != 0) {
			$sql .= " and op.product_id = $product_id";
		}

		$sql .= " GROUP BY op.product_id ";


		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	/*REVISA ID DE LAS SOLICITUDADES MAS VENDIDAS*/
	public static function getAllByDateVendido($start, $end)
	{
		//$sql = "select product_id,COUNT(product_id)*q AS VENTAS   from ".self::$tablename." 
		$sql = "select product_id,sum(q) AS VENTAS   from " . self::$tablename . " 
 			where 
 			estado = 1 and
 			date(created_at) >= \"$start\" and 
 			date(created_at) <= \"$end\" and
 			operation_type_id = 2
 			group by product_id 
 			order by VENTAS desc  LIMIT 5";

		if ($start == $end) {
			$sql = "select  product_id,sum(q) AS VENTAS from " . self::$tablename . " 
				where estado = 1 and date(created_at) = \"$start\" and
 				operation_type_id = 2
 				group by product_id 
 				order by VENTAS desc  LIMIT 5";
		}

		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}


	/*CANTIDAD EXCATA VENDIDOS POR PRODUCTO*/
	public static function getAllByDateVendidoProducto($start, $end, $product_id)
	{
		$sql = "select product_id, q   from " . self::$tablename . " 
 			where 
 			estado = 1 and
 			date(created_at) >= \"$start\" and 
 			date(created_at) <= \"$end\" and
 			operation_type_id = 2
 			and product_id = $product_id";

		if ($start == $end) {
			$sql = "select  product_id, q   from " . self::$tablename . " 
				where estado = 1 and date(created_at) = \"$start\" and
 				operation_type_id = 2 and 
 				product_id = $product_id";
		}

		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public function getProduct()
	{
		return ProductData::getById($this->product_id);
	}
	public function getOperationtype()
	{
		return OperationTypeData::getById($this->operation_type_id);
	}
	public function getPaquete()
	{
		$sql = "select idpaquete id,barcode,nombre name,descripcion description  from paquete where idpaquete=" . $this->idpaquete;
		$query = Executor::doit($sql);
		return Model::one($query[0], new ProductData());

	}

	public static function getQYesF($product_id)
	{
		$q = 0;
		$operations = self::getAllByProductId($product_id);
		$input_id = OperationTypeData::getByName("entrada")->id;
		$output_id = OperationTypeData::getByName("salida")->id;
		foreach ($operations as $operation) {
			if ($operation->operation_type_id == $input_id) {
				$q += $operation->q;
			} else if ($operation->operation_type_id == $output_id) {
				$q += (-$operation->q);
			}
		}
		// print_r($data);
		return $q;
	}

	public static function getCantidadbyOperation($product_id, $operation_type_id)
	{
		$sql = "SELECT sum(q) as Cantidad FROM " . self::$tablename . " WHERE estado=1 AND product_id = $product_id AND operation_type_id = $operation_type_id";

		$query = Executor::doit($sql);
		return Model::one($query[0], new OperationData());
	}



	public static function getAllByProductIdCutId($product_id, $cut_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and product_id=$product_id and cut_id=$cut_id order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getAllByProductId($product_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and product_id=$product_id  order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}


	public static function getAllByProductIdCutIdOficial($product_id, $cut_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and product_id=$product_id and cut_id=$cut_id order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}


	public static function getAllProductsBySellId($sell_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and sell_id=$sell_id order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getAllProductsBySellIdAll($sell_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and sell_id=$sell_id order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getAllProductsBySellId2($sell_id)
	{
		$sql = "SELECT product_id,q,prec_alt,descuento,created_at,idpaquete 
			FROM operation 
			WHERE estado=1 AND sell_id=$sell_id
			AND idpaquete= 'X' 
			UNION
			SELECT '0' product_id,'1' q,SUM(prec_alt*q) prec_alt,SUM(descuento*q) descuento,created_at,idpaquete
			FROM operation
			WHERE estado=1 AND sell_id=$sell_id
			AND idpaquete <> 'X'
			GROUP BY created_at,idpaquete";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}


	public static function getAllByProductIdCutIdYesF($product_id, $cut_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and product_id=$product_id and cut_id=$cut_id order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
		//return $array;
	}

	////////////////////////////////////////////////////////////////////
	public static function getOutputQ($product_id, $cut_id)
	{
		$q = 0;
		$operations = self::getOutputByProductIdCutId($product_id, $cut_id);
		$input_id = OperationTypeData::getByName("entrada")->id;
		$output_id = OperationTypeData::getByName("salida")->id;
		foreach ($operations as $operation) {
			if ($operation->operation_type_id == $input_id) {
				$q += $operation->q;
			} else if ($operation->operation_type_id == $output_id) {
				$q += (-$operation->q);
			}
		}
		// print_r($data);
		return $q;
	}

	public static function getOutputQYesF($product_id)
	{
		$q = 0;
		$operations = self::getOutputByProductId($product_id);
		$input_id = OperationTypeData::getByName("entrada")->id;
		$output_id = OperationTypeData::getByName("salida")->id;
		foreach ($operations as $operation) {
			if ($operation->operation_type_id == $input_id) {
				$q += $operation->q;
			} else if ($operation->operation_type_id == $output_id) {
				$q += (-$operation->q);
			}
		}
		// print_r($data);
		return $q;
	}

	public static function getInputQYesF($product_id)
	{
		$q = 0;
		$operations = self::getInputByProductId($product_id);
		$input_id = OperationTypeData::getByName("entrada")->id;
		foreach ($operations as $operation) {
			if ($operation->operation_type_id == $input_id) {
				$q += $operation->q;
			}
		}
		// print_r($data);
		return $q;
	}


	public static function getOutputByProductIdCutId($product_id, $cut_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and product_id=$product_id and cut_id=$cut_id and operation_type_id=2 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}


	public static function getOutputByProductId($product_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and product_id=$product_id and operation_type_id=2 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getInputByProductId($product_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and product_id=$product_id and operation_type_id=1 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}


	////////////////////////////////////////////////////////////////////
	public static function getInputQ($product_id, $cut_id)
	{
		$q = 0;
		//return Model::many($query[0],new OperationData());
		$operations = self::getInputByProductId($product_id);
		$input_id = OperationTypeData::getByName("entrada")->id;
		$output_id = OperationTypeData::getByName("salida")->id;
		foreach ($operations as $operation) {
			if ($operation->operation_type_id == $input_id) {
				$q += $operation->q;
			} else if ($operation->operation_type_id == $output_id) {
				$q += (-$operation->q);
			}
		}
		// print_r($data);
		return $q;
	}


	public static function getInputByProductIdCutId($product_id, $cut_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and product_id=$product_id and cut_id=$cut_id and operation_type_id=1 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getInputByProductIdCutIdYesF($product_id, $cut_id)
	{
		$sql = "select * from " . self::$tablename . " where estado=1 and product_id=$product_id and cut_id=$cut_id and operation_type_id=1 order by created_at desc";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public function update_created_at()
	{
		$sql = "update " . self::$tablename . " set created_at=$this->created_at where id=$this->id";
		Executor::doit($sql);
	}


	////////////////////////////////////////////////////////////////////////////

	public static function getMonthlySalesSummary($sd = null, $ed = null)
	{
		$sql = "SELECT 
					p.id,
					p.name,
					p.stock,
					IFNULL(SUM(o.q), 0) as total_qty,
					IFNULL(SUM(o.q * o.prec_alt), 0) as total_amount,
					GROUP_CONCAT(DISTINCT LPAD(MONTH(o.created_at), 2, '0') ORDER BY MONTH(o.created_at) SEPARATOR ', ') as months_list,
					COUNT(DISTINCT YEAR(o.created_at), MONTH(o.created_at)) as total_months,
					IFNULL(SUM(o.q) / NULLIF(COUNT(DISTINCT YEAR(o.created_at), MONTH(o.created_at)), 0), 0) as avg_qty_month
				FROM product p
				INNER JOIN operation o ON p.id = o.product_id AND o.operation_type_id = 2 AND o.estado = 1";

		if ($sd && $ed) {
			$sql .= " AND DATE(o.created_at) BETWEEN '$sd' AND '$ed' ";
		}

		$sql .= " WHERE p.is_active = 1
				GROUP BY p.id
				ORDER BY total_months DESC, total_qty DESC";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getMonthlySalesDetails($sd = null, $ed = null)
	{
		$sql = "SELECT 
					YEAR(o.created_at) as anio, 
					MONTH(o.created_at) as mes, 
					p.id as product_id,
					p.name as producto, 
					SUM(o.q) as cantidad_total, 
					SUM(o.q * o.prec_alt) as total_venta
				FROM operation o
				INNER JOIN product p ON o.product_id = p.id
				WHERE o.operation_type_id = 2 
				AND o.estado = 1
				AND p.is_active = 1";

		if ($sd && $ed) {
			$sql .= " AND DATE(o.created_at) BETWEEN '$sd' AND '$ed' ";
		}

		$sql .= " GROUP BY YEAR(o.created_at), MONTH(o.created_at), p.id
				ORDER BY anio DESC, mes DESC, cantidad_total DESC";
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getStockBeforeDate($date)
	{
		$sql = "SELECT product_id, 
					   SUM(CASE WHEN operation_type_id = 1 THEN q 
								WHEN operation_type_id = 2 THEN -q 
								ELSE 0 END) as stock
				FROM operation
				WHERE DATE(created_at) < '$date' AND estado = 1
				GROUP BY product_id";
		$query = Executor::doit($sql);
		$stocks = [];
		while($r = $query[0]->fetch_array()){
			$stocks[$r['product_id']] = $r['stock'];
		}
		return $stocks;
	}

	public static function getMonthlyMovementsBetweenDates($sd, $ed)
	{
		$sql = "SELECT product_id,
					   DATE_FORMAT(created_at, '%Y-%m') as mes,
					   SUM(CASE WHEN operation_type_id = 1 THEN q ELSE 0 END) as purchase_qty,
					   SUM(CASE WHEN operation_type_id = 2 THEN q ELSE 0 END) as sales_qty,
					   SUM(CASE WHEN operation_type_id = 2 THEN q * prec_alt ELSE 0 END) as sales_amount
				FROM operation
				WHERE DATE(created_at) BETWEEN '$sd' AND '$ed' AND estado = 1
				GROUP BY product_id, DATE_FORMAT(created_at, '%Y-%m')";
		$query = Executor::doit($sql);
		$movements = [];
		while($r = $query[0]->fetch_array()){
			$movements[$r['product_id']][$r['mes']] = [
				'purchase_qty' => $r['purchase_qty'],
				'sales_qty' => $r['sales_qty'],
				'sales_amount' => $r['sales_amount']
			];
		}
		return $movements;
	}

	public static function getSalesAndStockByProductReport($sd, $ed)
	{
		$sql = "SELECT 
					p.id as product_id,
					p.name as producto,
					SUM(CASE WHEN op.operation_type_id = 2 AND DATE(op.created_at) BETWEEN '$sd' AND '$ed' THEN op.q ELSE 0 END) as cantidad_vendida,
					SUM(CASE WHEN DATE(op.created_at) <= '$ed' THEN 
							 CASE WHEN op.operation_type_id = 1 THEN op.q 
								  WHEN op.operation_type_id = 2 THEN -op.q 
								  ELSE 0 END 
						 ELSE 0 END) as stock_final
				FROM product p
				LEFT JOIN operation op ON p.id = op.product_id AND op.estado = 1
				WHERE p.is_active = 1
				GROUP BY p.id
				HAVING cantidad_vendida > 0 OR stock_final > 0
				ORDER BY cantidad_vendida DESC, p.name ASC";
		
		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getSalesSummary($sd, $ed)
	{
		$sql = "SELECT p.name as producto,
					   SUM(op.q) as qty,
					   SUM(op.q * op.prec_alt) as total
				FROM operation op
				JOIN product p ON p.id = op.product_id
				WHERE op.operation_type_id = 2 
				  AND op.estado = 1
				  AND DATE(op.created_at) BETWEEN '$sd' AND '$ed'
				GROUP BY p.id
				ORDER BY total DESC";
		$query = Executor::doit($sql);
		$data = [];
		while($r = $query[0]->fetch_array()){
			$data[] = $r;
		}
		return $data;
	}

	public static function getBincartReport($sd, $ed, $user_id = 0)
	{
		$sql = "SELECT 
					op.created_at as fecha,
					p.name as producto,
					p.laboratorio,
					p.barcode,
					op.estado as estado_op,
					s.tipo_comprobante as tipo_comprobante_id,
					s.serie,
					s.comprobante,
					u.username as usuario,
					u.name as user_name,
					u.lastname as user_lastname,
					op.operation_type_id,
					op.q,
					op.product_id
				FROM operation op
				INNER JOIN product p ON op.product_id = p.id
				LEFT JOIN sell s ON op.sell_id = s.id
				LEFT JOIN user u ON s.user_id = u.id
				WHERE op.estado = 1 AND DATE(op.created_at) BETWEEN '$sd' AND '$ed' ";
		
		if ($user_id != 0) {
			$sql .= " AND s.user_id = $user_id ";
		}
		
		$sql .= " ORDER BY p.name ASC, op.created_at ASC, op.id ASC ";

		$query = Executor::doit($sql);
		$operations = array();
		while ($r = $query[0]->fetch_array()) {
			$op = new OperationData();
			$op->fecha = $r['fecha'];
			$op->producto = $r['producto'];
			$op->laboratorio = $r['laboratorio'];
			$op->barcode = $r['barcode'];
			$op->estado_op = $r['estado_op'];
			$op->tipo_comprobante_id = $r['tipo_comprobante_id'];
			$op->serie = $r['serie'];
			$op->comprobante = $r['comprobante'];
			
			// Format user
			$op->usuario = trim($r['user_name'].' '.$r['user_lastname']);
			if(empty($op->usuario)) $op->usuario = $r['usuario'];
			
			$op->operation_type_id = $r['operation_type_id'];
			$op->q = $r['q'];
			$op->product_id = $r['product_id'];
			$operations[] = $op;
		}

		// Calculate initial stock for products involved
		$products_involved = array();
		foreach ($operations as $op) {
			$products_involved[$op->product_id] = 0;
		}

		if (count($products_involved) > 0) {
			$prod_ids = implode(",", array_keys($products_involved));
			$sql_stock = "SELECT op.product_id, SUM(
							CASE 
								WHEN op.operation_type_id = 1 THEN op.q 
								WHEN s.tipo_comprobante = '07' THEN op.q 
								ELSE -op.q 
							END
						  ) as stock_inicial 
						  FROM operation op
						  LEFT JOIN sell s ON op.sell_id = s.id
						  WHERE DATE(op.created_at) < '$sd' AND op.estado=1 AND op.product_id IN ($prod_ids) 
						  GROUP BY op.product_id";
			$query_stock = Executor::doit($sql_stock);
			while ($r = $query_stock[0]->fetch_array()) {
				$products_involved[$r['product_id']] = $r['stock_inicial'];
			}
		}

		// Calculate running balance and prepare virtual rows for "Saldo Anterior"
		$final_operations = array();
		$opening_balances_added = array();

		// We need to process chronologically to handle the running balance correctly
		foreach ($operations as $op) {
			// Ensure q is numeric
			$op->q = floatval($op->q);

			// If this is the first time we see this product in the loop, 
			// and it has an initial stock, we add the virtual "Saldo Anterior" row.
			if (!isset($opening_balances_added[$op->product_id])) {
				$stock_inicial = floatval($products_involved[$op->product_id]);
				if ($stock_inicial != 0) {
					$virtual_op = new OperationData();
					$virtual_op->fecha = $sd . " 00:00:00";
					$virtual_op->producto = $op->producto;
					$virtual_op->laboratorio = $op->laboratorio;
					$virtual_op->barcode = $op->barcode;
					$virtual_op->estado_op = 1;
					$virtual_op->tipo_comprobante_id = null;
					$virtual_op->serie = "";
					$virtual_op->comprobante = "SALDO ANTERIOR";
					$virtual_op->usuario = "SISTEMA";
					$virtual_op->operation_type_id = 1; // Se muestra como Entrada
					$virtual_op->q = $stock_inicial;
					$virtual_op->product_id = $op->product_id;
					$virtual_op->saldo = $stock_inicial;
					$final_operations[] = $virtual_op;
				}
				$opening_balances_added[$op->product_id] = true;
			}

			if ($op->estado_op == 1) {
				// Lógica especial para Notas de Crédito (tipo 3 o 07)
				// Una Nota de Crédito sobre una venta SIEMPRE es una ENTRADA de inventario.
				$es_nota_credito = ($op->tipo_comprobante_id == '07');

				if ($op->operation_type_id == 1 || $es_nota_credito) {
					$products_involved[$op->product_id] += $op->q;
					// Si es Nota de Crédito, forzamos el tipo visual a 1 para que aparezca en la columna Entrada
					if ($es_nota_credito) $op->operation_type_id = 1;
				} else if ($op->operation_type_id == 2) {
					$products_involved[$op->product_id] -= $op->q;
				}
			}
			$op->saldo = $products_involved[$op->product_id];
			$final_operations[] = $op;
		}

		return $final_operations;
	}

	public static function getPurchaseProposal($months_to_analyze)
	{
		$sd = date("Y-m-d", strtotime("-$months_to_analyze months"));
		$ed = date("Y-m-d");

		$sql = "SELECT 
                    p.id, 
                    p.name as producto, 
                    p.stock as stock_actual,
                    COALESCE(SUM(CASE WHEN op.operation_type_id=2 AND op.estado=1 AND DATE(op.created_at) BETWEEN '$sd' AND '$ed' THEN op.q ELSE 0 END), 0) as total_sold
                FROM product p
                LEFT JOIN operation op ON p.id = op.product_id
                WHERE p.is_active = 1
                GROUP BY p.id
                HAVING total_sold > 0";

		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getInventoryReport()
	{
		$sql = "SELECT 
                    p.id, 
                    p.barcode, 
                    p.image,
                    p.name, 
                    p.inventary_min, 
                    p.is_stock,
                    p.stock as stock_tab,
                    p.stock as stock_real,
                    p.price_in,
                    p.price_may,
                    p.price_out,
                    p.laboratorio,
                    p.anaquel,
                    p.is_active
                FROM product p
                WHERE p.is_active = 1
                ORDER BY p.name ASC";

		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}

	public static function getProductsWithMovement()
	{
		$sql = "SELECT 
                    p.id,
					p.cod_digemid, 
                    p.barcode, 
                    p.image,
                    p.name, 
                    p.inventary_min, 
                    p.is_stock,
                    p.stock as stock_tab,
                    p.stock as stock_real,
                    p.price_in,
                    p.price_may,
                    p.price_out,
                    p.laboratorio,
                    p.anaquel,
                    p.is_active,
                    p.fecha_venc
                FROM product p
                WHERE p.is_active = 1
                AND EXISTS (SELECT 1 FROM operation op WHERE op.product_id = p.id AND op.estado = 1)
                ORDER BY p.name ASC";

		$query = Executor::doit($sql);
		return Model::many($query[0], new OperationData());
	}
}

?>