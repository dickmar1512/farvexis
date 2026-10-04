<?php
// Generar siguiente número para Serie 004 y Tipo 60
$last_sd = SellData::getLastBySerie('004', '60');
$next_num = 1;
if($last_sd){
    $next_num = intval($last_sd->comprobante) + 1;
}

function lpad($num, $len) {
    return str_pad($num, $len, "0", STR_PAD_LEFT);
}
$correlativo = lpad($next_num, 8);
?>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-file-invoice text-warning mr-2"></i> Nueva Salida Diversa</h1>
            </div>
            <div class="col-sm-6 text-right">
                <span class="badge badge-dark p-2" style="font-size: 1rem;">Serie: 004 - Nº: <?php echo $correlativo; ?></span>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Columna Izquierda: Selección de Productos -->
            <div class="col-md-5">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title text-sm font-weight-bold uppercase">Buscador de Productos</h3>
                    </div>
                    <div class="card-body">
                        <div class="input-group input-group-sm mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="search_product" class="form-control" placeholder="Buscar por nombre o código..." autocomplete="off">
                        </div>
                        <div id="search_results" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                            <div class="text-center py-5 text-muted opacity-3">
                                <i class="fas fa-search fa-4x mb-3"></i>
                                <p>Empiece a escribir para buscar productos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Resumen de Salida -->
            <div class="col-md-7">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title text-sm font-weight-bold uppercase">Productos Seleccionados para Baja</h3>
                    </div>
                    <form id="formAddSD">
                        <div class="card-body p-0">
                            <div id="sd_cart_list" style="min-height: 300px;">
                                <!-- Se carga vía AJAX -->
                                <div class="text-center py-5 text-muted italic">
                                    <i class="fas fa-info-circle mr-1"></i> No hay productos seleccionados aún.
                                </div>
                            </div>
                            
                            <div class="px-4 py-3 bg-light border-top">
                                <div class="form-group mb-0">
                                    <label class="text-xs font-weight-bold uppercase mb-1">Motivo / Observación de la Salida*</label>
                                    <textarea name="observacion" id="observacion" class="form-control form-control-sm" rows="3" placeholder="Ej. Productos vencidos lote 2023, frasco roto, etc." required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <input type="hidden" name="serie" value="004">
                            <input type="hidden" name="comprobante" value="<?php echo $correlativo; ?>">
                            <a href="./?view=salidasdiversas" class="btn btn-link text-muted btn-sm font-weight-bold mr-3">CANCELAR</a>
                            <button type="submit" class="btn btn-warning px-4 shadow-sm font-weight-bold">
                                <i class="fas fa-save mr-1"></i> PROCESAR SALIDA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        // Limpiar carrito temporal al cargar
        $.post("./?action=sd_cart", { op: "clear" }, function() { loadCart(); });

        // Búsqueda en tiempo real
        $("#search_product").on("keyup", function() {
            const query = $(this).val();
            if(query.length >= 2) {
                $.get("./?action=searchproductsd", { product: query }, function(html) {
                    $("#search_results").html(html);
                });
            } else if(query.length == 0) {
                $("#search_results").html('<div class="text-center py-5 text-muted opacity-3"><i class="fas fa-search fa-4x mb-3"></i><p>Empiece a escribir para buscar productos</p></div>');
            }
        });

        // Agregar al carrito
        $(document).on("click", ".btn-add-sd", function() {
            const id = $(this).data("id");
            const qty = $("#qty_" + id).val();
            
            $.post("./?action=sd_cart", { op: "add", product_id: id, q: qty }, function() {
                Toast.fire({ icon: 'success', title: 'Agregado a la lista' });
                loadCart();
            });
        });

        // Quitar del carrito
        $(document).on("click", ".btn-remove-sd", function() {
            const id = $(this).data("id");
            $.post("./?action=sd_cart", { op: "remove", product_id: id }, function() {
                loadCart();
            });
        });

        function loadCart() {
            $.get("./?action=sd_cart_list", function(html) {
                $("#sd_cart_list").html(html);
            });
        }

        // Procesar Salida
        $("#formAddSD").on("submit", function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: '¿Confirmar Salida?',
                text: "Esta acción descontará el stock de los productos seleccionados.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, procesar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    
                    $.ajax({
                        url: "./?action=addsalidadiversa",
                        type: "POST",
                        data: $(this).serialize(),
                        success: function(response) {
                            const res = JSON.parse(response);
                            if(res.status === "success") {
                                Swal.fire({ icon: 'success', title: '¡Éxito!', text: res.message, timer: 2000, showConfirmButton: false })
                                .then(() => { location.href = "./?view=salidasdiversas"; });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                            }
                        }
                    });
                }
            });
        });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    });
</script>
