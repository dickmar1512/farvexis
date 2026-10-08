<?php
/**
 * CRON: Reporte de Productos Vencidos y Por Vencer
 * -------------------------------------------------------
 * Lógica de vencimiento idéntica a searchproduct-action.php:
 *   - Vencido   : días_restantes <= 0
 *   - Crítico   : 1 – 90 días
 *   - Próximo   : 91 – 120 días
 *   - Vigente   : > 120 días
 *
 * Programación cron sugerida (Linux/cPanel):
 *   0 8 15 * *         php /ruta/al/proyecto/cron_reporte_vencimientos.php
 *   0 8 28-31 * *      php /ruta/al/proyecto/cron_reporte_vencimientos.php
 *
 * El script detecta si hoy es el último día del mes y se detiene
 * si no corresponde (días 28–31 que no sean el último día real).
 *
 * En Windows (Programador de tareas) apuntar a este archivo.
 */

define("ROOT", dirname(__DIR__));
date_default_timezone_set("America/Lima");

// ── Verificar que hoy es día 15 O último día del mes ─────────────────────────
$hoy        = (int) date('j');               // día del mes (sin cero inicial)
$ultimo_dia = (int) date('t');               // total de días del mes actual

$es_dia_15     = ($hoy === 15);
$es_ultimo_dia = ($hoy === $ultimo_dia);

if (!$es_dia_15 && !$es_ultimo_dia) {
    echo "Hoy (" . date('d/m/Y') . ") no corresponde ejecutar el reporte. Se requiere día 15 o último día del mes.\n";
    exit(0);
}

// ── Dependencias del proyecto ────────────────────────────────────────────────
include_once ROOT . "/core/autoload.php";
include_once ROOT . "/core/app/model/ProductData.php";
include_once ROOT . "/core/app/model/OperationData.php";
include_once ROOT . "/core/app/model/SellData.php";
include_once ROOT . "/core/app/model/UserData.php";
include_once ROOT . "/core/app/model/CLSPHPMailer.php";
require_once ROOT . "/assets/plugins/fpdf/fpdf.php";

Core::$root = "";

// Asegurar carpeta de temporales
if (!file_exists(ROOT . "/storage")) {
    mkdir(ROOT . "/storage", 0777, true);
}

// ── Función auxiliar para interpretar fechas de vencimiento (soporta YYYY-MM-DD, DD/MM/YYYY y series Excel)
function parseFechaVenc($raw) {
    $raw = trim((string)$raw);
    if (empty($raw)) return false;
    if (is_numeric($raw) && $raw > 40000 && $raw < 60000) {
        return (int) (($raw - 25569) * 86400);
    }
    $clean = str_replace('/', '-', $raw);
    return strtotime($clean);
}

// ── Función: calcula estado de vencimiento (igual que searchproduct-action) ──
function calcularEstadoVenc($fecha_venc_raw, $created_at_raw = null) {
    if (empty($fecha_venc_raw)) {
        return [
            'dias'           => PHP_INT_MAX,
            'estado'         => 'sin_fecha',
            'fecha_fmt'      => '—',
            'dias_txt'       => '—',
        ];
    }

    $ts_venc    = parseFechaVenc($fecha_venc_raw);
    $ts_created = !empty($created_at_raw) ? strtotime(str_replace('/', '-', trim((string)$created_at_raw))) : false;

    // Si fecha_venc es menor que created_at o es fecha dummy/inválida (ej: 1970-01-01, 0000-00-00)
    if ($ts_venc === false || $ts_venc <= 0 || ($ts_created !== false && date('Y-m-d', $ts_venc) < date('Y-m-d', $ts_created))) {
        return [
            'dias'      => PHP_INT_MAX,
            'estado'    => 'sin_vencimiento',
            'fecha_fmt' => 'SIN VENCIMIENTO',
            'dias_txt'  => '—',
        ];
    }

    $dias      = (int) ceil(($ts_venc - time()) / 86400);
    $fecha_fmt = date('d/m/Y', $ts_venc);

    if ($dias <= 0) {
        $estado  = 'vencido';
        $dias_txt = 'Vencido hace ' . abs($dias) . ' días';
    } elseif ($dias <= 90) {
        $estado  = 'critico';
        $dias_txt = 'Faltan ' . $dias . ' días';
    } elseif ($dias <= 120) {
        $estado  = 'proximo';
        $dias_txt = 'Faltan ' . $dias . ' días';
    } else {
        $estado  = 'vigente';
        $dias_txt = 'Faltan ' . $dias . ' días';
    }

    return compact('dias', 'estado', 'fecha_fmt', 'dias_txt');
}

// ── Cargar y clasificar todos los productos activos ──────────────────────────
$todos_productos = ProductData::getAll2();

$vencidos        = [];
$criticos        = [];
$proximos        = [];
$sin_vencimiento = [];

