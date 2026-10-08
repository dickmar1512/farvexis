<?php
/**
 * addproductxls-action.php
 * Importa productos desde un archivo Excel (.xlsx/.xls) vía AJAX.
 * Usa PDO con las credenciales de Database para mantener centralización.
 * Responde JSON.
 */
header('Content-Type: application/json; charset=utf-8');

// ── Helpers ─────────────────────────────────────────────────────────────────
function generarCodigoBarrasXls() {
    $primerDigito = '7';
    $resto = '';
    for ($i = 0; $i < 12; $i++) {
        $resto .= mt_rand(0, 9);
    }
    $codigo = $primerDigito . $resto;
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += ($i % 2 === 0) ? $codigo[$i] * 1 : $codigo[$i] * 3;
    }
    $digitoControl = (10 - ($sum % 10)) % 10;
    return $codigo . $digitoControl;
}

function obtenerCodigoBarrasUnicoXls($conn, $codigoExistente = null) {
    if (!empty($codigoExistente)) {
        $codigoExistente = trim($codigoExistente);
        if (is_numeric($codigoExistente) && stripos($codigoExistente, 'E') !== false) {
            $codigoExistente = sprintf('%.0f', (float)$codigoExistente);
        }
        return $codigoExistente;
    }
    do {
        $nuevoCodigo = generarCodigoBarrasXls();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM product WHERE barcode = ?");
        $stmt->execute([$nuevoCodigo]);
        $existe = $stmt->fetchColumn();
    } while ($existe > 0);
    return $nuevoCodigo;
}

include ROOT . "/assets/plugins/simplexlsx.class.php";

// Usar las mismas credenciales que Database.php (única fuente)
$db_host = "localhost";
$db_name = "dbcarepharm";
$db_user = "milenio";
$db_pass = "armagedon";

