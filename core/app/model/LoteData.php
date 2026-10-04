<?php

#[AllowDynamicProperties]
class LoteData
{
	public static $tablename = "lote";
	public $id;
	public $fech_ing;
	public $id_prod;
	public $num_lot;
	public $id_sell;
	public $user_id;
	public $fecha_fabricacion;
	public $fecha_vencimiento;
	public $proveedor_id;
	public $cantidad_inicial;
	public $cantidad_disponible;
	public $costo_unitario;
	public $estado;
	public $motivo_bloqueo;
	public $ubicacion;
	public $created_at;
	public $updated_at;

	public function LoteData()
	{
		$this->fech_ing = "NOW()";
	}

	public function add()
	{
		$db = Database::getCon();
		$stmt = $db->prepare("INSERT INTO lote
			(id_prod, num_lot, fech_ing, id_sell, user_id, fecha_fabricacion,
			 fecha_vencimiento, proveedor_id, cantidad_inicial, cantidad_disponible,
			 costo_unitario, estado, motivo_bloqueo, ubicacion, created_at)
			VALUES (?, ?, COALESCE(?, NOW()), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
		$fechaIngreso = $this->fech_ing === 'NOW()' ? null : $this->fech_ing;
		$idSell = $this->id_sell === 'NULL' ? null : $this->id_sell;
		$estado = $this->estado ?: 'cuarentena';
		$stmt->bind_param(
			"issiissidddsss",
			$this->id_prod,
			$this->num_lot,
			$fechaIngreso,
			$idSell,
			$this->user_id,
			$this->fecha_fabricacion,
			$this->fecha_vencimiento,
			$this->proveedor_id,
			$this->cantidad_inicial,
			$this->cantidad_disponible,
			$this->costo_unitario,
			$estado,
			$this->motivo_bloqueo,
			$this->ubicacion
		);
		return $stmt->execute();
	}

	public static function getAvailableByProduct($productId)
	{
		$db = Database::getCon();
		$stmt = $db->prepare("SELECT * FROM lote
			WHERE id_prod = ? AND estado = 'disponible'
			AND cantidad_disponible > 0
			AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
			ORDER BY fecha_vencimiento IS NULL, fecha_vencimiento ASC, fech_ing ASC, id ASC");
		$stmt->bind_param("i", $productId);
		$stmt->execute();
		$result = $stmt->get_result();
		$rows = [];
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}

	public static function getAll($status = '', $productId = 0)
	{
		$db = Database::getCon();
		$sql = "SELECT l.*, p.name AS product_name, p.barcode
			FROM lote l INNER JOIN product p ON p.id = l.id_prod WHERE 1=1";
		$params = [];
		$types = '';
		if ($status !== '') {
			$sql .= " AND l.estado = ?";
			$params[] = $status;
			$types .= 's';
		}
		if ((int)$productId > 0) {
			$sql .= " AND l.id_prod = ?";
			$params[] = (int)$productId;
			$types .= 'i';
		}
		$sql .= " ORDER BY l.fecha_vencimiento IS NULL, l.fecha_vencimiento ASC, l.fech_ing DESC, l.id DESC";
		$stmt = $db->prepare($sql);
		if ($params) {
			$stmt->bind_param($types, ...$params);
		}
		$stmt->execute();
		$result = $stmt->get_result();
		$rows = [];
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}

	public static function updateStatus($id, $status, $reason = null)
	{
		$allowed = ['cuarentena', 'disponible', 'bloqueado', 'vencido', 'agotado', 'retirado'];
		if (!in_array($status, $allowed, true)) {
			throw new InvalidArgumentException('Estado de lote no permitido.');
		}
		$db = Database::getCon();
		$stmt = $db->prepare("UPDATE lote SET estado = ?, motivo_bloqueo = ?, updated_at = NOW() WHERE id = ?");
		$stmt->bind_param("ssi", $status, $reason, $id);
		return $stmt->execute();
	}

	public static function canAllocate($productId, $quantity)
	{
		$remaining = (float)$quantity;
		foreach (self::getAvailableByProduct($productId) as $lot) {
			$remaining -= min($remaining, (float)$lot['cantidad_disponible']);
			if ($remaining <= 0.000001) {
				return true;
			}
		}
		return $remaining <= 0.000001;
	}

	public static function registerEntry($productId, $lotNumber, $quantity, $userId, $operationId = null, $expiryDate = null, $manufactureDate = null, $cost = null, $location = null)
	{
		$db = Database::getCon();
		$quantity = (float)$quantity;
		$db->begin_transaction();
		try {
			$find = $db->prepare("SELECT id, cantidad_disponible FROM lote WHERE id_prod = ? AND num_lot = ? FOR UPDATE");
			$find->bind_param("is", $productId, $lotNumber);
			$find->execute();
			$existing = $find->get_result()->fetch_assoc();

			if ($existing) {
				$lotId = (int)$existing['id'];
				$update = $db->prepare("UPDATE lote SET cantidad_inicial = cantidad_inicial + ?,
					cantidad_disponible = cantidad_disponible + ?, fecha_vencimiento = COALESCE(?, fecha_vencimiento),
					fecha_fabricacion = COALESCE(?, fecha_fabricacion), costo_unitario = COALESCE(?, costo_unitario),
					ubicacion = COALESCE(?, ubicacion), estado = 'disponible', updated_at = NOW() WHERE id = ?");
				$update->bind_param("ddssdsi", $quantity, $quantity, $expiryDate, $manufactureDate, $cost, $location, $lotId);
				$update->execute();
			} else {
				$insert = $db->prepare("INSERT INTO lote
					(id_prod, num_lot, fech_ing, user_id, fecha_fabricacion, fecha_vencimiento,
					 cantidad_inicial, cantidad_disponible, costo_unitario, estado, ubicacion, created_at)
					VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, 'disponible', ?, NOW())");
				$insert->bind_param("isissddds", $productId, $lotNumber, $userId, $manufactureDate, $expiryDate, $quantity, $quantity, $cost, $location);
				$insert->execute();
				$lotId = $db->insert_id;
			}

			$movement = $db->prepare("INSERT INTO lote_movimiento
				(lote_id, product_id, operation_id, tipo, cantidad, costo_unitario, referencia, user_id)
				VALUES (?, ?, ?, 'entrada', ?, ?, 'INGRESO', ?)");
			$movement->bind_param("iiiddi", $lotId, $productId, $operationId, $quantity, $cost, $userId);
			$movement->execute();
			$db->commit();
			return $lotId;
		} catch (Throwable $e) {
			$db->rollback();
			throw $e;
		}
	}

	public static function allocateForOperation($operationId, $productId, $quantity, $userId)
	{
		$db = Database::getCon();
		$remaining = (float)$quantity;
		$allocations = [];

		$db->begin_transaction();
		try {
			$stmt = $db->prepare("SELECT id, cantidad_disponible, costo_unitario
				FROM lote WHERE id_prod = ? AND estado = 'disponible'
				AND cantidad_disponible > 0
				AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
				ORDER BY fecha_vencimiento IS NULL, fecha_vencimiento ASC, fech_ing ASC, id ASC
				FOR UPDATE");
			$stmt->bind_param("i", $productId);
			$stmt->execute();
			$result = $stmt->get_result();

			while ($remaining > 0.000001 && ($lot = $result->fetch_assoc())) {
				$take = min($remaining, (float)$lot['cantidad_disponible']);
				$allocations[] = [$lot['id'], $take, $lot['costo_unitario']];
				$remaining -= $take;
			}

			if ($remaining > 0.000001) {
				throw new RuntimeException('Stock insuficiente en lotes disponibles para el producto ' . $productId . '.');
			}

			$update = $db->prepare("UPDATE lote SET cantidad_disponible = cantidad_disponible - ?,
				estado = CASE WHEN cantidad_disponible - ? <= 0 THEN 'agotado' ELSE estado END,
				updated_at = NOW() WHERE id = ?");
			$insertLink = $db->prepare("INSERT INTO operation_lote (operation_id, lote_id, cantidad, costo_unitario) VALUES (?, ?, ?, ?)");
			$insertMove = $db->prepare("INSERT INTO lote_movimiento
				(lote_id, product_id, operation_id, tipo, cantidad, costo_unitario, referencia, user_id)
				VALUES (?, ?, ?, 'salida', ?, ?, 'VENTA', ?)");

			foreach ($allocations as [$lotId, $take, $cost]) {
				$update->bind_param("ddi", $take, $take, $lotId);
				$update->execute();
				$insertLink->bind_param("iidd", $operationId, $lotId, $take, $cost);
				$insertLink->execute();
				$insertMove->bind_param("iiiddi", $lotId, $productId, $operationId, $take, $cost, $userId);
				$insertMove->execute();
			}

			$db->commit();
			return $allocations;
		} catch (Throwable $e) {
			$db->rollback();
			throw $e;
		}
	}

	public static function reverseForOperation($operationId, $userId)
	{
		$db = Database::getCon();
		$db->begin_transaction();
		try {
			$find = $db->prepare("SELECT ol.lote_id, ol.cantidad, ol.costo_unitario, l.id_prod
				FROM operation_lote ol INNER JOIN lote l ON l.id = ol.lote_id
				INNER JOIN operation op ON op.id = ol.operation_id AND op.estado = 1
				WHERE ol.operation_id = ? FOR UPDATE");
			$find->bind_param("i", $operationId);
			$find->execute();
			$result = $find->get_result();
			$restore = $db->prepare("UPDATE lote SET cantidad_disponible = cantidad_disponible + ?,
				estado = CASE WHEN estado = 'agotado' THEN 'disponible' ELSE estado END,
				updated_at = NOW() WHERE id = ?");
			$movement = $db->prepare("INSERT INTO lote_movimiento
				(lote_id, product_id, operation_id, tipo, cantidad, costo_unitario, referencia, user_id)
				VALUES (?, ?, ?, 'devolucion', ?, ?, 'ANULACION', ?)");

			while ($row = $result->fetch_assoc()) {
				$restore->bind_param("di", $row['cantidad'], $row['lote_id']);
				$restore->execute();
				$movement->bind_param("iiiddi", $row['lote_id'], $row['id_prod'], $operationId, $row['cantidad'], $row['costo_unitario'], $userId);
				$movement->execute();
			}
			$db->commit();
		} catch (Throwable $e) {
			$db->rollback();
			throw $e;
		}
	}
}

?>