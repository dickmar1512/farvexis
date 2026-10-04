<?php
/**
 * regenerate_nota_xml-action.php
 * Regenera los detalles (tabla det) de una Nota de Crédito a partir de su documento original.
 */

$numParam = $_POST["num"] ?? '';

header('Content-Type: application/json; charset=utf-8');

if (empty($numParam)) {
    echo json_encode(['exito' => false, 'descripcion' => 'Número de comprobante inválido']);
    exit;
}

// Buscar la nota
$comp_cab = NotData::getByIdComprobado($numParam);
if (!$comp_cab) {
    echo json_encode(['exito' => false, 'descripcion' => 'No se encontró la nota de crédito']);
    exit;
}

$idNota = $comp_cab->id;
$tipoDocNota = 7; // Nota de Crédito

// Buscar el documento original
$docOrigenNum = $comp_cab->serieDocModifica;
$esFactura = (strpos($docOrigenNum, 'F') === 0);

$detallesOriginales = [];
$idDocOriginal = 0;
$tipoDocOriginal = $esFactura ? 1 : 3;

$cabeceraOriginal = null;
$triOriginal = null;
$leyOriginal = null;

if ($esFactura) {
    $product = Factura2Data::getByNumDoc($docOrigenNum);
    if ($product) {
        $idDocOriginal = $product->id;
        $detallesOriginales = DetData::getByIdNota($idDocOriginal, 1);
        $cabeceraOriginal = CabData::getById($idDocOriginal, 1);
        $triOriginal = TriData::getById($idDocOriginal, 1);
        $leyOriginal = LeyData::getById($idDocOriginal, 1);
    }
} else {
    $product = BoletaData::getByNumDoc($docOrigenNum);
    if ($product) {
        $idDocOriginal = $product->id;
        $detallesOriginales = DetData::getByIdNota($idDocOriginal, 3);
        $cabeceraOriginal = CabData::getById($idDocOriginal, 3);
        $triOriginal = TriData::getById($idDocOriginal, 3);
        $leyOriginal = LeyData::getById($idDocOriginal, 3);
    }
}

if (empty($detallesOriginales)) {
    echo json_encode(['exito' => false, 'descripcion' => 'No se encontraron detalles en el documento original para regenerar la nota']);
    exit;
}

$conexion = Database::getCon();

// Eliminar detalles actuales si existiesen
$sql_del = "DELETE FROM det WHERE ID_TIPO_DOC = '$idNota' AND TIPO_DOC = '$tipoDocNota'";
$conexion->query($sql_del);

