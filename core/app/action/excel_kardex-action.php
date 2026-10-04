<?php
/**
 * excel_kardex-action.php
 * Genera y descarga el kardex de un producto en formato Excel.
 * Reemplaza excel_kardex.php de la raíz.
 * Usa Database::getCon() en lugar de conexion.php/funciones.php.
 */

$con          = Database::getCon();
$id_producto  = (int)($_POST['id_producto'] ?? 0);
$selMes       = (int)($_POST['selMes']      ?? date('n'));
$selAnio      = (int)($_POST['selAnio']     ?? date('Y'));

// ── Helpers (antes en funciones.php) ────────────────────────────────────────
function kardex_get_producto($con, $product_id) {
    $result = $con->query("SELECT name, price_out FROM product WHERE id = {$product_id} LIMIT 1");
    return $result ? $result->fetch_assoc() : null;
}

function kardex_inventario_inicial($con, $product_id, $condicion = '') {
    $sql = "SELECT SUM(ope.q) suma, ope.operation_type_id
            FROM operation ope
            WHERE ope.product_id = {$product_id} {$condicion}
            GROUP BY ope.operation_type_id";
    $rows   = [];
    $result = $con->query($sql);
    if ($result) {
        while ($fila = $result->fetch_assoc()) {
            $rows[] = $fila;
        }
    }
    return $rows;
}

function kardex_get_operaciones($con, $product_id, $condicion = '') {
    $sql = "SELECT ope.*, sel.tipo_comprobante, sel.serie, sel.comprobante
            FROM operation ope
            INNER JOIN sell sel ON sel.id = ope.sell_id
            WHERE ope.product_id = {$product_id} {$condicion}";
    $rows   = [];
    $result = $con->query($sql);
    if ($result) {
        while ($fila = $result->fetch_assoc()) {
            $rows[] = $fila;
        }
    }
    return $rows;
}

function kardex_convertir_fecha($fecha) {
    if ($fecha === '0000-00-00') return 'Sin fecha';
    $date = date_create($fecha);
    return date_format($date, 'd-m-Y');
}
// ────────────────────────────────────────────────────────────────────────────

$producto = kardex_get_producto($con, $id_producto);

require_once ROOT . '/assets/plugins/PHPExcel/PHPExcel.php';

$excel = new PHPExcel();
$excel->getProperties()->setCreator('TARO')->setLastModifiedBy('Admin')->setTitle('kardex');
$excel->setActiveSheetIndex(0);

$pagina = $excel->getActiveSheet();
$pagina->setTitle('REGISTRO DE INVENTARIO');

$pagina->setCellValue('D1', 'FORMATO 13.1: REGISTRO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO VALORIZADO');
$pagina->mergeCells('D1:L1');

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$pagina->setCellValue('A3', 'PERIODO:');
$pagina->setCellValue('B3', $meses[$selMes] . '-' . $selAnio);

$pagina->setCellValue('A4', 'RUC:');
$pagina->setCellValue('B4', EmpresaData::getDatos()->Emp_Ruc ?? '');

$pagina->setCellValue('A5', 'NOMBRE COMERCIAL:');
$pagina->setCellValue('B5', EmpresaData::getDatos()->Emp_RazonSocial ?? '');

$pagina->setCellValue('A6', 'ESTABLECIMIENTO:');
$pagina->setCellValue('B6', '');

$pagina->setCellValue('A7', 'TIPO:');
$pagina->setCellValue('B7', '01 MERCADERÍA');

$pagina->setCellValue('A8', 'DESCRIPCIÓN:');
$pagina->setCellValue('B8', $producto['name'] ?? '');

$pagina->setCellValue('A9', 'METODO DE VALUACIÓN:');
$pagina->setCellValue('B9', 'Promedio');

// Encabezados de tabla
$pagina->setCellValue('A13', 'DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR');
$pagina->mergeCells('A13:D13');
$pagina->setCellValue('A14', 'FECHA');
$pagina->setCellValue('B14', 'TIPO');
$pagina->setCellValue('C14', 'SERIE');
$pagina->setCellValue('D14', 'NÚMERO');
$pagina->setCellValue('E13', 'TIPO DE OPERACIÓN');
$pagina->mergeCells('E13:E14');
$pagina->setCellValue('F13', 'ENTRADAS');
$pagina->mergeCells('F13:H13');
$pagina->setCellValue('F14', 'CANTIDAD');
$pagina->setCellValue('G14', 'COSTO UNITARIO');
$pagina->setCellValue('H14', 'COSTO TOTAL');
$pagina->setCellValue('I13', 'SALIDAS');
$pagina->mergeCells('I13:K13');
$pagina->setCellValue('I14', 'CANTIDAD');
$pagina->setCellValue('J14', 'COSTO UNITARIO');
$pagina->setCellValue('K14', 'COSTO TOTAL');
$pagina->setCellValue('L13', 'SALDO FINAL');
$pagina->mergeCells('L13:N13');
$pagina->setCellValue('L14', 'CANTIDAD');
$pagina->setCellValue('M14', 'COSTO UNITARIO');
$pagina->setCellValue('N14', 'COSTO TOTAL');

// Inventario inicial
$condicion_inv = "AND MONTH(ope.created_at) = {$selMes} AND YEAR(ope.created_at) = {$selAnio}";
$inventario_inicial = kardex_inventario_inicial($con, $id_producto, $condicion_inv);

$cantidad_inv_inicial = ($inventario_inicial[0]['suma'] ?? 0) - ($inventario_inicial[1]['suma'] ?? 0);

$excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11, 15, $cantidad_inv_inicial);
$excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12, 15, $producto['price_out'] ?? 0);
$excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13, 15, ($producto['price_out'] ?? 0) * $cantidad_inv_inicial);

// Operaciones del período
$condicion_op = "AND MONTH(ope.created_at) = {$selMes} AND YEAR(ope.created_at) = {$selAnio}";
$operaciones  = kardex_get_operaciones($con, $id_producto, $condicion_op);

$fila        = 16;
$cantidad_sf = $cantidad_inv_inicial;

foreach ($operaciones as $ope) {
    $cantidad       = $ope['q'];
    $precio_unitario = $ope['prec_alt'];
    $precio_total   = $cantidad * $precio_unitario;

    $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, $fila, kardex_convertir_fecha($ope['created_at']));
    $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, $fila, '0' . $ope['tipo_comprobante']);
    $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2, $fila, $ope['serie']);
    $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, $fila, $ope['comprobante']);

    if ($ope['operation_type_id'] == 1) {
        $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, $fila, '02');
        $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, $fila, $cantidad);
        $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, $fila, $precio_unitario);
        $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7, $fila, $precio_total);
        $cantidad_sf += $cantidad;
    } elseif ($ope['operation_type_id'] == 2) {
        $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, $fila, '01');
        $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8, $fila, $cantidad);
        $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9, $fila, $precio_unitario);
        $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10, $fila, $precio_total);
        $cantidad_sf -= $cantidad;
    }

    $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11, $fila, $cantidad_sf);
    $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12, $fila, $precio_unitario);
    $excel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13, $fila, $cantidad_sf * $precio_unitario);

    $fila++;
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="kardex_' . $id_producto . '_' . $selMes . '_' . $selAnio . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$objWriter->save('php://output');
exit;
?>
