<?php
/**
 * Generador CLI de plantilla Excel para CarePharm
 * Ejecutar: php generate_template.php
 * Genera: assets/templates/plantilla_productos.xlsx
 */

// --- DEFINICIÓN DE COLUMNAS según captura del usuario ---
$headers = [
    'A' => ['title' => 'COD.DIGEMID'                   , 'width' => 18, 'style' => 1],
    'B' => ['title' => 'PRODUCTO'                      , 'width' => 40, 'style' => 1],
    'C' => ['title' => 'PRINCIPIO ACTIVO'              , 'width' => 35, 'style' => 1],
    'D' => ['title' => 'PRESENTACION/FORMAS POR UNIDAD', 'width' => 30, 'style' => 1],
    'E' => ['title' => 'LABORATORIO'                   , 'width' => 22, 'style' => 2],
    'F' => ['title' => 'F.V.'                          , 'width' => 14, 'style' => 3],
    'G' => ['title' => 'CANT x CAJA'                   , 'width' => 14, 'style' => 4],
    'H' => ['title' => 'CANT. UNI'                     , 'width' => 12, 'style' => 4],
    'I' => ['title' => 'LOT'                           , 'width' => 12, 'style' => 4],
    'J' => ['title' => 'PREC.PROVEEDOR'                , 'width' => 16, 'style' => 4],
    'K' => ['title' => 'PRECIO UNITARIO (PRECIO COSTO)', 'width' => 26, 'style' => 5],
    'L' => ['title' => 'FACTURA Nro'                   , 'width' => 18, 'style' => 2],
    'M' => ['title' => 'Nro DE GUIA'                   , 'width' => 16, 'style' => 2],
    'N' => ['title' => 'PRECIO VTA.'                   , 'width' => 14, 'style' => 6],
    'O' => ['title' => 'PRECIO VTA. COMERCIAL'         , 'width' => 22, 'style' => 6],
    'P' => ['title' => 'CODIGO DE BARRAS'              , 'width' => 20, 'style' => 1],
    'Q' => ['title' => 'PROVEEDOR'                     , 'width' => 28, 'style' => 2],
    'R' => ['title' => 'RUC'                           , 'width' => 16, 'style' => 2],
    'S' => ['title' => 'SEDE'                          , 'width' => 14, 'style' => 4],
    'T' => ['title' => 'F.COMPRA'                      , 'width' => 14, 'style' => 3],
    'U' => ['title' => 'REGISTRO SA.'                  , 'width' => 18, 'style' => 2],
];

$fieldNames = [
    'A' => 'cod_digemid', 'B' => 'name',         'C' => 'principio_activo',  'D' => 'presentation',
    'E' => 'laboratorio', 'F' => 'fecha_venc',   'G' => 'cant_caja',    'H' => 'stock',
    'I' => 'num_lote',    'J' => 'prec_prov',    'K' => 'price_in',     'L' => 'nro_factura',
    'M' => 'nro_guia',    'N' => 'price_out',    'O' => 'price_may',    'P' => 'barcode',
    'Q' => 'proveedor',   'R' => 'ruc',          'S' => 'sede',         'T' => 'fecha_compra',
    'U' => 'reg_san',
];

// Fila de ejemplo según la captura del usuario
$example = [
    'A' => '1211041',
    'B' => 'BICARBONATO DE SODIO POTE X 500',
    'C' => 'BICARBONATO DE SODIO',
    'D' => 'POTE X 50 GR',
    'E' => 'OLCASA S.A.C.',
    'F' => '1/25/2026',
    'G' => '1',
    'H' => '12',
    'I' => '35167',
    'J' => 'S/ 15.96',
    'K' => 'S/ 1.13',
    'L' => 'F001-0000857',
    'M' => '',
    'N' => 'S/ 2.00',
    'O' => 'S/ 1.80',
    'P' => '7.9641-15 F001-0000657',
    'Q' => '2300657+90',
    'R' => '',
    'S' => 'IQUITOS',
    'T' => '5/05/2026',
    'U' => '123456',
];

// ===== SHARED STRINGS =====
$strings = [];
function ss_idx(&$strings, $val) {
    $k = array_search($val, $strings, true);
    if ($k === false) { $strings[] = $val; return count($strings) - 1; }
    return $k;
}
foreach ($headers    as $h) { ss_idx($strings, $h['title']); }
foreach ($fieldNames as $v) { ss_idx($strings, $v); }
foreach ($example    as $v) { if ($v !== '') ss_idx($strings, $v); }

