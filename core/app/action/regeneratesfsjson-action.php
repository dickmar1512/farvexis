<?php
$conexion = new mysqli('localhost', 'milenio', 'armagedon', 'dbcarepharm', 3306);

if (mysqli_connect_errno()) {
    echo json_encode(["status" => "error", "message" => "La conexión con el servidor de base de datos falló: " . mysqli_connect_error()]);
    exit();
}

$tipo_doc = $_GET["tipo_doc"] ?? null; 
$id_tipo_doc = $_GET["id_tipo_doc"] ?? null; 
$regenerate = $_GET["regenerate"] ?? 0;

if (!$tipo_doc || !$id_tipo_doc) {
    echo json_encode(["status" => "error", "message" => "Faltan parámetros (tipo_doc, id_tipo_doc)"]);
    exit();
}

$empresa = EmpresaData::getDatos();
$RUC = $empresa->Emp_Ruc;
$serie = "";
$comprobante = "";

$tabla_doc = ($tipo_doc == '01') ? "factura" : "boleta";
$sql_doc = "SELECT SERIE, COMPROBANTE FROM $tabla_doc WHERE id = '$id_tipo_doc'";
$res_doc = $conexion->query($sql_doc);

if ($row = $res_doc->fetch_assoc()) {
    $serie = $row["SERIE"];
    $comprobante = $row["COMPROBANTE"];
} else {
    echo json_encode(["status" => "error", "message" => "Comprobante no encontrado en la base de datos"]);
    exit();
}

$json_data = array();

// 1. CABECERA
$sql_cab = "SELECT tipOperacion, fecEmision, horEmision, fecVencimiento, codLocalEmisor, tipDocUsuario, numDocUsuario, rznSocialUsuario, tipMoneda, sumTotTributos, sumTotValVenta, sumPrecioVenta, sumDescTotal, sumOtrosCargos, sumTotalAnticipos, sumImpVenta, ublVersionId, customizationId FROM cab WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_cab = $conexion->query($sql_cab);
if ($cab = $res_cab->fetch_assoc()) {
    $json_data["cabecera"] = $cab;
}

// 2. DETALLE
$json_data["detalle"] = array();
$sql_det = "SELECT codUnidadMedida, ctdUnidadItem, codProducto, codProductoSUNAT, desItem, mtoValorUnitario, sumTotTributosItem, codTriIGV, mtoIgvItem, mtoBaseIgvItem, nomTributoIgvItem, codTipTributoIgvItem, tipAfeIGV, porIgvItem, codTriISC, mtoIscItem, mtoBaseIscItem, nomTributoIscItem, codTipTributoIscItem, tipSisISC, porIscItem, codTriOtroItem, mtoTriOtroItem, mtoBaseTriOtroItem, nomTributoIOtroItem, codTipTributoIOtroItem, porTriOtroItem, codTriIcbper, mtoTriIcbperItem, ctdBolsasTriIcbperItem, nomTributoIcbperItem, codTipTributoIcbperItem, mtoTriIcbperUnidad, mtoPrecioVentaUnitario, mtoValorVentaItem, mtoValorReferencialUnitario FROM det WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_det = $conexion->query($sql_det);
while ($det = $res_det->fetch_assoc()) {
    $json_data["detalle"][] = $det;
}

// 3. TRIBUTOS
$json_data["tributos"] = array();
$sql_tri = "SELECT ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo FROM tri WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_tri = $conexion->query($sql_tri);
while ($tri = $res_tri->fetch_assoc()) {
    $json_data["tributos"][] = $tri;
}

// 4. LEYENDAS
$json_data["leyendas"] = array();
$sql_ley = "SELECT codLeyenda, desLeyenda FROM ley WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_ley = $conexion->query($sql_ley);
while ($ley = $res_ley->fetch_assoc()) {
    $json_data["leyendas"][] = $ley;
}

// 5. DATO ADICIONAL (ACA)
$sql_aca = "SELECT ctaBancoNacionDetraccion, codBienDetraccion, porDetraccion, mtoDetraccion, codPaisCliente, codUbigeoCliente, desDireccionCliente, codPaisEntrega, codUbigeoEntrega, desDireccionEntrega FROM aca WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_aca = $conexion->query($sql_aca);
if ($aca = $res_aca->fetch_assoc()) {
    $json_data["datoAdicional"] = $aca;
}

// Nombre del archivo a generar
$filename = $RUC . "-" . $tipo_doc . "-" . $serie . "-" . $comprobante . ".json";
$path = "../efact1.3.4/sunat_archivos/sfs/DATA/" . $filename;
$path_ori_dir = "../efact1.3.4/data_ori/";
$path_ori = $path_ori_dir . $filename;

if ($regenerate == 1 && file_exists($path)) {
    if (!file_exists($path_ori_dir)) {
        mkdir($path_ori_dir, 0777, true);
    }
    copy($path, $path_ori);
    unlink($path);
}

// Convertir array a JSON String
$json_string = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Guardar el archivo directamente en la carpeta SFS DATA
@file_put_contents($path, $json_string);

if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    echo "JSON Generado correctamente en SFS/DATA.";
}
exit();
?>
