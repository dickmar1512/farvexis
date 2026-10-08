<?php
####################### NUMERO DE REGISTRO DE COMPROBANTES ###############################
$mysqli = Database::getCon(); //BASE DE DATOS

$sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
$sucursalObj = SucursalData::getById($sucursalId);
$codLocalEmisor = $sucursalObj ? $sucursalObj->codigo : '0000';

$defaultBoletaSerie = ComprobanteCorrelativoData::getSerieDefecto('03', $codLocalEmisor);
$defaultFacturaSerie = ComprobanteCorrelativoData::getSerieDefecto('01', $codLocalEmisor);
$defaultNvSerie = ComprobanteCorrelativoData::getSerieDefecto('70', $codLocalEmisor);

// BOLETA (03)
$q_b = $mysqli->query("SELECT serie, ultimo_numero FROM comprobante_correlativo WHERE sucursal_id=$sucursalId AND tipo_comprobante='03' AND serie='$defaultBoletaSerie'");
if($q_b && $row_b = $q_b->fetch_assoc()){
    $SERIE = $row_b['serie'];
    $COMPROBANTE = str_pad($row_b['ultimo_numero'] + 1, 8, '0', STR_PAD_LEFT);
} else {
    $SERIE = $defaultBoletaSerie;
    $COMPROBANTE = '00000001';
}

// FACTURA (01)
$q_f = $mysqli->query("SELECT serie, ultimo_numero FROM comprobante_correlativo WHERE sucursal_id=$sucursalId AND tipo_comprobante='01' AND serie='$defaultFacturaSerie'");
if($q_f && $row_f = $q_f->fetch_assoc()){
    $SERIE_F = $row_f['serie'];
    $COMPROBANTE_F = str_pad($row_f['ultimo_numero'] + 1, 8, '0', STR_PAD_LEFT);
} else {
    $SERIE_F = $defaultFacturaSerie;
    $COMPROBANTE_F = '00000001';
}

// NOTA VENTA (70)
$q_nv = $mysqli->query("SELECT serie, ultimo_numero FROM comprobante_correlativo WHERE sucursal_id=$sucursalId AND tipo_comprobante='70' AND serie='$defaultNvSerie'");
if($q_nv && $row_nv = $q_nv->fetch_assoc()){
    $SERIE_NV = $row_nv['serie'];
    $ORDEN = str_pad($row_nv['ultimo_numero'] + 1, 8, '0', STR_PAD_LEFT);
} else {
    $SERIE_NV = $defaultNvSerie;
    $ORDEN = '00000001';
}

$empresa = EmpresaData::getDatos();

// Cálculo de totales para los formularios (Boleta, Factura, Nota Venta)
$total = 0;
$dsctotal = 0;
$has_controlled = false;
if (isset($_SESSION["cart"]) && count($_SESSION["cart"]) > 0) {
    foreach ($_SESSION["cart"] as $p) {
        $product = ProductData::getById($p["product_id"]);
        if($product) {
            if ($product->is_controlled == 1) {
                $has_controlled = true;
            }
            $precio = ($product->is_may == 1) ? $product->price_may : $p["precio_unitario"];
            $pt = ($precio - $p["descuento"]) * $p["q"];
            $total += $pt;
            $dsctotal += $p["descuento"] * $p["q"];
        }
    }
}
$total = round($total, 2);
$dsctotal = round($dsctotal, 2);
$empresa = EmpresaData::getDatos();
?>

