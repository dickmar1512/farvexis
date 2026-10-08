<?php
$empresa = EmpresaData::getDatos();

// Generar correlativo visual para Ingreso Diverso (tipo 60)
$db = Database::getCon();
$sucursalId = (int)($_SESSION['sucursal_id'] ?? 1);
$sucursalObj = SucursalData::getById($sucursalId);
$codLocalEmisor = $sucursalObj ? $sucursalObj->codigo : '0000';
$serie_ingreso = ComprobanteCorrelativoData::getSerieDefecto('60', $codLocalEmisor);
$next_comprobante_60 = ComprobanteCorrelativoData::verSiguiente($db, '60', $serie_ingreso, $codLocalEmisor, $sucursalId);
$providers = PersonData::getProviders();

$total = 0;
if (isset($_SESSION["reabastecer"]) && count($_SESSION["reabastecer"]) > 0) {
    foreach ($_SESSION["reabastecer"] as $p) {
        $pt = $p["price_in"] * $p["q"];
        $total += $pt;
    }
}
?>
<style>
/* CSS form más compacto como en sell-view */
.compact-form { font-size: 13px; }
.compact-form .form-control { padding: 4px 8px; font-size: 12px; height: auto; min-height: 28px; }
.compact-form .form-group { margin-bottom: 8px; }
.compact-form label { font-size: 11px; font-weight: 600; margin-bottom: 2px; color: #555; }
.compact-form .btn { padding: 4px 12px; font-size: 12px; }
.compact-form .card { border: 1px solid #dee2e6; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.compact-form .card-header { background-color: #f8f9fa; padding: 8px 12px; border-bottom: 1px solid #dee2e6; }
.compact-form .card-header h6 { margin: 0; font-size: 13px; font-weight: 600; color: #495057; }
.compact-form .card-body { padding: 12px; }
.compact-form .table td, .compact-form .table th { padding: 4px 6px; font-size: 11px; vertical-align: middle; }
.compact-form .breadcrumb { padding: 4px 8px; margin-bottom: 8px; font-size: 12px; }
.compact-form .content-header h1 { font-size: 20px; margin: 0; }
.compact-form .icheck-primary, .compact-form .icheck-success, .compact-form .icheck-danger { margin: 0 10px; }
.compact-form .icheck-primary label, .compact-form .icheck-success label, .compact-form .icheck-danger label { font-size: 12px; margin: 0; cursor: pointer; }
.bg-light-info { background-color: rgba(23, 162, 184, 0.05); }
.opacity-2 { opacity: 0.2; }
</style>

<div class="compact-form">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-1">
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
            <div class="card">
                <div class="card-header text-center">
                    <h6 class="card-title w-100 mb-0 font-weight-bold">TIPO DE COMPROBANTE DE COMPRA</h6>
                    <div class="text-center mt-2">
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

                <div class="card-body">
                    <!-- Botón Agregar Item Centralizado -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-success btn-lg btn-block shadow-sm font-weight-bold" id="btnAgregarReManual" onclick="agregarProductoRe()">
                                <i class="fa fa-plus-circle"></i> AGREGAR PRODUCTO (F1)
                            </button>
                        </div>
                    </div>

                    <!-- FORMULARIO DE REABASTECIMIENTO -->
                    <form method="post" id="processReForm" action="./?action=processre">
                        <!-- Se pasa el tipo comprobante oculto (sincronizado con los radio de arriba) -->
                        <input type="hidden" name="optTipoComprobante" id="optTipoComprobante" value="1">
                        
                        <div class="row mb-3">
                            <!-- INFO COMPROBANTE -->
                            <div class="col-md-6">
                                <div class="card h-100 mb-0">
                                    <div class="card-header"><h6><i class="fa fa-file-alt"></i> Información del Comprobante</h6></div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>FECHA EMISIÓN:</label>
                                                    <input type="date" name="fecemi" required class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label id="lblSerie">SERIE:</label>
                                                    <input type="text" name="serie" id="serie_input" required class="form-control form-control-sm font-weight-bold text-uppercase" placeholder="F001">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label id="lblNumero">NÚMERO:</label>
                                                    <input type="text" name="comprobante" id="comprobante_input" required class="form-control form-control-sm font-weight-bold" placeholder="000123">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- INFO PROVEEDOR -->
                            <div class="col-md-6">
                                <div class="card h-100 mb-0">
                                    <div class="card-header"><h6><i class="fa fa-truck"></i> Información del Proveedor</h6></div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>PROVEEDOR:</label>
                                                    <select name="client_id" class="form-control select2bs4" required>
                                                        <option value="">-- SELECCIONAR PROVEEDOR --</option>
                                                        <?php foreach ($providers as $p): ?>
                                                            <option value="<?php echo $p->id; ?>"><?php echo $p->name . " " . $p->lastname; ?> (<?= $p->numero_documento ?>)</option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>MONTO PAGADO:</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text font-weight-bold" style="padding: 2px 8px; font-size: 12px;">S/</span>
                                                        </div>
                                                        <input type="number" step="any" name="money" id="money_re" required class="form-control form-control-sm font-weight-bold" value="<?php echo $total; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Carrito de Compras -->
                        <div class="card">
                            <div class="card-body p-0">
                                <div id="re-cart-container">
                                    <?php include "core/app/action/re_cart_table-action.php"; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm" style="min-width: 250px;">
                                    <i class="fas fa-check-circle mr-1"></i> FINALIZAR INGRESO
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>
</div>

<script>
window.addEventListener('load', function() {
    if (typeof jQuery !== 'undefined') {
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2bs4').select2({ theme: 'bootstrap4' });
            }

            // Sincronizar selección de comprobante
            $(document).on('change', 'input[name="optTipoRe"]', function() {
                const val = $(this).val();
                $('#optTipoComprobante').val(val);
                
                if(val == '60') {
                    $("#lblSerie").text('IDENTIFICADOR:');
                    $("#lblNumero").text('N° INTERNO:');
                    $('#serie_input').val('<?php echo $serie_ingreso; ?>').attr('placeholder', '<?php echo $serie_ingreso; ?>');
                    $('#comprobante_input').val('<?php echo $next_comprobante_60; ?>').attr('placeholder', '<?php echo $next_comprobante_60; ?>');
                } else if(val == '1') {
                    $("#lblSerie").text('SERIE:');
                    $("#lblNumero").text('NÚMERO:');
                    $('#serie_input').val('').attr('placeholder', 'F001');
                    $('#comprobante_input').val('').attr('placeholder', '000001');
                } else {
                    $("#lblSerie").text('SERIE:');
                    $("#lblNumero").text('NÚMERO:');
                    $('#serie_input').val('').attr('placeholder', 'B001');
                    $('#comprobante_input').val('').attr('placeholder', '000001');
                }
            });

            // F1 para abrir el modal de búsqueda
            $(document).on('keydown', function(e) {
                if (e.key === "F1") {
                    e.preventDefault();
                    agregarProductoRe();
                }
            });

            // AJAX submit para processReForm
            $('#processReForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                Swal.fire({ title: 'Procesando ingreso...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                $.ajax({
                    type: 'POST',
                    url: './?action=processre',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({ icon: 'success', title: '¡Listo!', text: response.message, timer: 1500, showConfirmButton: false })
                                .then(function() { window.location.href = response.redirect; });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                        }
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo procesar el ingreso.' });
                    }
                });
            });
        });
    }
});