// ===== STYLES XML =====
$stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="4">
    <font><sz val="9"/><name val="Calibri"/></font>
    <font><b/><sz val="9"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
    <font><sz val="8"/><color rgb="FF666666"/><name val="Calibri"/><i/></font>
    <font><sz val="9"/><name val="Calibri"/></font>
  </fonts>
  <fills count="11">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF17375E"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1F497D"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFE26B0A"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF595959"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFC0504D"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF375623"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFEAF1FB"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFF2CC"/></patternFill></fill>
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
  <cellXfs count="10">
    <xf numFmtId="0" fontId="0" fillId="0"  borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="3"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="4"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="5"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="6"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="7"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="2" fillId="8"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="3" fillId="9"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
    <xf numFmtId="0" fontId="3" fillId="10" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
  </cellXfs>
</styleSheet>';

// ===== WORKSHEET =====
$colDefs = '<cols>';
$i = 1;
foreach ($headers as $h) {
    $colDefs .= '<col min="'.$i.'" max="'.$i.'" width="'.$h['width'].'" customWidth="1"/>';
    $i++;
}
$colDefs .= '</cols>';

// Row 1: Coloured headers
$row1 = '<row r="1" ht="42" customHeight="1">';
foreach ($headers as $col => $h) {
    $si = ss_idx($strings, $h['title']);
    $row1 .= '<c r="'.$col.'1" t="s" s="'.$h['style'].'"><v>'.$si.'</v></c>';
}
$row1 .= '</row>';

// Row 2: Field-name hints (italic/blue bg)
$row2 = '<row r="2" ht="13">';
foreach ($fieldNames as $col => $fn) {
    $si = ss_idx($strings, $fn);
    $row2 .= '<c r="'.$col.'2" t="s" s="7"><v>'.$si.'</v></c>';
}
$row2 .= '</row>';

// Row 3: Example data (yellow bg)
$row3 = '<row r="3" ht="16">';
foreach (array_keys($headers) as $col) {
    $val = $example[$col] ?? '';
    if ($val !== '') {
        $si = ss_idx($strings, $val);
        $row3 .= '<c r="'.$col.'3" t="s" s="8"><v>'.$si.'</v></c>';
    } else {
        $row3 .= '<c r="'.$col.'3" s="9"/>';
    }
}
$row3 .= '</row>';

// Rows 4-103: Empty data rows
$emptyRows = '';
$colKeys = array_keys($headers);
for ($r = 4; $r <= 103; $r++) {
    $emptyRows .= '<row r="'.$r.'" ht="15">';
    foreach ($colKeys as $col) {
        $emptyRows .= '<c r="'.$col.$r.'" s="9"/>';
    }
    $emptyRows .= '</row>';
}

$worksheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
           xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheetViews>
    <sheetView tabSelected="1" workbookViewId="0">
      <pane ySplit="2" topLeftCell="A3" activePane="bottomLeft" state="frozen"/>
      <selection pane="bottomLeft" activeCell="A3" sqref="A3"/>
    </sheetView>
  </sheetViews>
  <sheetFormatPr defaultRowHeight="15" customHeight="1"/>
  '.$colDefs.'
  <sheetData>
    '.$row1.'
    '.$row2.'
    '.$row3.'
    '.$emptyRows.'
  </sheetData>
  <pageSetup orientation="landscape" paperSize="9"/>
</worksheet>';

// Shared Strings XML
$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
     count="'.count($strings).'" uniqueCount="'.count($strings).'">';
foreach ($strings as $s) {
    $ssXml .= '<si><t xml:space="preserve">'.htmlspecialchars($s, ENT_XML1, 'UTF-8').'</t></si>';
}
$ssXml .= '</sst>';

// ===== ASSEMBLE XLSX =====
$zip = new ZipArchive();
$tmpFile = tempnam(sys_get_temp_dir(), 'cpxlsx_');
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
    <sheet name="LISTA EXCEL" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

$zip->addFromString('xl/styles.xml', $stylesXml);
$zip->addFromString('xl/sharedStrings.xml', $ssXml);
$zip->addFromString('xl/worksheets/sheet1.xml', $worksheetXml);
$zip->close();

// Guardar en assets/templates/
$destDir = __DIR__ . '/assets/templates';
if (!is_dir($destDir)) mkdir($destDir, 0777, true);
$destFile = $destDir . '/plantilla_productos.xlsx';
copy($tmpFile, $destFile);
unlink($tmpFile);

echo "OK - Plantilla generada: " . filesize($destFile) . " bytes\n";
echo "Ruta: $destFile\n";