<style>
/* CSS para hacer el formulario más compacto con cards */
.compact-form {
    font-size: 13px;
}
.compact-form .form-control {
    padding: 4px 8px;
    font-size: 12px;
    height: auto;
    min-height: 28px;
}
.compact-form .form-group {
    margin-bottom: 8px;
}
.compact-form label {
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 2px;
    color: #555;
}
.compact-form .btn {
    padding: 4px 12px;
    font-size: 12px;
}
.compact-form .card {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.compact-form .card-header {
    background-color: #f8f9fa;
    padding: 8px 12px;
    border-bottom: 1px solid #dee2e6;
}
.compact-form .card-header h6 {
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}
.compact-form .card-body {
    padding: 12px;
}
.compact-form .table td, .compact-form .table th {
    padding: 4px 6px;
    font-size: 11px;
    vertical-align: middle;
}
.compact-form .breadcrumb {
    padding: 4px 8px;
    margin-bottom: 8px;
    font-size: 12px;
}
.compact-form .content-header h1 {
    font-size: 20px;
    margin: 0;
}
.compact-form .icheck-primary, .compact-form .icheck-success, .compact-form .icheck-danger {
    margin: 0 10px;
}
.compact-form .icheck-primary input, .compact-form .icheck-success input, .compact-form .icheck-danger input {
    margin-right: 5px;
}
.compact-form .icheck-primary label, .compact-form .icheck-success label, .compact-form .icheck-danger label {
    font-size: 12px;
    margin: 0;
}
.btn-full {
    width: 100%;
    background: #28a745;
    color: white;
    border: none;
    padding: 10px;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
    margin-bottom: 10px;
}
.btn-full:hover {
    background: #218838;
}
.icon-inbox {
    font-size: 48px;
    color: #ccc;
}
.icon-container {
    text-align: center;
    padding: 40px;
}
.total-section {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    margin-top: 10px;
}
.total-section .total-amount {
    font-size: 18px;
    font-weight: bold;
    color: #28a745;
}
.action-buttons {
    margin-top: 10px;
}
</style>

<div class="compact-form">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class='fa fa-shopping-cart'></i> Añadir Producto</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Venta</a></li>
                        <li class="breadcrumb-item active">Generar Venta</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">ELIJA COMPROBANTE</h6>
                    <div class="text-center mt-2">
                        <div class="icheck-primary d-inline">
                            <input type="radio" id="optTipoComprobante1" name="optTipoComprobante" value="3" checked>
                            <label for="optTipoComprobante1">BOLETA (<span style="color:red"><b>F4</b></span>)</label>
                        </div>
                        <div class="icheck-success d-inline">
                            <input type="radio" id="optTipoComprobante2" name="optTipoComprobante" value="1">
                            <label for="optTipoComprobante2">FACTURA (<span style="color:red"><b>F5</b></span>)</label>
                        </div>
                        <div class="icheck-danger d-inline">
                            <input type="radio" id="optTipoComprobante3" name="optTipoComprobante" value="0">
                            <label for="optTipoComprobante3">NOTA VENTA (<span style="color:red"><b>F6</b></span>)</label>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <?php
                    $permiso = PermisoData::get_permiso_x_key('proforma');
                    $permiso2 = PermisoData::get_permiso_x_key('comprobantes_fisicos');
                    $permiso3 = PermisoData::get_permiso_x_key('otro_documento_no_dni');
                    $ubigeos = SellData::getAllUbigeo();
                    $clients = PersonData::getClients();
                    
                    $total = 0;
                    $dsctotal = 0;
                    if (isset($_SESSION["cart"]) && count($_SESSION["cart"]) > 0) {
                        foreach ($_SESSION["cart"] as $p) {
                            $product = ProductData::getById($p["product_id"]);
                            $precio = ($product->is_may == 1) ? $product->price_may : $p["precio_unitario"];
                            $dsctotal += $p["descuento"] * $p["q"];
                            $total += ($precio - $p["descuento"]) * $p["q"];
                        }
                    }
                    $total = round($total, 2);
                    ?>

                    <!-- Botón Agregar Item Centralizado -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-success btn-lg btn-block" id="btnAgregarItemCentral">
                                <i class="fa fa-plus-circle"></i> AGREGAR PRODUCTO (F1)
                            </button>
                        </div>
                    </div>

                    <!-- FORMULARIO BOLETA -->
                    <div id="comprobante_boleta">
                        <form id="formboleta" class="form-horizontal" method="post" onsubmit="enviado2(3,event)">
                            <input type="hidden" name="RUC" value="<?php echo $empresa->Emp_Ruc; ?>">
                            <input type="hidden" name="TIPO" value="03">
                            <input type="hidden" name="tipOperacion" value="0101">
                            <input type="hidden" name="fecVencimiento" value="-">
                            <input type="hidden" name="codLocalEmisor" value="<?php echo $codLocalEmisor; ?>">
                            <input type="hidden" name="tipMoneda" value="PEN">
                            <input type="hidden" name="porDescGlobal" value="-">
                            <input type="hidden" name="mtoDescGlobal" value="0">
                            <input type="hidden" name="mtoBasImpDescGlobal" value="0">
                            <input type="hidden" name="sumTotTributos" value="0">
                            <input type="hidden" name="sumDescTotal" value="<?= $dsctotal ?>">
                            <input type="hidden" name="sumOtrosCargos" value="0">
                            <input type="hidden" name="sumTotalAnticipos" value="0">
                            <input type="hidden" name="ublVersionId" value="2.1">
                            <input type="hidden" name="customizationId" value="2.0">
                            <input type="hidden" name="tipDocUsuario" value="1">
                            <input type="hidden" name="codProducto" value="0">
                            <input type="hidden" name="codUnidadMedida" value="NIU">
                            <input type="hidden" name="sumTotTributosItem" value="0">
                            <input type="hidden" name="codProductoSUNAT" value="-">
                            <input type="hidden" name="mtoIgvItem" value="0">
                            <input type="hidden" name="codTriIGV" value="9997">
                            <input type="hidden" name="codTipTributoIgvItem" value="VAT">
                            <input type="hidden" name="nomTributoIgvItem" value="EXO">
                            <input type="hidden" name="tipAfeIGV" value="20">
                            <input type="hidden" name="codCatTributoIgvItem" value="E">
                            <input type="hidden" name="tipSisISC" value="">
                            <input type="hidden" name="mtoIscItem" value="0">
                            <input type="hidden" name="porIgvItem" value="0">
                            <input type="hidden" name="codTriISC" value="-">
                            <input type="hidden" name="nomTributoIscItem" value="">
                            <input type="hidden" name="codTipTributoIscItem" value="-">
                            <input type="hidden" name="codCatTributoIscItem" value="-">
                            <input type="hidden" name="porIscItem" value="">
                            <input type="hidden" name="mtoValorReferencialUnitario" value="0">
                            <input type="hidden" name="codTipDescuentoItem" value="-">
                            <input type="hidden" name="porDescuentoItem" value="0">
                            <input type="hidden" name="mtoDescuentoItem" value="0">
                            <input type="hidden" name="mtoBasImpDescuentoItem" value="0">
                            <input type="hidden" name="codTipCargoItem" value="-">
                            <input type="hidden" name="porCargoItem" value="0">
                            <input type="hidden" name="mtoCargoItem" value="0">
                            <input type="hidden" name="mtoBasImpCargoItem" value="0">
                            <input type="hidden" name="ideTributo" value="9997">
                            <input type="hidden" name="nomTributo" value="EXO">
                            <input type="hidden" name="codTipTributo" value="VAT">
                            <input type="hidden" name="codCatTributo" value="E">
                            <input type="hidden" name="mtoTributo" value="0">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h6><i class="fa fa-file-alt"></i> Información del Comprobante</h6></div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6"><div class="form-group"><label>FECHA:</label><input type="date" name="fecEmision" class="form-control form-control-sm" value="<?php echo date("Y-m-d"); ?>"></div></div>
                                                <div class="col-md-6"><div class="form-group"><label>HORA:</label><input type="text" name="horEmision" class="form-control form-control-sm" value="<?php echo date('H:i:s'); ?>"></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6"><div class="form-group"><label>SERIE:</label><input type="text" name="SERIE" class="form-control form-control-sm" value="<?php echo $SERIE; ?>"></div></div>
                                                <div class="col-md-6"><div class="form-group"><label>CORRELATIVO:</label><input type="text" name="COMPROBANTE" class="form-control form-control-sm" value="<?php echo $COMPROBANTE; ?>"></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group"><label>FORMA DE PAGO:</label>
                                                        <select name="formaPago" class="form-control form-control-sm" onchange="toggleCredito(this.value, 'boleta')"><option value="1" selected>Contado</option><option value="2">Crédito</option></select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group"><label>TIPO DE PAGO:</label>
                                                        <select name="selTipoPago" class="form-control form-control-sm"><option value="1" selected>Efectivo</option><option value="2">Plin</option><option value="3">Yape</option><option value="4">T. Débito</option><option value="5">T. Crédito</option></select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" id="vencimiento_boleta" style="display:none;">
                                                    <div class="form-group"><label class="text-danger font-weight-bold"><i class="fa fa-calendar-alt"></i> FECHA VENCIMIENTO CRÉDITO:</label>
                                                        <input type="date" name="fecVencimiento" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h6><i class="fa fa-user"></i> Información del Cliente</h6></div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4"><div class="form-group"><label><?php echo ($permiso3->Pee_Valor == 1) ? 'Documento:' : 'DNI:'; ?></label><input type="number" name="numDocUsuario" id="numDocUsuario" class="form-control form-control-sm" onblur="<?php echo ($permiso3->Pee_Valor == 1) ? 'validar_no_dni()' : 'validar_dni()'; ?>" required value="00000000"></div></div>
                                                <div class="col-md-8"><div class="form-group"><label>CLIENTE:</label><input type="text" name="rznSocialUsuario" id="rznSocialUsuario" class="form-control form-control-sm" value="Cliente General" required></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4"><div class="form-group"><label>DISTRITO:</label><select name="codUbigeoCliente" id="codUbigeoCliente" class="form-control form-control-sm"><option value="0">SELECCIONAR</option><?php foreach ($ubigeos as $ubigeo): ?><option value="<?php echo $ubigeo->codubigeo; ?>"><?php echo $ubigeo->distrito; ?></option><?php endforeach; ?></select></div></div>
                                                <div class="col-md-8"><div class="form-group"><label>DIRECCIÓN:</label><input type="text" name="desDireccionCliente" id="desDireccionCliente" class="form-control form-control-sm" placeholder="Dirección"></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4"><div class="form-group"><label>DESCUENTO:</label><input type="number" name="discount" id="discount" class="form-control form-control-sm" required value="<?= $dsctotal ?>" step="any"></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>CASH CLIENTE:</label><input type="number" name="money" id="money" required class="form-control form-control-sm" step="any" value="<?php echo $total; ?>"></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>PAGO PARCIAL EFECTIVO:</label><input type="number" name="pagoParcial" id="pagoParcial" class="form-control form-control-sm" step="any" value="0"></div></div>
                                            </div>
                                            <div class="row mt-2 border-top pt-2 recipe-section" style="display:none;">
                                                <div class="col-12">
                                                    <h6 class="text-danger font-weight-bold"><i class="fa fa-prescription"></i> RECETA MÉDICA (Producto Controlado)</h6>
                                                    <small class="recipe-products-list text-muted d-block mb-2"></small>
                                                </div>
                                                <div class="col-md-8"><div class="form-group"><label>PACIENTE NOMBRE:</label><div class="input-group"><input type="text" name="paciente_nombre" class="form-control form-control-sm" id="paciente_nombre_1"><div class="input-group-append"><button type="button" class="btn btn-sm btn-info" onclick="openModalPaciente(1)"><i class="fa fa-user-plus"></i></button></div></div></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>PACIENTE DNI:</label><input type="text" name="paciente_dni" class="form-control form-control-sm" id="paciente_dni_1"></div></div>
                                                <div class="col-md-8"><div class="form-group"><label>MÉDICO NOMBRE:</label><div class="input-group"><input type="text" name="medico_nombre" class="form-control form-control-sm" id="medico_nombre_1"><div class="input-group-append"><button type="button" class="btn btn-sm btn-info" onclick="openModalMedico(1)"><i class="fa fa-user-md"></i></button></div></div></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>MÉDICO CMP:</label><input type="text" name="medico_cmp" class="form-control form-control-sm" id="medico_cmp_1"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card"><div class="card-header"><h6><i class="fa fa-shopping-cart"></i> Carrito de Compras</h6></div><div class="card-body"><div class="cart-container"><?php include "core/app/action/cart_table-action.php"; ?></div></div></div>
                        </form>
                    </div>

                    <!-- FORMULARIO FACTURA -->
                    <div id="comprobante_factura" style="display: none">
                        <form id="formfactura" class="form-horizontal" method="post" onsubmit="enviado2(1,event)">
                            <input type="hidden" name="RUC" value="<?php echo $empresa->Emp_Ruc; ?>">
                            <input type="hidden" name="TIPO" value="01">
                            <input type="hidden" name="tipOperacion" value="0101">
                            <input type="hidden" name="fecVencimiento" value="-">
                            <input type="hidden" name="codLocalEmisor" value="<?php echo $codLocalEmisor; ?>">
                            <input type="hidden" name="tipMoneda" value="PEN">
                            <input type="hidden" name="porDescGlobal" value="-">
                            <input type="hidden" name="mtoDescGlobal" value="0">
                            <input type="hidden" name="mtoBasImpDescGlobal" value="0">
                            <input type="hidden" name="sumTotTributos" value="0">
                            <input type="hidden" name="sumDescTotal" value="<?= $dsctotal ?>">
                            <input type="hidden" name="sumOtrosCargos" value="0">
                            <input type="hidden" name="sumTotalAnticipos" value="0">
                            <input type="hidden" name="ublVersionId" value="2.1">
                            <input type="hidden" name="customizationId" value="2.0">
                            <input type="hidden" name="tipDocUsuario" value="6">
                            <input type="hidden" name="codProducto" value="0">
                            <input type="hidden" name="codUnidadMedida" value="NIU">
                            <input type="hidden" name="sumTotTributosItem" value="0">
                            <input type="hidden" name="codProductoSUNAT" value="-">
                            <input type="hidden" name="mtoIgvItem" value="0">
                            <input type="hidden" name="codTriIGV" value="9997">
                            <input type="hidden" name="codTipTributoIgvItem" value="VAT">
                            <input type="hidden" name="nomTributoIgvItem" value="EXO">
                            <input type="hidden" name="tipAfeIGV" value="20">
                            <input type="hidden" name="codCatTributoIgvItem" value="E">
                            <input type="hidden" name="tipSisISC" value="">
                            <input type="hidden" name="mtoIscItem" value="0">
                            <input type="hidden" name="porIgvItem" value="0">
                            <input type="hidden" name="codTriISC" value="-">
                            <input type="hidden" name="nomTributoIscItem" value="">
                            <input type="hidden" name="codTipTributoIscItem" value="-">
                            <input type="hidden" name="codCatTributoIscItem" value="-">
                            <input type="hidden" name="porIscItem" value="">
                            <input type="hidden" name="mtoValorReferencialUnitario" value="0">
                            <input type="hidden" name="codTipDescuentoItem" value="-">
                            <input type="hidden" name="porDescuentoItem" value="0">
                            <input type="hidden" name="mtoDescuentoItem" value="0">
                            <input type="hidden" name="mtoBasImpDescuentoItem" value="0">
                            <input type="hidden" name="codTipCargoItem" value="-">
                            <input type="hidden" name="porCargoItem" value="0">
                            <input type="hidden" name="mtoCargoItem" value="0">
                            <input type="hidden" name="mtoBasImpCargoItem" value="0">
                            <input type="hidden" name="ideTributo" value="9997">
                            <input type="hidden" name="nomTributo" value="EXO">
                            <input type="hidden" name="codTipTributo" value="VAT">
                            <input type="hidden" name="codCatTributo" value="E">
                            <input type="hidden" name="mtoTributo" value="0">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h6><i class="fa fa-file-alt"></i> Información del Comprobante</h6></div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6"><div class="form-group"><label>FECHA:</label><input type="date" name="fecEmision" class="form-control form-control-sm" value="<?php echo date("Y-m-d"); ?>"></div></div>
                                                <div class="col-md-6"><div class="form-group"><label>HORA:</label><input type="text" name="horEmision" class="form-control form-control-sm" value="<?php echo date('H:i:s'); ?>"></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6"><div class="form-group"><label>SERIE:</label><input type="text" name="SERIE" class="form-control form-control-sm" value="<?php echo $SERIE_F; ?>"></div></div>
                                                <div class="col-md-6"><div class="form-group"><label>CORRELATIVO:</label><input type="text" name="COMPROBANTE" class="form-control form-control-sm" value="<?php echo $COMPROBANTE_F; ?>"></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6"><div class="form-group"><label>FORMA DE PAGO:</label><select name="formaPago" class="form-control form-control-sm" onchange="toggleCredito(this.value, 'factura')"><option value="1" selected>Contado</option><option value="2">Crédito</option></select></div></div>
                                                <div class="col-md-6"><div class="form-group"><label>TIPO DE PAGO:</label><select name="selTipoPago" class="form-control form-control-sm"><option value="1" selected>Efectivo</option><option value="2">Plin</option><option value="3">Yape</option><option value="4">T. Débito</option><option value="5">T. Crédito</option></select></div></div>
                                                <div class="col-md-12" id="vencimiento_factura" style="display:none;">
                                                    <div class="form-group"><label class="text-danger font-weight-bold"><i class="fa fa-calendar-alt"></i> FECHA VENCIMIENTO CRÉDITO:</label>
                                                        <input type="date" name="fecVencimiento" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h6><i class="fa fa-user"></i> Información del Cliente</h6></div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4"><div class="form-group"><label>RUC:</label><input type="number" name="numDocUsuario" class="form-control form-control-sm" id="ruc" onblur="validar_ruc()" required></div></div>
                                                <div class="col-md-8"><div class="form-group"><label>RAZÓN SOCIAL:</label><input type="text" name="rznSocialUsuario" id="rznSocialUsuario" class="form-control form-control-sm" required></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4"><div class="form-group"><label>DISTRITO:</label><select name="codUbigeoCliente" id="codUbigeoCliente" class="form-control form-control-sm"><option value="0">SELECCIONAR</option><?php foreach ($ubigeos as $ubigeo): ?><option value="<?php echo $ubigeo->codubigeo; ?>"><?php echo $ubigeo->distrito; ?></option><?php endforeach; ?></select></div></div>
                                                <div class="col-md-8"><div class="form-group"><label>DIRECCIÓN:</label><input type="text" name="desDireccionCliente" id="desDireccionCliente" class="form-control form-control-sm"></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4"><div class="form-group"><label>DESCUENTO:</label><input type="number" name="discount" id="discount2" class="form-control form-control-sm" required value="<?= $dsctotal ?>" step="any"></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>CASH CLIENTE:</label><input type="number" name="money" id="money2" required class="form-control form-control-sm" step="any" value="<?php echo $total; ?>"></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>PAGO PARCIAL:</label><input type="number" name="pagoParcial" class="form-control form-control-sm" step="any" value="0"></div></div>
                                            </div>
                                            <div class="row mt-2 border-top pt-2 recipe-section" style="display:none;">
                                                <div class="col-12">
                                                    <h6 class="text-danger font-weight-bold"><i class="fa fa-prescription"></i> RECETA MÉDICA (Producto Controlado)</h6>
                                                    <small class="recipe-products-list text-muted d-block mb-2"></small>
                                                </div>
                                                <div class="col-md-8"><div class="form-group"><label>PACIENTE NOMBRE:</label><div class="input-group"><input type="text" name="paciente_nombre" class="form-control form-control-sm" id="paciente_nombre_2"><div class="input-group-append"><button type="button" class="btn btn-sm btn-info" onclick="openModalPaciente(2)"><i class="fa fa-user-plus"></i></button></div></div></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>PACIENTE DNI:</label><input type="text" name="paciente_dni" class="form-control form-control-sm" id="paciente_dni_2"></div></div>
                                                <div class="col-md-8"><div class="form-group"><label>MÉDICO NOMBRE:</label><div class="input-group"><input type="text" name="medico_nombre" class="form-control form-control-sm" id="medico_nombre_2"><div class="input-group-append"><button type="button" class="btn btn-sm btn-info" onclick="openModalMedico(2)"><i class="fa fa-user-md"></i></button></div></div></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>MÉDICO CMP:</label><input type="text" name="medico_cmp" class="form-control form-control-sm" id="medico_cmp_2"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card"><div class="card-header"><h6><i class="fa fa-shopping-cart"></i> Carrito de Compras</h6></div><div class="card-body"><div class="cart-container"><?php include "core/app/action/cart_table-action.php"; ?></div></div></div>
                        </form>
                    </div>

                    <!-- FORMULARIO NOTA VENTA -->
                    <div id="comprobante_orden" style="display: none;">
                        <form id="formnotaventa" class="form-horizontal" method="post" onsubmit="enviado2(0,event)">
                            <input type="hidden" name="TIPO" value="70">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h6><i class="fa fa-sticky-note"></i> Información de la Nota de Venta</h6></div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6"><div class="form-group"><label>FECHA:</label><input type="date" name="fecEmision" class="form-control form-control-sm" value="<?php echo date("Y-m-d"); ?>"></div></div>
                                                <div class="col-md-6"><div class="form-group"><label>HORA:</label><input type="text" name="horEmision" class="form-control form-control-sm" value="<?php echo date('H:i:s'); ?>"></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6"><div class="form-group"><label>SERIE:</label><input type="text" name="SERIE" class="form-control form-control-sm" value="<?php echo $SERIE_NV; ?>"></div></div>
                                                <div class="col-md-6"><div class="form-group"><label>Nº ORDEN:</label><input type="text" name="COMPROBANTE" class="form-control form-control-sm" value="<?php echo $ORDEN; ?>"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h6><i class="fa fa-user"></i> Información del Cliente</h6></div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group"><label>CLIENTE:</label>
                                                        <div class="input-group">
                                                            <select name="client_id" id="nv_client_id" class="form-control form-control-sm">
                                                                <?php foreach ($clients as $client): ?><option value="<?php echo $client->id; ?>"><?php echo $client->name . " " . $client->lastname; ?></option><?php endforeach; ?>
                                                            </select>
                                                            <div class="input-group-append">
                                                                <button type="button" class="btn btn-sm btn-info" onclick="openModalClienteNV()"><i class="fa fa-plus"></i> Nuevo</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group"><label>EFECTIVO:</label><input type="number" name="money3" id="money3" class="form-control form-control-sm" step="any" required><input type="hidden" name="discount3" value="0"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-2 border-top pt-2 recipe-section" style="display:none;">
                                                <div class="col-12">
                                                    <h6 class="text-danger font-weight-bold"><i class="fa fa-prescription"></i> RECETA MÉDICA (Producto Controlado)</h6>
                                                    <small class="recipe-products-list text-muted d-block mb-2"></small>
                                                </div>
                                                <div class="col-md-8"><div class="form-group"><label>PACIENTE NOMBRE:</label><div class="input-group"><input type="text" name="paciente_nombre" class="form-control form-control-sm" id="paciente_nombre_3"><div class="input-group-append"><button type="button" class="btn btn-sm btn-info" onclick="openModalPaciente(3)"><i class="fa fa-user-plus"></i></button></div></div></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>PACIENTE DNI:</label><input type="text" name="paciente_dni" class="form-control form-control-sm" id="paciente_dni_3"></div></div>
                                                <div class="col-md-8"><div class="form-group"><label>MÉDICO NOMBRE:</label><div class="input-group"><input type="text" name="medico_nombre" class="form-control form-control-sm" id="medico_nombre_3"><div class="input-group-append"><button type="button" class="btn btn-sm btn-info" onclick="openModalMedico(3)"><i class="fa fa-user-md"></i></button></div></div></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>MÉDICO CMP:</label><input type="text" name="medico_cmp" class="form-control form-control-sm" id="medico_cmp_3"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card"><div class="card-header"><h6><i class="fa fa-shopping-cart"></i> Carrito de Compras</h6></div><div class="card-body"><div class="cart-container"><?php include "core/app/action/cart_table-action.php"; ?></div></div></div>
                        </form>
                    </div>

                    <!-- SECCIÓN DE ERRORES -->
                    <div id="error_container">
                        <?php if (isset($_SESSION["errors"])): ?>
                            <div class="alert alert-danger mt-3">
                                <h6>Errores encontrados:</h6>
                                <table class="table table-sm">
                                    <tr><th>Código</th><th>Producto</th><th>Mensaje</th></tr>
                                    <?php foreach ($_SESSION["errors"] as $error): $product = ProductData::getById($error["product_id"]); ?>
                                        <tr><td><?php echo $product->barcode; ?></td><td><?php echo $product->name; ?></td><td><b><?php echo $error["message"]; ?></b></td></tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            <?php unset($_SESSION["errors"]); ?>
                        <?php endif; ?>
                    </div>

                    <!-- MODALES DE CREACION -->
                    <div class="modal fade" id="modalCrearPersona" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <form id="formCrearPersona" onsubmit="savePersonaAjax(event)">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalPersonaTitle">Nuevo</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="role" id="modal_role" value="">
                                        <input type="hidden" id="modal_target_idx" value="">
                                        
                                        <div class="form-group">
                                            <label>Nombres</label>
                                            <input type="text" class="form-control form-control-sm" name="nombres" id="modal_nombres" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Apellidos</label>
                                            <input type="text" class="form-control form-control-sm" name="apellido_paterno" id="modal_apellidos">
                                        </div>
                                        <div class="form-group" id="group_doc">
                                            <label>Documento / DNI</label>
                                            <input type="text" class="form-control form-control-sm" name="numero_documento" id="modal_documento">
                                        </div>
                                        <div class="form-group" id="group_cmp" style="display:none;">
                                            <label>CMP</label>
                                            <input type="text" class="form-control form-control-sm" name="cmp" id="modal_cmp">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>