function agregarProductoRe() {
    Swal.fire({
        title: '<h4 style="margin: 0; font-size: 1.1em;"><i class="fas fa-search"></i> Buscar Producto para Compras</h4>',
        html: `
            <div style="text-align: left; margin-top: 10px;">
                <form id="searchReForm" autocomplete="off" onsubmit="return false;">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        </div>
                        <input type="text" id="product_code_re" name="product" 
                               class="form-control" 
                               placeholder="Nombre o código del producto..."
                               autofocus>
                    </div>
                </form>
                <div id="show_search_results_re" style="margin-top: 15px; max-height: 400px; overflow-y: auto;"></div>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        width: '800px',
        didOpen: () => {
            const inputField = document.getElementById('product_code_re');
            setTimeout(() => { inputField.focus(); }, 100);

            let typingTimer;
            inputField.addEventListener('input', function() {
                clearTimeout(typingTimer);
                const query = this.value.trim();
                
                if (query.length === 0) {
                    $('#show_search_results_re').html('');
                    return;
                }
                
                typingTimer = setTimeout(() => {
                    $('#show_search_results_re').html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>');
                    $.get("./?action=searchproduct_re", { product: query }, function(data) {
                        $('#show_search_results_re').html(data);
                    }).fail(function() {
                        $('#show_search_results_re').html('<div class="alert alert-danger">Error en la búsqueda</div>');
                    });
                }, 400); // 400ms debounce
            });

            // Enviar por AJAX usando submit (enter)
            $('#searchReForm').on('submit', function() {
                const query = inputField.value.trim();
                if(query.length > 0) {
                    $('#show_search_results_re').html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>');
                    $.get("./?action=searchproduct_re", { product: query }, function(data) {
                        $('#show_search_results_re').html(data);
                    });
                }
            });
        }
    });
}

function limpiarCartRe() {
    Swal.fire({
        title: '¿Cancelar reabastecimiento?',
        text: 'Se vaciará la lista de productos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, vaciar',
        cancelButtonText: 'No'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.get('./?action=clearre', function() { refreshReCart(); });
        }
    });
}
</script>