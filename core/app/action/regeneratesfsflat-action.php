<?php
$conexion = new mysqli('localhost', 'milenio', 'armagedon', 'dbcarepharm', 3306);

if (mysqli_connect_errno()) {
    echo "La conexión con el servidor de base de datos falló: " . mysqli_connect_error();
    exit();
}

$tipo_doc = $_GET["tipo_doc"] ?? null; 
$id_tipo_doc = $_GET["id_tipo_doc"] ?? null; 
$regenerate = $_GET["regenerate"] ?? 0;

if (!$tipo_doc || !$id_tipo_doc) {
    echo "Faltan parámetros (tipo_doc, id_tipo_doc)";
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
    echo "Comprobante no encontrado en la base de datos";
    exit();
}

$base_filename = $RUC . "-" . $tipo_doc . "-" . $serie . "-" . $comprobante;
$base_path = "../efact1.3.4/sunat_archivos/sfs/DATA/";
$path_ori_dir = "../efact1.3.4/data_ori/";

if ($regenerate == 1) {
    if (!file_exists($path_ori_dir)) {
        mkdir($path_ori_dir, 0777, true);
    }
    $extensions = ['cab', 'det', 'tri', 'ley', 'aca'];
    foreach ($extensions as $ext) {
        $file = $base_path . $base_filename . "." . $ext;
        if (file_exists($file)) {
            copy($file, $path_ori_dir . $base_filename . "." . $ext);
            unlink($file);
        }
    }
}

$files_created = [];

// Función auxiliar para unir con |
function buildFlatRow($row) {
    return implode("|", array_values($row)) . "|";
}

// 1. CABECERA (.cab)
$sql_cab = "SELECT tipOperacion, fecEmision, horEmision, fecVencimiento, codLocalEmisor, tipDocUsuario, numDocUsuario, rznSocialUsuario, tipMoneda, sumTotTributos, sumTotValVenta, sumPrecioVenta, sumDescTotal, sumOtrosCargos, sumTotalAnticipos, sumImpVenta, ublVersionId, customizationId FROM cab WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_cab = $conexion->query($sql_cab);
if ($cab = $res_cab->fetch_assoc()) {
    $content = buildFlatRow($cab);
    $path = $base_path . $base_filename . ".cab";
    file_put_contents($path, $content);
    $files_created[$base_filename . ".cab"] = $content;
}

// 2. DETALLE (.det)
$det_content = "";
$sql_det = "SELECT codUnidadMedida, ctdUnidadItem, codProducto, codProductoSUNAT, desItem, mtoValorUnitario, sumTotTributosItem, codTriIGV, mtoIgvItem, mtoBaseIgvItem, nomTributoIgvItem, codTipTributoIgvItem, tipAfeIGV, porIgvItem, codTriISC, mtoIscItem, mtoBaseIscItem, nomTributoIscItem, codTipTributoIscItem, tipSisISC, porIscItem, codTriOtroItem, mtoTriOtroItem, mtoBaseTriOtroItem, nomTributoIOtroItem, codTipTributoIOtroItem, porTriOtroItem, mtoPrecioVentaUnitario, mtoValorVentaItem, mtoValorReferencialUnitario FROM det WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_det = $conexion->query($sql_det);
while ($det = $res_det->fetch_assoc()) {
    // SUNAT expects specific order. Let's map it explicitly to match the original generator logic
    $det_str = $det['codUnidadMedida']."|".
               $det['ctdUnidadItem']."|".
               $det['codProducto']."|".
               $det['codProductoSUNAT']."|".
               $det['desItem']."|".
               $det['mtoValorUnitario']."|".
               $det['sumTotTributosItem']."|".
               $det['codTriIGV']."|".
               $det['mtoIgvItem']."|".
               $det['mtoBaseIgvItem']."|".
               $det['nomTributoIgvItem']."|".
               $det['codTipTributoIgvItem']."|".
               $det['tipAfeIGV']."|".
               $det['porIgvItem']."|".
               $det['codTriISC']."|".
               $det['mtoIscItem']."|".
               $det['mtoBaseIscItem']."|".
               $det['nomTributoIscItem']."|".
               $det['codTipTributoIscItem']."|".
               $det['tipSisISC']."|".
               $det['porIscItem']."|".
               $det['codTriOtroItem']."|".
               $det['mtoTriOtroItem']."|".
               $det['mtoBaseTriOtroItem']."|".
               $det['nomTributoIOtroItem']."|".
               $det['codTipTributoIOtroItem']."|".
               $det['porTriOtroItem']."|-||||||". // ICBPER hardcoded empty as in original code
               $det['mtoPrecioVentaUnitario']."|".
               $det['mtoValorVentaItem']."|".
               $det['mtoValorReferencialUnitario']."|\r\n";
    $det_content .= $det_str;
}
if ($det_content != "") {
    $path = $base_path . $base_filename . ".det";
    file_put_contents($path, $det_content);
    $files_created[$base_filename . ".det"] = $det_content;
}

// 3. TRIBUTOS (.tri)
$tri_content = "";
$sql_tri = "SELECT ideTributo, nomTributo, codTipTributo, mtoBaseImponible, mtoTributo FROM tri WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_tri = $conexion->query($sql_tri);
while ($tri = $res_tri->fetch_assoc()) {
    $tri_content .= buildFlatRow($tri) . "\r\n";
}
if ($tri_content != "") {
    $path = $base_path . $base_filename . ".tri";
    file_put_contents($path, $tri_content);
    $files_created[$base_filename . ".tri"] = $tri_content;
}

// 4. LEYENDAS (.ley)
$ley_content = "";
$sql_ley = "SELECT codLeyenda, desLeyenda FROM ley WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_ley = $conexion->query($sql_ley);
while ($ley = $res_ley->fetch_assoc()) {
    $ley_content .= buildFlatRow($ley) . "\r\n";
}
if ($ley_content != "") {
    $path = $base_path . $base_filename . ".ley";
    file_put_contents($path, $ley_content);
    $files_created[$base_filename . ".ley"] = $ley_content;
}

// 5. DATO ADICIONAL (.aca)
$sql_aca = "SELECT ctaBancoNacionDetraccion, codBienDetraccion, porDetraccion, mtoDetraccion, codPaisCliente, codUbigeoCliente, desDireccionCliente, codPaisEntrega, codUbigeoEntrega, desDireccionEntrega FROM aca WHERE TIPO_DOC = '$tipo_doc' AND ID_TIPO_DOC = '$id_tipo_doc'";
$res_aca = $conexion->query($sql_aca);
if ($aca = $res_aca->fetch_assoc()) {
    // The original logic outputs double pipe after desDireccionCliente
    $content = $aca['ctaBancoNacionDetraccion']."|".
               $aca['codBienDetraccion']."|".
               $aca['porDetraccion']."|".
               $aca['mtoDetraccion']."|".
               $aca['codPaisCliente']."|".
               $aca['codUbigeoCliente']."|".
               $aca['desDireccionCliente']."||". // DOUBLE PIPE!
               $aca['codPaisEntrega']."|".
               $aca['codUbigeoEntrega']."|".
               $aca['desDireccionEntrega']."|";
    $path = $base_path . $base_filename . ".aca";
    file_put_contents($path, $content);
    $files_created[$base_filename . ".aca"] = $content;
}

// Generamos un ZIP para que el usuario pueda descargarlos todos a la vez.
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    echo "Archivos Planos Generados correctamente en SFS/DATA.";
}
exit();
?>
