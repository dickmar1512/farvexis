<?php
/**
 * Action: downloadtemplate
 * Sirve la plantilla XLSX pre-generada para importación de productos.
 * 
 * NOTA: La plantilla se genera vía CLI con generate_template.php
 * porque el PHP del servidor web no tiene ext-zip habilitado.
 * Para regenerar: php generate_template.php
 */

$filePath = dirname(__FILE__, 4) . '/assets/templates/plantilla_productos.xlsx';

if (!file_exists($filePath)) {
  http_response_code(404);
  echo 'Plantilla no encontrada. Ejecute: php generate_template.php';
  exit;
}

$filename = 'plantilla_importacion_' . date('Ymdhis') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: max-age=0');
header('Pragma: public');
readfile($filePath);
exit;