try {
    // Validar archivo subido
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'No se ha subido ningún archivo o hubo un error en la carga.']);
        exit;
    }

    $file_tmp_path = $_FILES['image']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, ['xlsx', 'xls'])) {
        echo json_encode(['status' => 'error', 'message' => 'Solo se permiten archivos Excel (.xlsx, .xls)']);
        exit;
    }

    $xlsx = new SimpleXLSX($file_tmp_path);

    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmtSelectProveedor = $conn->prepare("SELECT c.id FROM cliente c JOIN persona p ON c.persona_id = p.id WHERE p.numero_documento = ? AND c.kind = 2");
    $stmtInsertPersona = $conn->prepare("INSERT INTO persona (tipo_documento_id, numero_documento, nombres, apellido_paterno, direccion, email, telefono, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtInsertCliente = $conn->prepare("INSERT INTO cliente (persona_id, tipo_cliente, company, kind) VALUES (?, ?, ?, 2)");

    $stmtSelectProducto = $conn->prepare("SELECT id FROM product WHERE (barcode <> '' AND barcode = ?)");
    $stmtInsertProducto = $conn->prepare("INSERT INTO product (image, barcode, name, description, stock, is_stock, inventary_min, price_in, price_out, price_may, unit, presentation, user_id, category_id, fecha_venc, laboratorio, reg_san, created_at, is_active, cod_digemid, principio_activo, is_controlled, anaquel) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtUpdateProducto = $conn->prepare("UPDATE product SET stock = stock + ?, price_in = ?, price_out = ?, price_may = ?, fecha_venc = ?, user_id = ?, reg_san = ?, cod_digemid = ?, principio_activo = ?, is_controlled = ?, anaquel = ? WHERE id = ?");

    $stmtSelectCompra = $conn->prepare("SELECT id FROM sell WHERE estado = 1 and comprobante = ? AND serie = ?");
    $stmtInsertCompra = $conn->prepare("INSERT INTO sell (person_id, tipo_comprobante, serie, comprobante, fecha_emi, user_id, operation_type_id, created_at, total, cash, discount, observacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtInsertOperacion = $conn->prepare("INSERT INTO operation (product_id, q, prec_alt, descuento, operation_type_id, sell_id, created_at, descripcion, idpaquete) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtInsertLote = $conn->prepare("INSERT INTO lote (id_prod, num_lot, fech_ing, id_sell, user_id, fecha_fabricacion, fecha_vencimiento, cantidad_inicial, cantidad_disponible, costo_unitario, estado, proveedor_id, created_at) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, 'disponible', ?, NOW())");
    $stmtUpdateLote = $conn->prepare("UPDATE lote SET cantidad_inicial = cantidad_inicial + ?, cantidad_disponible = cantidad_disponible + ?, fecha_fabricacion = COALESCE(?, fecha_fabricacion), fecha_vencimiento = COALESCE(?, fecha_vencimiento), costo_unitario = COALESCE(?, costo_unitario), estado = 'disponible', proveedor_id = COALESCE(?, proveedor_id), updated_at = NOW() WHERE id = ?");
    $stmtInsertLoteMovimiento = $conn->prepare("INSERT INTO lote_movimiento (lote_id, product_id, operation_id, tipo, cantidad, costo_unitario, referencia, user_id) VALUES (?, ?, ?, 'entrada', ?, ?, 'INGRESO', ?)");

    $stmtSelectUnidad = $conn->prepare("SELECT id FROM unidad_medida WHERE name = ?");
    $stmtInsertUnidad = $conn->prepare("INSERT INTO unidad_medida (name, sigla) VALUES (?, '-')");

    $stmtSelectOperacion = $conn->prepare("SELECT id, q FROM operation WHERE estado = 1 and product_id = ? AND sell_id = ?");
    $stmtUpdateOperacion = $conn->prepare("UPDATE operation SET q = q + ?, prec_alt = ? WHERE id = ?");

    $stmtSelectLoteExistente = $conn->prepare("SELECT id FROM lote WHERE id_prod = ? AND num_lot = ? AND id_sell = ?");
    $stmtUpdateSellTotal = $conn->prepare("UPDATE sell SET total = (SELECT SUM(q * prec_alt) FROM operation WHERE sell_id = ?), cash = (SELECT SUM(q * prec_alt) FROM operation WHERE sell_id = ?) WHERE id = ?");
    $stmtInsertHistory = $conn->prepare("INSERT INTO price_history (product_id, price_in, price_out, user_id, sell_id, created_at) VALUES (?, ?, ?, ?, ?, ?)");

    $rows = $xlsx->rows();
    array_shift($rows);

    $rows = array_filter($rows, function($fila) {
        return !empty(array_filter($fila, function($valor) {
            return $valor !== '' && $valor !== null;
        }));
    });

    $inserted_rows  = 0;
    $idcompra       = null;
    $processedSells = [];

    $conn->beginTransaction();

    foreach ($rows as $fields) {
        if (isset($fields[0]) && strtolower(trim($fields[0])) === 'cod_digemid') {
            continue;
        }

        $rucproveedor = trim($fields[19] ?? '');
        $proveedor    = trim($fields[18] ?? '');

        if (empty($rucproveedor)) continue;

        $stmtSelectProveedor->execute([$rucproveedor]);
        $proveedorExistente = $stmtSelectProveedor->fetch(PDO::FETCH_ASSOC);

        if ($proveedorExistente) {
            $idproveedor = $proveedorExistente['id'];
        } else {
            $tipo_doc = strlen($rucproveedor) == 11 ? 6 : 1;
            $stmtInsertPersona->execute([$tipo_doc, $rucproveedor, $proveedor, '', '', '', '', date('Y-m-d H:i:s')]);
            $idpersona = $conn->lastInsertId();
            $stmtInsertCliente->execute([$idpersona, 1, '']);
            $idproveedor = $conn->lastInsertId();
        }

        $nombreUnidad = !empty($fields[3]) ? trim($fields[3]) : 'UNIDAD';
        $stmtSelectUnidad->execute([$nombreUnidad]);
        $unidadExistente = $stmtSelectUnidad->fetch(PDO::FETCH_ASSOC);
        if (!$unidadExistente) {
            $stmtInsertUnidad->execute([$nombreUnidad]);
            $idunidad = $conn->lastInsertId();
        } else {
            $idunidad = $unidadExistente['id'];
        }

        $codigoExistente = !empty($fields[17]) ? $fields[17] : null;
        $barcode = obtenerCodigoBarrasUnicoXls($conn, $codigoExistente);

        $productData = [
            'image'           => 'medgen.png',
            'barcode'         => $barcode,
            'cod_digemid'     => (!empty($fields[0]) && is_numeric(trim($fields[0]))) ? (int)trim($fields[0]) : 0,
            'name'            => trim($fields[1]),
            'description'     => '',
            'principio_activo'=> trim($fields[2]),
            'presentation'    => trim($fields[3]),
            'stock'           => !empty($fields[9]) ? (int)$fields[9] : 0,
            'is_stock'        => 1,
            'inventary_min'   => 10,
            'price_in'        => !empty($fields[12]) ? (float)str_replace(['S/', ' ', ','], ['', '', ''], $fields[12]) : 0,
            'price_out'       => !empty($fields[15]) ? (float)str_replace(['S/', ' ', ','], ['', '', ''], $fields[15]) : 0,
            'price_may'       => !empty($fields[16]) ? (float)str_replace(['S/', ' ', ','], ['', '', ''], $fields[16]) : 0,
            'unit'            => $idunidad,
            'user_id'         => $_SESSION['user_id'],
            'category_id'     => 1,
            'fecha_venc'      => !empty($fields[7]) ? $fields[7] : null,
            'laboratorio'     => !empty($fields[4]) ? trim($fields[4]) : '-',
            'reg_san'         => !empty($fields[5]) ? trim($fields[5]) : '-',
            'created_at'      => date('Y-m-d H:i:s'),
            'is_active'       => 1,
            'is_controlled'   => (!empty($fields[22]) && strtoupper(trim($fields[22])) === 'SI') ? 1 : 0,
            'anaquel'         => trim($fields[23] ?? '')
        ];

        $stmtSelectProducto->execute([$barcode]);
        $productoExistente = $stmtSelectProducto->fetch(PDO::FETCH_ASSOC);

        if ($productoExistente) {
            $stmtUpdateProducto->execute([
                $productData['stock'], $productData['price_in'], $productData['price_out'],
                $productData['price_may'], $productData['fecha_venc'], $productData['user_id'],
                $productData['reg_san'], $productData['cod_digemid'], $productData['principio_activo'],
                $productData['is_controlled'], $productData['anaquel'],
                $productoExistente['id']
            ]);
            $idproducto = $productoExistente['id'];
        } else {
            $stmtInsertProducto->execute([
                $productData['image'], $productData['barcode'], $productData['name'],
                $productData['description'], $productData['stock'], $productData['is_stock'],
                $productData['inventary_min'], $productData['price_in'], $productData['price_out'],
                $productData['price_may'], $productData['unit'], $productData['presentation'],
                $productData['user_id'], $productData['category_id'], $productData['fecha_venc'],
                $productData['laboratorio'], $productData['reg_san'], $productData['created_at'],
                $productData['is_active'], $productData['cod_digemid'], $productData['principio_activo'],
                $productData['is_controlled'], $productData['anaquel']
            ]);
            $idproducto = $conn->lastInsertId();
        }

        $comprobante_input = trim($fields[13] ?? '');
        $parts  = explode('-', $comprobante_input);
        $serie  = trim($parts[0] ?? '');
        $numcom = trim($parts[1] ?? '');

        $stmtSelectCompra->execute([$numcom, $serie]);
        $compraExistente = $stmtSelectCompra->fetch(PDO::FETCH_ASSOC);

        if ($compraExistente) {
            $idcompra = $compraExistente['id'];
        } else {
            $fechcompra = trim($fields[21] ?? '');
            $fecha_emi  = date('Y-m-d');
            if (!empty($fechcompra)) {
                $fechaObj = DateTime::createFromFormat('d/m/Y', $fechcompra);
                if ($fechaObj !== false) $fecha_emi = $fechaObj->format('Y-m-d');
            }
            $nro_guia    = trim($fields[14] ?? '');
            $sede        = trim($fields[20] ?? '');
            $observacion = "GUIA: $nro_guia | SEDE: $sede";

            $stmtInsertCompra->execute([
                $idproveedor, 1, $serie, $numcom, $fecha_emi, $_SESSION['user_id'], 1,
                date('Y-m-d H:i:s'), 0, 0, 0, $observacion
            ]);
            $idcompra = $conn->lastInsertId();
        }

        if (!in_array($idcompra, $processedSells)) {
            $processedSells[] = $idcompra;
        }

        $stmtSelectOperacion->execute([$idproducto, $idcompra]);
        $opExistente = $stmtSelectOperacion->fetch(PDO::FETCH_ASSOC);

        if ($opExistente) {
            $stmtUpdateOperacion->execute([$productData['stock'], $productData['price_in'], $opExistente['id']]);
        } else {
            $stmtInsertOperacion->execute([
                $idproducto, $productData['stock'], $productData['price_in'], 0, 1,
                $idcompra, date('Y-m-d H:i:s'), '', ''
            ]);
        }

        $numLote = !empty($fields[10]) ? trim($fields[10]) : 'S/L';
        $stmtSelectLoteExistente->execute([$idproducto, $numLote, $idcompra]);
        $loteExistente = $stmtSelectLoteExistente->fetch(PDO::FETCH_ASSOC);
        $fechaVencimiento = !empty($productData['fecha_venc']) ? $productData['fecha_venc'] : null;
        $fechaFabricacion = !empty($fields[6]) ? date('Y-m-d', strtotime(str_replace('/', '-', $fields[6]))) : null;
        if (!$loteExistente) {
            $stmtInsertLote->execute([
                $idproducto,
                $numLote,
                $idcompra,
                $_SESSION['user_id'],
                $fechaFabricacion,
                $fechaVencimiento,
                $productData['stock'],
                $productData['stock'],
                $productData['price_in'],
                $idproveedor
            ]);
            $currentLoteId = $conn->lastInsertId();
        } else {
            $stmtUpdateLote->execute([
                $productData['stock'],
                $productData['stock'],
                $fechaFabricacion,
                $fechaVencimiento,
                $productData['price_in'],
                $idproveedor,
                $loteExistente['id']
            ]);
            $currentLoteId = $loteExistente['id'];
        }

        // Insertar movimiento de lote (necesita el ID de operación)
        $stmtSelectOperacion->execute([$idproducto, $idcompra]);
        $opFinal = $stmtSelectOperacion->fetch(PDO::FETCH_ASSOC);
        if ($opFinal && $productData['stock'] > 0) {
            $stmtInsertLoteMovimiento->execute([
                $currentLoteId,
                $idproducto,
                $opFinal['id'],
                $productData['stock'],
                $productData['price_in'],
                $_SESSION['user_id']
            ]);
        }

        $stmtInsertHistory->execute([
            $idproducto, $productData['price_in'], $productData['price_out'],
            $_SESSION['user_id'], $idcompra, date('Y-m-d H:i:s')
        ]);

        $inserted_rows++;
    }

    foreach ($processedSells as $sid) {
        $stmtUpdateSellTotal->execute([$sid, $sid, $sid]);
    }

    $conn->commit();

    echo json_encode([
        'status'   => 'success',
        'message'  => "Se importaron {$inserted_rows} filas correctamente.",
        'rows'     => $inserted_rows,
        'redirect' => "./?view=importarexcel&success=1&rows={$inserted_rows}"
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error en la importación: ' . $e->getMessage()
    ]);
}
?>