foreach ($todos_productos as $p) {
    $info = calcularEstadoVenc($p->fecha_venc, $p->created_at);
    $p->_v = $info;
    switch ($info['estado']) {
        case 'vencido':         $vencidos[]        = $p; break;
        case 'critico':         $criticos[]        = $p; break;
        case 'proximo':         $proximos[]        = $p; break;
        case 'sin_vencimiento': $sin_vencimiento[] = $p; break;
    }
}

// Ordenar cada grupo por días restantes (más urgente primero)
$ordenar = function($a, $b) {
    $da = ($a->_v['dias'] === PHP_INT_MAX) ? 99999 : $a->_v['dias'];
    $db = ($b->_v['dias'] === PHP_INT_MAX) ? 99999 : $b->_v['dias'];
    return $da <=> $db;
};
usort($vencidos, $ordenar);
usort($criticos, $ordenar);
usort($proximos, $ordenar);
usort($sin_vencimiento, function($a, $b) {
    return strcmp($a->name ?? '', $b->name ?? '');
});

$total_alertas = count($vencidos) + count($criticos) + count($proximos);

echo "=== CRON VENCIMIENTOS [" . date('d/m/Y H:i') . "] ===\n";
echo "Vencidos: " . count($vencidos) . " | Críticos: " . count($criticos) . " | Próximos: " . count($proximos) . " | Sin Vencimiento: " . count($sin_vencimiento) . "\n";
echo "Total con alerta: $total_alertas\n";

// ── Generar PDF con FPDF ──────────────────────────────────────────────────────
$pdf = new FPDF();
$pdf->SetMargins(12, 12, 12);

// ---- Portada / resumen ----
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetFillColor(30, 60, 114);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 14, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE',"REPORTE DE VENCIMIENTOS DE PRODUCTOS"), 0, 1, 'C', true);

$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 8, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE',"Generado el: " . date('d/m/Y H:i')), 0, 1, 'C');
$pdf->Ln(4);

// Resumen estadístico
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 9, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE',"Resumen de Alertas y Vencimientos"), 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