// Insertar detalles desde el documento original
foreach ($detallesOriginales as $det) {
    $codUnidadMedida = $det->codUnidadMedida ?? 'NIU';
    $cantidad = $det->ctdUnidadItem ?? 1;
    $codProducto = $det->codProducto ?? '0';
    $codProductoSUNAT = $det->codProductoSUNAT ?? '-';
    $descripcion_producto = $det->desItem ?? 'PRODUCTO';
    $precio_unitario = $det->mtoValorUnitario ?? 0;
    
    $mtoValorVentaItem = $det->mtoValorVentaItem ?? 0;
    $sumTotTributosItem = $det->sumTotTributosItem ?? 0;
    
    $codTriIGV = $det->codTriIGV ?? '9997';
    $mtoIgvItem = $det->mtoIgvItem ?? 0;
    $mtoBaseIgvItem = $det->mtoBaseIgvItem ?? 0;
    $nomTributoIgvItem = $det->nomTributoIgvItem ?? 'EXO';
    $codTipTributoIgvItem = $det->codTipTributoIgvItem ?? 'VAT';
    $tipAfeIGV = $det->tipAfeIGV ?? '20';
    $porIgvItem = $det->porIgvItem ?? 18;
    
    $codTriISC = $det->codTriISC ?? '-';
    $mtoIscItem = $det->mtoIscItem ?? 0;
    $mtoBaseIscItem = $det->mtoBaseIscItem ?? 0;
    $nomTributoIscItem = $det->nomTributoIscItem ?? '';
    $codTipTributoIscItem = $det->codTipTributoIscItem ?? '';
    $tipSisISC = $det->tipSisISC ?? '';
    $porIscItem = $det->porIscItem ?? '';
    
    $codTriOtroItem = $det->codTriOtroItem ?? '-';
    $mtoTriOtroItem = $det->mtoTriOtroItem ?? 0;
    $mtoBaseTriOtroItem = $det->mtoBaseTriOtroItem ?? 0;
    $nomTributoIOtroItem = $det->nomTributoIOtroItem ?? '';
    $codTipTributoIOtroItem = $det->codTipTributoIOtroItem ?? '';
    $porTriOtroItem = $det->porTriOtroItem ?? '';
    
    $mtoPrecioVentaUnitario = $det->mtoPrecioVentaUnitario ?? 0;
    $mtoValorReferencialUnitario = $det->mtoValorReferencialUnitario ?? 0;

    $sql_DET = "INSERT INTO det (
        TIPO_DOC, ID_TIPO_DOC,
        codUnidadMedida, ctdUnidadItem, codProducto, codProductoSUNAT,
        desItem, mtoValorUnitario, sumTotTributosItem,
        codTriIGV, mtoIgvItem, mtoBaseIgvItem,
        nomTributoIgvItem, codTipTributoIgvItem, tipAfeIGV, porIgvItem,
        codTriISC, mtoIscItem, mtoBaseIscItem,
        nomTributoIscItem, codTipTributoIscItem, tipSisISC, porIscItem,
        codTriOtroItem, mtoTriOtroItem, mtoBaseTriOtroItem,
        nomTributoIOtroItem, codTipTributoIOtroItem, porTriOtroItem,
        mtoPrecioVentaUnitario, mtoValorVentaItem, mtoValorReferencialUnitario
    ) VALUES (
        '$tipoDocNota', '$idNota',
        '$codUnidadMedida', '$cantidad', '$codProducto', '$codProductoSUNAT',
        '" . $conexion->real_escape_string($descripcion_producto) . "', '$precio_unitario', '$sumTotTributosItem',
        '$codTriIGV', '$mtoIgvItem', '$mtoBaseIgvItem',
        '$nomTributoIgvItem', '$codTipTributoIgvItem', '$tipAfeIGV', '$porIgvItem',
        '$codTriISC', '$mtoIscItem', '$mtoBaseIscItem',
        '$nomTributoIscItem', '$codTipTributoIscItem', '$tipSisISC', '$porIscItem',
        '$codTriOtroItem', '$mtoTriOtroItem', '$mtoBaseTriOtroItem',
        '$nomTributoIOtroItem', '$codTipTributoIOtroItem', '$porTriOtroItem',
        '$mtoPrecioVentaUnitario', '$mtoValorVentaItem', '$mtoValorReferencialUnitario'
    )";
    $conexion->query($sql_DET);
}

// Actualizar cabecera si estaba en 0
if ($cabeceraOriginal && ($comp_cab->sumPrecioVenta == 0 || $comp_cab->sumPrecioVenta === null)) {
    $sumTotValVenta = floatval($cabeceraOriginal->sumTotValVenta ?? 0);
    $sumPrecioVenta = floatval($cabeceraOriginal->sumPrecioVenta ?? 0);
    $sumTotTributos = floatval($cabeceraOriginal->sumTotTributos ?? 0);
    $sumImpVenta    = floatval($cabeceraOriginal->sumImpVenta ?? 0);
    
    $conexion->query("UPDATE nota SET sumTotValVenta='$sumTotValVenta', sumPrecioVenta='$sumPrecioVenta', sumTotTributos='$sumTotTributos', sumImpVenta='$sumImpVenta' WHERE id='$idNota'");
}

// Restaurar tabla tri
$conexion->query("DELETE FROM tri WHERE ID_TIPO_DOC='$idNota' AND TIPO_DOC='$tipoDocNota'");
if ($triOriginal) {
    $ideTributo = $triOriginal->ideTributo ?? '9997';
    $nomTributo = $triOriginal->nomTributo ?? 'EXO';
    $codTipTributo = $triOriginal->codTipTributo ?? 'VAT';
    $mtoBaseImponible = $triOriginal->mtoBaseImponible ?? 0;
    $mtoTributo = $triOriginal->mtoTributo ?? 0;
    $conexion->query("INSERT INTO tri (TIPO_DOC, ID_TIPO_DOC, ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo) VALUES ('$tipoDocNota', '$idNota', '$ideTributo', '$nomTributo', '$codTipTributo', '$mtoBaseImponible', '$mtoTributo')");
}

// Restaurar tabla ley
$conexion->query("DELETE FROM ley WHERE ID_TIPO_DOC='$idNota' AND TIPO_DOC='$tipoDocNota'");
if ($leyOriginal) {
    $codLeyenda = $leyOriginal->codLeyenda ?? '1000';
    $desLeyenda = $conexion->real_escape_string($leyOriginal->desLeyenda ?? '');
    $conexion->query("INSERT INTO ley (TIPO_DOC, ID_TIPO_DOC, codLeyenda, desLeyenda) VALUES ('$tipoDocNota', '$idNota', '$codLeyenda', '$desLeyenda')");
}

echo json_encode(['exito' => true, 'descripcion' => 'Los detalles y datos principales de la nota han sido regenerados correctamente a partir del documento original']);
?>


