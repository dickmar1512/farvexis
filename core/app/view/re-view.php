<?php
$empresa = EmpresaData::getDatos();

// Generar correlativo para Ingreso Diverso (tipo 60)
$sql = "SELECT MAX(CAST(comprobante AS UNSIGNED)) as max_comp FROM sell WHERE tipo_comprobante='60' AND serie='I001'";
$query = Executor::doit($sql);
$r = $query[0]->fetch_array();
$next_comprobante_60 = $r['max_comp'] ? sprintf("%06d", $r['max_comp'] + 1) : "000001";
?>
<style>
.compact-form .card-header {
    padding: 8px 12px;
}
.compact-form .card-body {
    padding: 12px;
}
.compact-form .icheck-primary, .compact-form .icheck-success, .compact-form .icheck-danger {
    margin: 0 10px;
}
.compact-form .icheck-primary label, .compact-form .icheck-success label, .compact-form .icheck-danger label {
    font-size: 13px;
    margin: 0;
    cursor: pointer;
}
.bg-light-info { background-color: rgba(23, 162, 184, 0.05); }
.opacity-2 { opacity: 0.2; }
</style>

<div class="compact-form">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class='fas fa-truck-loading mr-2'></i> Gestión de Reabastecimiento</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="./?view=res">Compras</a></li>
                        <li class="breadcrumb-item active">Reabastecer</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Selección de Comprobante (Similar a sell-view) -->
            <div class="card card-primary card-outline shadow-sm mb-3">
                <div class="card-header text-center">
                    <h6 class="card-title w-100 mb-0 font-weight-bold">TIPO DE COMPROBANTE DE COMPRA</h6>
                </div>
                <div class="card-body py-2">
                    <div class="text-center">
                        <div class="icheck-primary d-inline mr-3">
                            <input type="radio" id="optRe1" name="optTipoRe" value="1" checked>
                            <label for="optRe1"><i class="fas fa-file-invoice"></i> FACTURA COMPRA</label>
                        </div>
                        <div class="icheck-success d-inline mr-3">
                            <input type="radio" id="optRe3" name="optTipoRe" value="3">
                            <label for="optRe3"><i class="fas fa-file-invoice-dollar"></i> BOLETA COMPRA</label>
                        </div>
                        <div class="icheck-danger d-inline">
                            <input type="radio" id="optRe60" name="optTipoRe" value="60">
                            <label for="optRe60"><i class="fas fa-clipboard-list"></i> INGRESO DIVERSO</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Sección de Búsqueda -->
                <div class="col-md-12">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm"><i class="fas fa-search mr-1"></i> BUSCADOR DE PRODUCTOS</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" id="btnAgregarReManual">
                                    <i class="fas fa-plus mr-1"></i> AGREGAR MANUAL (F1)
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <form id="searchForm" autocomplete="off">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-barcode text-primary"></i></span>
                                            </div>
                                            <input type="text" id="product_code2" name="product" 
                                                   class="form-control border-left-0" 
                                                   placeholder="Escriba el nombre o código para buscar..."
                                                   autofocus>
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de Resultados de Búsqueda -->
                <div class="col-md-12" id="search-results-container">
                    <!-- Se llena mediante AJAX en re.js -->
                </div>

                <!-- Contenedor del Carrito (Lista de productos agregados) -->
                <div class="col-md-12" id="re-cart-container">
                    <?php include "core/app/action/re_cart_table-action.php"; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
window.addEventListener('load', function() {
    if (typeof jQuery !== 'undefined') {
        $(document).ready(function() {
            // Sincronizar selección de comprobante con el formulario del carrito
            $(document).on('change', 'input[name="optTipoRe"]', function() {
                const val = $(this).val();
                // Buscar el radio correspondiente en el formulario generado por AJAX
                $(`#opt${val}`).prop('checked', true).trigger('change');
                
                // Cambiar placeholders según tipo
                if(val == '60') {
                    $('input[name="serie"]').val('I001').attr('placeholder', 'I001');
                    $('input[name="comprobante"]').val('<?php echo $next_comprobante_60; ?>').attr('placeholder', '<?php echo $next_comprobante_60; ?>');
                } else if(val == '1') {
                    $('input[name="serie"]').val('').attr('placeholder', 'F001');
                    $('input[name="comprobante"]').val('').attr('placeholder', '000001');
                } else {
                    $('input[name="serie"]').val('').attr('placeholder', 'B001');
                    $('input[name="comprobante"]').val('').attr('placeholder', '000001');
                }
            });

            // F1 para búsqueda manual o activar buscador
            $(document).on('keydown', function(e) {
                if (e.key === "F1") {
                    e.preventDefault();
                    $("#product_code2").focus();
                }
            });
        });
    }
});
</script>