// Fila 1: Vencidos y Críticos (68mm + 25mm = 93mm cada bloque, total 186mm)
// Caja Vencidos
$pdf->SetFillColor(220, 53, 69);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(68, 9, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE',"Vencidos"), 1, 0, 'C', true);
$pdf->SetFillColor(253, 232, 232);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(25, 9, (string)count($vencidos), 1, 0, 'C', true);

// Caja Críticos
$pdf->SetFillColor(220, 53, 69);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(68, 9, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE',"Críticos (<=90 días)"), 1, 0, 'C', true);
$pdf->SetFillColor(253, 232, 232);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(25, 9, (string)count($criticos), 1, 1, 'C', true);

// Fila 2: Próximos y Sin Vencimiento
// Caja Próximos
$pdf->SetFillColor(217, 164, 6);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(68, 9, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE',"Próximos (91–120 días)"), 1, 0, 'C', true);
$pdf->SetFillColor(254, 249, 231);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(25, 9, (string)count($proximos), 1, 0, 'C', true);

// Caja Sin Vencimiento
$pdf->SetFillColor(108, 117, 125);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(68, 9, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE',"Sin Vencimiento"), 1, 0, 'C', true);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(25, 9, (string)count($sin_vencimiento), 1, 1, 'C', true);

$pdf->Ln(2);

// Contador total
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(52, 58, 64);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 9, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE',"TOTAL CON ALERTA: $total_alertas productos"), 1, 1, 'C', true);
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);

// ── Función auxiliar para partir texto en hasta 2 líneas sin solaparse entre columnas ──
function splitTextToLines(FPDF $pdf, $maxW, $text, $maxLines = 2) {
    $text = trim((string)$text);
    if ($text === '') return [''];
    if ($pdf->GetStringWidth($text) <= $maxW) {
        return [$text];
    }
    $words = explode(' ', $text);
    $lines = [];
    $cur = '';
    $idx = 0;
    while ($idx < count($words)) {
        $w = $words[$idx];
        $test = ($cur === '') ? $w : $cur . ' ' . $w;
        if ($pdf->GetStringWidth($test) <= $maxW) {
            $cur = $test;
            $idx++;
        } else {
            if ($cur !== '') {
                $lines[] = $cur;
                $cur = '';
                if (count($lines) === $maxLines - 1) {
                    $last = implode(' ', array_slice($words, $idx));
                    if ($pdf->GetStringWidth($last) <= $maxW) {
                        $lines[] = $last;
                    } else {
                        while ($pdf->GetStringWidth($last . '…') > $maxW && mb_strlen($last) > 0) {
                            $last = mb_substr($last, 0, -1);
                        }
                        $lines[] = $last . '…';
                    }
                    return $lines;
                }
            } else {
                if ($pdf->GetStringWidth($w) <= $maxW) {
                    $lines[] = $w;
                } else {
                    while ($pdf->GetStringWidth($w . '…') > $maxW && mb_strlen($w) > 0) {
                        $w = mb_substr($w, 0, -1);
                    }
                    $lines[] = $w . '…';
                }
                $idx++;
                if (count($lines) === $maxLines) {
                    return $lines;
                }
            }
        }
    }
    if ($cur !== '') {
        $lines[] = $cur;
    }
    return array_slice($lines, 0, $maxLines);
}

// ---- Función auxiliar para tabla de grupo en PDF (con soporte de 2 líneas sin solape) ----
function pdfTablaGrupo(FPDF &$pdf, array $lista, string $titulo, array $fill_rgb) {
    if (empty($lista)) {
        return;
    }
    if ($pdf->GetY() > 220) {
        $pdf->AddPage();
    }
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor($fill_rgb[0], $fill_rgb[1], $fill_rgb[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 9, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE', $titulo . " (" . count($lista) . " productos)"), 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    $printHeader = function() use (&$pdf) {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(52, 58, 64);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(8,  8, '#',              1, 0, 'C', true);
        $pdf->Cell(60, 8, 'PRODUCTO',       1, 0, 'L', true);
        $pdf->Cell(38, 8, 'LABORATORIO',    1, 0, 'L', true);
        $pdf->Cell(18, 8, 'STOCK',          1, 0, 'C', true);
        $pdf->Cell(32, 8, 'F.VENCIMIENTO',  1, 0, 'C', true);
        $pdf->Cell(30, 8, iconv('UTF-8','windows-1252//TRANSLIT//IGNORE','DÍAS RESTANTES'), 1, 1, 'C', true);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetTextColor(0, 0, 0);
    };

    $printHeader();

    $i = 1;
    foreach ($lista as $p) {
        $pdf->SetFont('Arial', '', 8.5);
        $nombre = iconv('UTF-8','windows-1252//TRANSLIT//IGNORE', $p->name ?? '');
        $lab    = iconv('UTF-8','windows-1252//TRANSLIT//IGNORE', $p->laboratorio ?? '—');
        $stock  = ($p->is_stock == 0) ? 'Ilimitado' : (string)(int)$p->stock;
        $fv     = iconv('UTF-8','windows-1252//TRANSLIT//IGNORE', $p->_v['fecha_fmt']);
        $dt     = iconv('UTF-8','windows-1252//TRANSLIT//IGNORE', $p->_v['dias_txt']);

        // Calcular líneas de producto y laboratorio (ancho disponible: 58mm y 36mm)
        $linesNom = splitTextToLines($pdf, 58, $nombre, 2);
        $linesLab = splitTextToLines($pdf, 36, $lab, 2);
        $numLines = max(count($linesNom), count($linesLab));
        $h = ($numLines > 1) ? 9.2 : 6.5;

        // Salto de página
        if ($pdf->GetY() + $h > 275) {
            $pdf->AddPage();
            $printHeader();
        }

        $bg = ($i % 2 === 0);
        if ($bg) {
            $pdf->SetFillColor(245, 245, 245);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        // Dibujar rectángulos de borde y fondo
        $pdf->Rect($x,       $y, 8,  $h, $bg ? 'FD' : 'D');
        $pdf->Rect($x + 8,   $y, 60, $h, $bg ? 'FD' : 'D');
        $pdf->Rect($x + 68,  $y, 38, $h, $bg ? 'FD' : 'D');
        $pdf->Rect($x + 106, $y, 18, $h, $bg ? 'FD' : 'D');
        $pdf->Rect($x + 124, $y, 32, $h, $bg ? 'FD' : 'D');
        $pdf->Rect($x + 156, $y, 30, $h, $bg ? 'FD' : 'D');

        // Col #
        $pdf->SetXY($x, $y + ($h - 4) / 2);
        $pdf->Cell(8, 4, (string)$i, 0, 0, 'C');

        // Col PRODUCTO (1 o 2 líneas)
        if (count($linesNom) > 1) {
            $pdf->SetXY($x + 8 + 1, $y + 0.8);
            $pdf->Cell(58, 3.8, $linesNom[0], 0, 0, 'L');
            $pdf->SetXY($x + 8 + 1, $y + 4.6);
            $pdf->Cell(58, 3.8, $linesNom[1], 0, 0, 'L');
        } else {
            $pdf->SetXY($x + 8 + 1, $y + ($h - 4) / 2);
            $pdf->Cell(58, 4, $linesNom[0], 0, 0, 'L');
        }

        // Col LABORATORIO (1 o 2 líneas)
        if (count($linesLab) > 1) {
            $pdf->SetXY($x + 68 + 1, $y + 0.8);
            $pdf->Cell(36, 3.8, $linesLab[0], 0, 0, 'L');
            $pdf->SetXY($x + 68 + 1, $y + 4.6);
            $pdf->Cell(36, 3.8, $linesLab[1], 0, 0, 'L');
        } else {
            $pdf->SetXY($x + 68 + 1, $y + ($h - 4) / 2);
            $pdf->Cell(36, 4, $linesLab[0], 0, 0, 'L');
        }

        // Col STOCK
        $pdf->SetXY($x + 106, $y + ($h - 4) / 2);
        $pdf->Cell(18, 4, $stock, 0, 0, 'C');

        // Col F.VENCIMIENTO
        if ($p->_v['fecha_fmt'] === 'SIN VENCIMIENTO') {
            $pdf->SetFont('Arial', 'B', 7.5);
            $pdf->SetXY($x + 124, $y + ($h - 4) / 2);
            $pdf->Cell(32, 4, $fv, 0, 0, 'C');
            $pdf->SetFont('Arial', '', 8.5);
        } else {
            $pdf->SetXY($x + 124, $y + ($h - 4) / 2);
            $pdf->Cell(32, 4, $fv, 0, 0, 'C');
        }

        // Col DÍAS RESTANTES
        $pdf->SetXY($x + 156, $y + ($h - 4) / 2);
        $pdf->Cell(30, 4, $dt, 0, 0, 'C');

        $pdf->SetXY($x, $y + $h);
        $i++;
    }
    $pdf->Ln(5);
}

// ---- Sección Vencidos ----
if (!empty($vencidos)) {
    pdfTablaGrupo($pdf, $vencidos, "PRODUCTOS VENCIDOS", [180, 30, 45]);
}

// ---- Sección Críticos ----
if (!empty($criticos)) {
    if ($pdf->GetY() > 220) { $pdf->AddPage(); }
    pdfTablaGrupo($pdf, $criticos, "CRÍTICOS — Vencen en ≤ 90 días", [200, 60, 40]);
}

// ---- Sección Próximos ----
if (!empty($proximos)) {
    if ($pdf->GetY() > 220) { $pdf->AddPage(); }
    pdfTablaGrupo($pdf, $proximos, "PRÓXIMOS — Vencen en 91–120 días", [180, 130, 10]);
}

// ---- Sección Sin Vencimiento ----
if (!empty($sin_vencimiento)) {
    if ($pdf->GetY() > 220) { $pdf->AddPage(); }
    pdfTablaGrupo($pdf, $sin_vencimiento, "PRODUCTOS SIN VENCIMIENTO", [108, 117, 125]);
}

$pdfPath = ROOT . "/storage/Reporte_Vencimientos_" . date('Y-m-d') . ".pdf";
$pdf->Output('F', $pdfPath);
echo "PDF generado: $pdfPath\n";

// ── Generar Excel con estilos idénticos al PDF ────────────────────────────────
function generarExcelReporte(array $vencidos, array $criticos, array $proximos, array $sin_vencimiento, string $tipo_ejecucion, string $destFile) {
    $strings = [];
    $ssIdx = function($val) use (&$strings) {
        $val = (string)$val;
        $k = array_search($val, $strings, true);
        if ($k === false) {
            $strings[] = $val;
            return count($strings) - 1;
        }
        return $k;
    };

    $colDefs = '<cols>
        <col min="1" max="1" width="7" customWidth="1"/>
        <col min="2" max="2" width="46" customWidth="1"/>
        <col min="3" max="3" width="30" customWidth="1"/>
        <col min="4" max="4" width="12" customWidth="1"/>
        <col min="5" max="5" width="20" customWidth="1"/>
        <col min="6" max="6" width="25" customWidth="1"/>
    </cols>';

    $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
      <fonts count="10">
        <font><sz val="9"/><name val="Calibri"/></font>
        <font><b/><sz val="14"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
        <font><b/><sz val="9.5"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
        <font><b/><sz val="10"/><color rgb="FF000000"/><name val="Calibri"/></font>
        <font><i/><sz val="9"/><color rgb="FF555555"/><name val="Calibri"/></font>
        <font><sz val="9"/><color rgb="FF000000"/><name val="Calibri"/></font>
        <font><b/><sz val="9"/><color rgb="FF000000"/><name val="Calibri"/></font>
        <font><b/><sz val="9"/><color rgb="FFC0392B"/><name val="Calibri"/></font>
        <font><b/><sz val="9"/><color rgb="FF6C757D"/><name val="Calibri"/></font>
      </fonts>
      <fills count="14">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FF1E3C72"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FF343A40"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFDC3545"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFC0392B"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFD9A406"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FF6C757D"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFFDE8E8"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFFDE8E8"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFFFF3CD"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFE2E3E5"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFF9F9F9"/></patternFill></fill>
      </fills>
      <borders count="2">
        <border><left/><right/><top/><bottom/><diagonal/></border>
        <border>
          <left style="thin"><color rgb="FFCCCCCC"/></left>
          <right style="thin"><color rgb="FFCCCCCC"/></right>
          <top style="thin"><color rgb="FFCCCCCC"/></top>
          <bottom style="thin"><color rgb="FFCCCCCC"/></bottom>
          <diagonal/>
        </border>
      </borders>
      <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
      <cellXfs count="28">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="4" fillId="8" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="3" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="4" fillId="9" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="3" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="4" fillId="10" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="3" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="4" fillId="11" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="2" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
        <xf numFmtId="0" fontId="2" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
        <xf numFmtId="0" fontId="2" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
        <xf numFmtId="0" fontId="2" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
        <xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
        <xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="6" fillId="12" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="6" fillId="12" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
        <xf numFmtId="0" fontId="7" fillId="12" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>
        <xf numFmtId="0" fontId="8" fillId="12" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="9" fillId="12" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="6" fillId="13" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="6" fillId="13" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
        <xf numFmtId="0" fontId="7" fillId="13" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>
        <xf numFmtId="0" fontId="8" fillId="13" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="9" fillId="13" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
      </cellXfs>
    </styleSheet>';

    $rowsXml = '';
    $r = 1;
    $merges = [];

    // Fila 1: Título
    $rowsXml .= '<row r="'.$r.'" ht="26" customHeight="1">';
    $rowsXml .= '<c r="A'.$r.'" t="s" s="1"><v>'.$ssIdx('REPORTE DE VENCIMIENTOS DE PRODUCTOS').'</v></c>';
    foreach (['B','C','D','E','F'] as $c) { $rowsXml .= '<c r="'.$c.$r.'" s="1"/>'; }
    $rowsXml .= '</row>';
    $merges[] = 'A'.$r.':F'.$r;
    $r++;

    // Fila 2: Subtítulo
    $rowsXml .= '<row r="'.$r.'" ht="18" customHeight="1">';
    $rowsXml .= '<c r="A'.$r.'" t="s" s="2"><v>'.$ssIdx('Generado el: ' . date('d/m/Y H:i') . '  |  ' . $tipo_ejecucion).'</v></c>';
    foreach (['B','C','D','E','F'] as $c) { $rowsXml .= '<c r="'.$c.$r.'" s="2"/>'; }
    $rowsXml .= '</row>';
    $merges[] = 'A'.$r.':F'.$r;
    $r++;

    $r++; // Espacio

    // Cajas de resumen estadístico
    $rowsXml .= '<row r="'.$r.'" ht="20" customHeight="1">';
    $rowsXml .= '<c r="A'.$r.'" t="s" s="3"><v>'.$ssIdx('VENCIDOS').'</v></c>';
    $rowsXml .= '<c r="B'.$r.'" s="4"><v>'.count($vencidos).'</v></c>';
    $rowsXml .= '<c r="C'.$r.'" s="0"/>';
    $rowsXml .= '<c r="D'.$r.'" t="s" s="5"><v>'.$ssIdx('CRÍTICOS (≤90d)').'</v></c>';
    $rowsXml .= '<c r="E'.$r.'" s="6"><v>'.count($criticos).'</v></c>';
    $rowsXml .= '<c r="F'.$r.'" s="0"/>';
    $rowsXml .= '</row>';
    $r++;

    $rowsXml .= '<row r="'.$r.'" ht="20" customHeight="1">';
    $rowsXml .= '<c r="A'.$r.'" t="s" s="7"><v>'.$ssIdx('PRÓXIMOS (91-120d)').'</v></c>';
    $rowsXml .= '<c r="B'.$r.'" s="8"><v>'.count($proximos).'</v></c>';
    $rowsXml .= '<c r="C'.$r.'" s="0"/>';
    $rowsXml .= '<c r="D'.$r.'" t="s" s="9"><v>'.$ssIdx('SIN VENCIMIENTO').'</v></c>';
    $rowsXml .= '<c r="E'.$r.'" s="10"><v>'.count($sin_vencimiento).'</v></c>';
    $rowsXml .= '<c r="F'.$r.'" s="0"/>';
    $rowsXml .= '</row>';
    $r++;

    $total_alertas = count($vencidos) + count($criticos) + count($proximos);
    $rowsXml .= '<row r="'.$r.'" ht="22" customHeight="1">';
    $rowsXml .= '<c r="A'.$r.'" t="s" s="11"><v>'.$ssIdx("TOTAL CON ALERTA: {$total_alertas} productos").'</v></c>';
    foreach (['B','C','D','E','F'] as $c) { $rowsXml .= '<c r="'.$c.$r.'" s="11"/>'; }
    $rowsXml .= '</row>';
    $merges[] = 'A'.$r.':F'.$r;
    $r++;

    $r++; // Espacio

    $renderExcelGroup = function(array $lista, string $titulo, int $bannerStyle) use (&$rowsXml, &$r, &$merges, $ssIdx) {
        if (empty($lista)) return;

        // Fila Banner de Grupo
        $rowsXml .= '<row r="'.$r.'" ht="24" customHeight="1">';
        $rowsXml .= '<c r="A'.$r.'" t="s" s="'.$bannerStyle.'"><v>'.$ssIdx($titulo . ' (' . count($lista) . ' productos)').'</v></c>';
        foreach (['B','C','D','E','F'] as $c) { $rowsXml .= '<c r="'.$c.$r.'" s="'.$bannerStyle.'"/>'; }
        $rowsXml .= '</row>';
        $merges[] = 'A'.$r.':F'.$r;
        $r++;

        // Encabezados de Columna
        $rowsXml .= '<row r="'.$r.'" ht="20" customHeight="1">';
        $rowsXml .= '<c r="A'.$r.'" t="s" s="17"><v>'.$ssIdx('#').'</v></c>';
        $rowsXml .= '<c r="B'.$r.'" t="s" s="16"><v>'.$ssIdx('PRODUCTO').'</v></c>';
        $rowsXml .= '<c r="C'.$r.'" t="s" s="16"><v>'.$ssIdx('LABORATORIO').'</v></c>';
        $rowsXml .= '<c r="D'.$r.'" t="s" s="17"><v>'.$ssIdx('STOCK').'</v></c>';
        $rowsXml .= '<c r="E'.$r.'" t="s" s="17"><v>'.$ssIdx('F. VENCIMIENTO').'</v></c>';
        $rowsXml .= '<c r="F'.$r.'" t="s" s="17"><v>'.$ssIdx('DÍAS RESTANTES').'</v></c>';
        $rowsXml .= '</row>';
        $r++;

        $i = 1;
        foreach ($lista as $p) {
            $v   = $p->_v;
            $alt = ($i % 2 === 0);

            $sCenter = $alt ? 23 : 18;
            $sLeft   = $alt ? 24 : 19;
            $sBold   = $alt ? 25 : 20;
            $sDt     = ($v['estado'] === 'sin_vencimiento') ? ($alt ? 27 : 22) : ($alt ? 26 : 21);

            $stock = ($p->is_stock == 0) ? '∞' : (string)(int)$p->stock;

            $rowsXml .= '<row r="'.$r.'" ht="20" customHeight="1">';
            $rowsXml .= '<c r="A'.$r.'" s="'.$sCenter.'"><v>'.$i.'</v></c>';
            $rowsXml .= '<c r="B'.$r.'" t="s" s="'.$sBold.'"><v>'.$ssIdx($p->name ?? '').'</v></c>';
            $rowsXml .= '<c r="C'.$r.'" t="s" s="'.$sLeft.'"><v>'.$ssIdx($p->laboratorio ?? '—').'</v></c>';
            $rowsXml .= '<c r="D'.$r.'" t="s" s="'.$sCenter.'"><v>'.$ssIdx($stock).'</v></c>';
            $rowsXml .= '<c r="E'.$r.'" t="s" s="'.$sCenter.'"><v>'.$ssIdx($v['fecha_fmt']).'</v></c>';
            $rowsXml .= '<c r="F'.$r.'" t="s" s="'.$sDt.'"><v>'.$ssIdx($v['dias_txt']).'</v></c>';
            $rowsXml .= '</row>';
            $r++;
            $i++;
        }
        $r++;
    };

    $renderExcelGroup($vencidos,        'PRODUCTOS VENCIDOS',             12);
    $renderExcelGroup($criticos,        'CRÍTICOS — Vencen en ≤ 90 días', 13);
    $renderExcelGroup($proximos,        'PRÓXIMOS — Vencen en 91–120 días', 14);
    $renderExcelGroup($sin_vencimiento, 'PRODUCTOS SIN VENCIMIENTO',       15);

    $mergeXml = '';
    if (!empty($merges)) {
        $mergeXml = '<mergeCells count="'.count($merges).'">';
        foreach ($merges as $m) {
            $mergeXml .= '<mergeCell ref="'.$m.'"/>';
        }
        $mergeXml .= '</mergeCells>';
    }

    $worksheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
               xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
      <sheetViews>
        <sheetView tabSelected="1" workbookViewId="0">
          <pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>
        </sheetView>
      </sheetViews>
      <sheetFormatPr defaultRowHeight="16" customHeight="1"/>
      '.$colDefs.'
      <sheetData>'.$rowsXml.'</sheetData>
      '.$mergeXml.'
      <pageSetup orientation="landscape" paperSize="9"/>
    </worksheet>';

    $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
         count="'.count($strings).'" uniqueCount="'.count($strings).'">';
    foreach ($strings as $s) {
        $ssXml .= '<si><t xml:space="preserve">'.htmlspecialchars($s, ENT_XML1, 'UTF-8').'</t></si>';
    }
    $ssXml .= '</sst>';

    $zip = new ZipArchive();
    $tmpFile = tempnam(sys_get_temp_dir(), 'repvenc_');
    $zip->open($tmpFile, ZipArchive::OVERWRITE);

    $zip->addFromString('[Content_Types].xml',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
      <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
      <Default Extension="xml"  ContentType="application/xml"/>
      <Override PartName="/xl/workbook.xml"          ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
      <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
      <Override PartName="/xl/sharedStrings.xml"     ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
      <Override PartName="/xl/styles.xml"            ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    </Types>');

    $zip->addFromString('_rels/.rels',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
      <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    </Relationships>');

    $zip->addFromString('xl/_rels/workbook.xml.rels',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
      <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"     Target="worksheets/sheet1.xml"/>
      <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
      <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"        Target="styles.xml"/>
    </Relationships>');

    $zip->addFromString('xl/workbook.xml',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
              xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
      <bookViews><workbookView activeTab="0"/></bookViews>
      <sheets>
        <sheet name="Reporte Vencimientos" sheetId="1" r:id="rId1"/>
      </sheets>
    </workbook>');

    $zip->addFromString('xl/styles.xml', $stylesXml);
    $zip->addFromString('xl/sharedStrings.xml', $ssXml);
    $zip->addFromString('xl/worksheets/sheet1.xml', $worksheetXml);
    $zip->close();

    copy($tmpFile, $destFile);
    unlink($tmpFile);
    return file_exists($destFile);
}

$xlsxPath = ROOT . "/storage/Reporte_Vencimientos_" . date('Y-m-d') . ".xlsx";
generarExcelReporte($vencidos, $criticos, $proximos, $sin_vencimiento, $tipo_ejecucion ?? 'Quincena (día 15)', $xlsxPath);
echo "Excel generado: $xlsxPath\n";

// ── Generar HTML del correo (optimizado para evitar recorte por tamaño en Gmail) ──
$tipo_ejecucion = $es_dia_15 ? 'Quincena (día 15)' : 'Cierre de Mes (último día)';

// Función para construir tabla HTML de un grupo con marcado compacto
function htmlTablaGrupo(array $lista, string $titulo, string $color_cabecera) {
    if (empty($lista)) return '';

    $html  = "<h3 style='color:{$color_cabecera}; margin: 20px 0 8px;'>{$titulo} (" . count($lista) . " productos)</h3>";
    $html .= "<table class='rpt-tbl' style='width:100%; border-collapse:collapse; font-family:Arial,sans-serif; font-size:12px; margin-bottom:12px;'>";
    $html .= "<thead>
        <tr style='background:#343a40; color:#fff;'>
            <th style='padding:6px 4px; border:1px solid #ccc; width:4%; text-align:center;'>#</th>
            <th style='padding:6px 8px; border:1px solid #ccc; width:34%; text-align:left;'>PRODUCTO</th>
            <th style='padding:6px 8px; border:1px solid #ccc; width:22%; text-align:left;'>LABORATORIO</th>
            <th style='padding:6px 4px; border:1px solid #ccc; width:8%; text-align:center;'>STOCK</th>
            <th style='padding:6px 4px; border:1px solid #ccc; width:16%; text-align:center;'>F. VENCIMIENTO</th>
            <th style='padding:6px 4px; border:1px solid #ccc; width:16%; text-align:center;'>DÍAS RESTANTES</th>
        </tr>
    </thead><tbody>";

    $i = 1;
    foreach ($lista as $p) {
        $v        = $p->_v;
        $bg       = ($i % 2 === 0) ? '#f9f9f9' : '#ffffff';
        $stock    = ($p->is_stock == 0) ? '<span style="color:#28a745;">∞</span>' : (string)(int)$p->stock;
        $nombre   = htmlspecialchars($p->name ?? '');
        $lab      = htmlspecialchars($p->laboratorio ?? '—');
        $fv       = htmlspecialchars($v['fecha_fmt']);
        $dt       = htmlspecialchars($v['dias_txt']);
        $color_dt = ($v['estado'] === 'sin_vencimiento') ? '#6c757d' : '#c0392b';

        $html .= "<tr style='background:{$bg};'>
            <td style='padding:5px 4px; border:1px solid #ddd; text-align:center; color:#555;'>{$i}</td>
            <td style='padding:5px 8px; border:1px solid #ddd; font-weight:bold;'>{$nombre}</td>
            <td style='padding:5px 8px; border:1px solid #ddd; font-size:11px;'>{$lab}</td>
            <td style='padding:5px 4px; border:1px solid #ddd; text-align:center;'>{$stock}</td>
            <td style='padding:5px 4px; border:1px solid #ddd; text-align:center; font-weight:bold;'>{$fv}</td>
            <td style='padding:5px 4px; border:1px solid #ddd; text-align:center; color:{$color_dt}; font-weight:bold;'>{$dt}</td>
        </tr>";
        $i++;
    }
    $html .= "</tbody></table>";
    return $html;
}

$cuerpo  = "<head><style>
    body { font-family: Arial, sans-serif; font-size: 13px; color:#333; }
    .resumen-box { display:inline-block; padding:12px 18px; border-radius:6px; text-align:center; margin:0 4px; }
    .rpt-tbl td { padding:5px 6px; border:1px solid #ddd; }
</style></head>";

$cuerpo .= "<h2 style='color:#1e3c72; margin-bottom:4px;'>&#128197; Reporte de Vencimientos de Productos</h2>";
$cuerpo .= "<p style='color:#555; margin-top:0;'>
    <strong>Tipo:</strong> {$tipo_ejecucion} &nbsp;|&nbsp;
    <strong>Generado:</strong> " . date('d/m/Y H:i') . "
</p>";
$cuerpo .= "<p>Se adjuntan el <strong>PDF</strong> y el <strong>Excel</strong> con el detalle completo. A continuación el resumen del reporte:</p>";

// Cajas resumen
$cuerpo .= "<div style='margin:18px 0; text-align:center;'>";
$cuerpo .= "<span class='resumen-box' style='background:#dc3545;color:#fff;'>
    <strong style='font-size:2rem;'>" . count($vencidos) . "</strong><br>VENCIDOS</span>";
$cuerpo .= "<span class='resumen-box' style='background:#fde8e8;color:#c0392b;border:2px solid #e74c3c;'>
    <strong style='font-size:2rem;'>" . count($criticos) . "</strong><br>CRÍTICOS<br><small>≤ 90 días</small></span>";
$cuerpo .= "<span class='resumen-box' style='background:#fff3cd;color:#856404;border:2px solid #ffc107;'>
    <strong style='font-size:2rem;'>" . count($proximos) . "</strong><br>PRÓXIMOS<br><small>91–120 días</small></span>";
if (!empty($sin_vencimiento)) {
    $cuerpo .= "<span class='resumen-box' style='background:#e2e3e5;color:#383d41;border:2px solid #6c757d;'>
        <strong style='font-size:2rem;'>" . count($sin_vencimiento) . "</strong><br>SIN VENC.<br><small>No perecibles</small></span>";
}
$cuerpo .= "<span class='resumen-box' style='background:#343a40;color:#fff;'>
    <strong style='font-size:2rem;'>{$total_alertas}</strong><br>TOTAL ALERTA</span>";
$cuerpo .= "</div>";

if ($total_alertas === 0 && empty($sin_vencimiento)) {
    $cuerpo .= "<p style='background:#d4edda;color:#155724;padding:14px;border-radius:6px;'>
        &#10003; <strong>¡Sin alertas!</strong> Todos los productos están vigentes con más de 120 días de anticipación.
    </p>";
} else {
    $cuerpo .= htmlTablaGrupo($vencidos,        '&#128128; Productos VENCIDOS',             '#dc3545');
    $cuerpo .= htmlTablaGrupo($criticos,        '&#9888;&#65039; CRÍTICOS (≤ 90 días)',      '#c0392b');
    $cuerpo .= htmlTablaGrupo($proximos,        '&#128336; PRÓXIMOS (91–120 días)',         '#856404');
    $cuerpo .= htmlTablaGrupo($sin_vencimiento, '&#8505;&#65039; PRODUCTOS SIN VENCIMIENTO', '#6c757d');
}

$cuerpo .= "<p style='margin-top:20px; font-size:12px; color:#888;'>
    Este reporte se genera automáticamente el día 15 y el último día de cada mes.
</p>";

// ── Enviar correo con PDF y Excel adjuntos ────────────────────────────────────
$arraddress = [];
$arrAddcc   = [];
$asunto     = "REPORTE DE VENCIMIENTOS [" . date('d/m/Y') . "] — {$tipo_ejecucion} — {$total_alertas} producto(s) con alerta";

$firma  = '<tr><td class="sub_pie">BOTICA ALFONZO UGARTE</td></tr>';
$firma .= '<tr><td class="sub_pie">botica.au@gmail.com</td></tr>';

$mailer  = new CLSPHPMailer();
$atachar = [
    $pdfPath  => "Reporte_Vencimientos_" . date('Y-m-d') . ".pdf",
    $xlsxPath => "Reporte_Vencimientos_" . date('Y-m-d') . ".xlsx"
];
$res     = $mailer->fnMail($arraddress, $arrAddcc, $asunto, $cuerpo, 'pie', $firma, $atachar);

if ($res) {
    echo "Correo enviado correctamente a: " . implode(', ', array_merge($arraddress, $arrAddcc)) . "\n";
    echo "Adjuntos: " . implode(', ', array_values($atachar)) . "\n";
} else {
    echo "ERROR: Falló el envío del correo.\n";
}

// Limpiar archivos temporales si se desea (comentado para conservar histórico)
// unlink($pdfPath);
// unlink($xlsxPath);

echo "=== FIN [" . date('H:i:s') . "] ===\n";
?>
