<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$total = 0;
?>

<?php if (isset($_SESSION["reabastecer"]) && count($_SESSION["reabastecer"]) > 0): ?>
    <!-- Lista de Productos -->
    <div class="card card-outline card-primary shadow-sm mt-3">
        <div class="card-header py-2 bg-primary">
            <h3 class="card-title text-white"><i class="fas fa-shopping-basket mr-2"></i> PRODUCTOS A INGRESAR</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-striped mb-0">
                    <thead class="bg-light text-uppercase" style="font-size: 0.8rem;">
                        <tr>
                            <th class="text-center" style="width: 60px;">Cant.</th>
                            <th>Descripción del Producto</th>
                            <th class="text-center">Reg. Sanitario</th>
                            <th class="text-center">Lote</th>
                            <th class="text-center">Vencimiento</th>
                            <th>Laboratorio</th>
                            <th class="text-right">P. Unit (S/)</th>
                            <th class="text-right">Total (S/)</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION["reabastecer"] as $p): 
                            $product = ProductData::getById($p["product_id"]);
                            $pt = $p["price_in"] * $p["q"];
                            $total += $pt;
                        ?>
                            <tr style="font-size: 0.9rem;">
                                <td class="text-center"><b><?php echo $p["q"]; ?></b></td>
                                <td><?php echo $product->name; ?></td>
                                <td class="text-center"><span class="badge badge-secondary"><?= $p["rs"] ?></span></td>
                                <td class="text-center"><span class="badge badge-info"><?= $p["nl"] ?></span></td>
                                <td class="text-center"><?php echo $p["fec_venc"] ?></td>
                                <td><?php echo $p["labo"]; ?></td>
                                <td class="text-right font-weight-bold"><?php echo number_format($p["price_in"], 2, '.', ','); ?></td>
                                <td class="text-right text-primary font-weight-bold"><?php echo number_format($pt, 2, '.', ','); ?></td>
                                <td class="text-center">
                                    <button type="button" onclick="removeItemRe(<?= $product->id ?>)" class="btn btn-xs btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-right py-2">
            <h4 class="mb-0">TOTAL COMPRA: <span class="text-success font-weight-bold">S/ <?php echo number_format($total, 2, '.', ','); ?></span></h4>
        </div>
    </div>

    <!-- Datos del Comprobante (Sección Manual como pidió el usuario) -->
    <div class="card card-outline card-success shadow-sm mt-3">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-file-signature mr-2"></i> INFORMACIÓN DEL COMPROBANTE</h3>
        </div>
        <div class="card-body py-3">
            <form method="post" id="processReForm" action="./?action=processre">
                <input type="hidden" name="total" value="<?php echo $total; ?>">
                
                <!-- Radio ocultos para sincronizar con la selección superior -->
                <div style="display:none;">
                    <input type="radio" id="opt1" name="optTipoComprobante" value="1" checked>
                    <input type="radio" id="opt3" name="optTipoComprobante" value="3">
                    <input type="radio" id="opt60" name="optTipoComprobante" value="60">
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><i class="far fa-calendar-alt mr-1 text-muted"></i> FECHA DE EMISIÓN</label>
                            <input type="date" name="fecemi" required class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-truck mr-1 text-muted"></i> PROVEEDOR</label>
                            <?php $providers = PersonData::getProviders(); ?>
                            <select name="client_id" class="form-control select2bs4" required>
                                <option value="">-- SELECCIONAR PROVEEDOR --</option>
                                <?php foreach ($providers as $p): ?>
                                    <option value="<?php echo $p->id; ?>"><?php echo $p->name . " " . $p->lastname; ?> (<?= $p->numero_documento ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><i class="fas fa-money-bill-wave mr-1 text-muted"></i> MONTO PAGADO</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold">S/</span>
                                </div>
                                <input type="number" step="any" name="money" required class="form-control text-right font-weight-bold" value="<?php echo $total; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label id="lblSerie"><i class="fas fa-hashtag mr-1 text-muted"></i> SERIE</label>
                            <input type="text" name="serie" required class="form-control font-weight-bold text-uppercase" placeholder="F001">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label id="lblNumero"><i class="fas fa-list-ol mr-1 text-muted"></i> NÚMERO</label>
                            <input type="text" name="comprobante" required class="form-control font-weight-bold" placeholder="000123">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="btn-group w-100 mb-3">
                            <a href="#" onclick="limpiarCartRe()" class="btn btn-danger btn-lg shadow-sm" style="width: 30%;">
                                <i class="fas fa-times mr-1"></i> CANCELAR
                            </a>
                            <button type="submit" class="btn btn-success btn-lg shadow-sm" style="width: 70%;">
                                <i class="fas fa-check-circle mr-1"></i> FINALIZAR INGRESO
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        // Inicializar select2 en el contenido cargado dinámicamente
        if ($.fn.select2) {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
        }
        
        // Listener interno por si se cambia el radio oculto (vía script externo)
        $('input[name="optTipoComprobante"]').change(function() {
            const val = $(this).val();
            if(val == '60') {
                $("#lblSerie").html('<i class="fas fa-hashtag mr-1 text-muted"></i> IDENTIFICADOR');
                $("#lblNumero").html('<i class="fas fa-list-ol mr-1 text-muted"></i> N° INTERNO');
            } else {
                $("#lblSerie").html('<i class="fas fa-hashtag mr-1 text-muted"></i> SERIE');
                $("#lblNumero").html('<i class="fas fa-list-ol mr-1 text-muted"></i> NÚMERO');
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

    function limpiarCartRe() {
        Swal.fire({
            title: '¿Cancelar reabastecimiento?',
            text: 'Se vaciará la lista de productos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.get('./?action=clearre', function() { refreshReCart(); });
            }
        });
    }
    </script>
<?php else: ?>
    <div class="card shadow-sm mt-3 border-0 bg-light-info">
        <div class="card-body text-center py-5">
            <div class="text-muted mb-3"><i class="fas fa-shopping-basket fa-4x opacity-2 text-info"></i></div>
            <h5 class="text-info font-weight-bold">LISTA DE REABASTECIMIENTO VACÍA</h5>
            <p class="text-muted">Busque productos arriba para comenzar el proceso de ingreso a almacén.</p>
        </div>
    </div>
<?php endif; ?>
