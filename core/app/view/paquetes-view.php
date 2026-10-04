<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-cubes text-primary mr-2"></i> Gestión de Paquetes
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#modalNewKit">
                        <i class="fas fa-plus-circle mr-1"></i> AGREGAR NUEVO PAQUETE
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header py-2 text-right">
                <h3 class="card-title text-sm font-weight-bold uppercase float-left">Listado de Kits de Productos</h3>
                <small class="text-muted italic">Administre sus paquetes y presentaciones de venta</small>
            </div>
            <div class="card-body p-0">
                <?php
                $kits = KitData::getAll();
                if(count($kits) > 0):
                ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm table-valign-middle mb-0" id="paquetesTable">
                        <thead class="bg-light text-muted text-xs uppercase">
                            <tr>
                                <th class="pl-3">Código</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th class="text-center">Estado</th>
                                <th class="text-right pr-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kits as $kit): 
                                $fecha = date('Y-m-d H:i:s');
                            ?>
                            <tr>
                                <td class="pl-3 font-weight-bold text-xs"><?php echo $kit->barcode; ?></td>
                                <td>
                                    <?php if($kit->imagen != ""): ?>
                                        <img src="storage/products/<?php echo $kit->imagen; ?>" class="img-thumbnail" style="width:40px; height:40px; object-fit:cover;">
                                    <?php else: ?>
                                        <div class="img-thumbnail d-flex align-items-center justify-content-center bg-light text-muted" style="width:40px; height:40px;">
                                            <i class="fas fa-image text-xs"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-sm font-weight-bold"><?php echo $kit->nombre; ?></td>
                                <td class="text-xs text-muted"><?php echo $kit->descripcion; ?></td>
                                <td class="text-sm font-weight-bold text-primary">
                                    S/ <?php echo number_format($kit->precio, 2, '.', ','); ?>
                                </td>
                                <td class="text-center">
                                    <?php if($kit->estado == 1): ?>
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-ban mr-1"></i> Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right pr-3">
                                    <div class="btn-group">
                                        <!-- Botón Editar Info -->
                                        <button type="button" class="btn btn-xs btn-outline-info shadow-sm btn-edit-kit" 
                                                data-id="<?php echo $kit->idpaquete; ?>" title="Editar Información">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <!-- Botón Gestionar Productos -->
                                        <button type="button" class="btn btn-xs btn-outline-primary shadow-sm ml-1 btn-manage-products" 
                                                data-id="<?php echo $kit->idpaquete; ?>" data-name="<?php echo $kit->nombre; ?>" title="Gestionar Productos">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <?php if($kit->estado == 1): ?>
                                            <button onclick="cambiarEstadoKit(<?= $kit->idpaquete ?>, 0, '<?= $fecha ?>')" class="btn btn-xs btn-outline-danger shadow-sm ml-1" title="Desactivar">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        <?php else: ?>
                                            <button onclick="cambiarEstadoKit(<?= $kit->idpaquete ?>, 1, '')" class="btn btn-xs btn-outline-success shadow-sm ml-1" title="Activar">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-boxes fa-4x text-muted mb-3 opacity-2"></i>
                    <h5 class="text-muted">No se encontraron paquetes registrados</h5>
                    <p class="text-xs text-muted">Comienza agregando uno nuevo para gestionar kits de productos.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- MODAL PARA NUEVO PAQUETE -->
<?php 
    $codigo_res = KitData::getBarcode();
    $barcode_gen = "P0000";
    if(count($codigo_res) > 0){
        $barcode_gen = $codigo_res[0]->barcode;
    }
?>
<div class="modal fade" id="modalNewKit" tabindex="-1" role="dialog" aria-labelledby="modalNewKitLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalNewKitLabel">
                    <i class="fas fa-plus-circle mr-2"></i> Registrar Nuevo Paquete / Kit
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddKit" enctype="multipart/form-data">
                <div class="modal-body py-4">
                    <div class="row">
                        <div class="col-md-4 text-center border-right">
                            <label class="d-block text-muted text-xs uppercase mb-3">Imagen del Paquete</label>
                            <div class="preview-container mb-3 d-flex align-items-center justify-content-center bg-light border rounded" style="height: 180px;">
                                <i class="fas fa-image fa-4x text-muted opacity-3 icon-preview"></i>
                                <img src="#" class="img-fluid rounded d-none img-preview" style="max-height: 100%;">
                            </div>
                            <div class="custom-file">
                                <input type="file" name="image" class="custom-file-input inputImage" accept="image/*">
                                <label class="custom-file-label text-xs">Elegir archivo</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="text-xs font-weight-bold uppercase mb-1">Código de Barras*</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                            </div>
                                            <input type="text" name="barcode" class="form-control font-weight-bold text-primary" value="<?php echo $barcode_gen; ?>" required readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="text-xs font-weight-bold uppercase mb-1">Nombre del Paquete*</label>
                                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Ej. Kit Familiar de Vitaminas" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="text-xs font-weight-bold uppercase mb-1">Descripción</label>
                                        <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Detalle los productos que incluye el kit..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="text-xs font-weight-bold uppercase mb-1">Precio de Venta*</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text font-weight-bold">S/</span>
                                            </div>
                                            <input type="number" step="0.01" name="price_out" class="form-control font-weight-bold" placeholder="0.00" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link btn-sm text-muted font-weight-bold" data-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold">
                        <i class="fas fa-save mr-1"></i> GUARDAR PAQUETE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PARA EDITAR PAQUETE (INFO BÁSICA) -->
<div class="modal fade" id="modalEditKit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-edit mr-2"></i> Editar Información del Paquete
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditKit" enctype="multipart/form-data">
                <input type="hidden" name="kit_id" id="edit_kit_id">
                <div class="modal-body py-4">
                    <div class="row">
                        <div class="col-md-4 text-center border-right">
                            <label class="d-block text-muted text-xs uppercase mb-3">Imagen del Paquete</label>
                            <div class="preview-container mb-3 d-flex align-items-center justify-content-center bg-light border rounded" style="height: 180px;">
                                <img id="edit_img_preview" src="#" class="img-fluid rounded" style="max-height: 100%;">
                            </div>
                            <div class="custom-file">
                                <input type="file" name="image" class="custom-file-input inputImage" accept="image/*">
                                <label class="custom-file-label text-xs">Cambiar imagen</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="text-xs font-weight-bold uppercase mb-1">Código de Barras*</label>
                                        <input type="text" name="barcode" id="edit_barcode" class="form-control form-control-sm font-weight-bold" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 text-right">
                                        <label class="text-xs font-weight-bold uppercase d-block mb-1">Estado</label>
                                        <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                            <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active">
                                            <label class="custom-control-label text-xs" for="edit_is_active">Paquete Activo</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="text-xs font-weight-bold uppercase mb-1">Nombre del Paquete*</label>
                                        <input type="text" name="name" id="edit_name" class="form-control form-control-sm" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="text-xs font-weight-bold uppercase mb-1">Descripción</label>
                                        <textarea name="description" id="edit_description" class="form-control form-control-sm" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="text-xs font-weight-bold uppercase mb-1">Precio de Venta*</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text font-weight-bold">S/</span>
                                            </div>
                                            <input type="number" step="0.01" name="price_out" id="edit_price" class="form-control font-weight-bold" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link btn-sm text-muted font-weight-bold" data-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn btn-info px-4 shadow-sm font-weight-bold text-white">
                        <i class="fas fa-sync-alt mr-1"></i> ACTUALIZAR INFORMACIÓN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PARA GESTIONAR PRODUCTOS DEL KIT -->
<div class="modal fade" id="modalManageProducts" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-boxes mr-2"></i> Contenido del Paquete: <span id="manage_kit_name" class="text-info"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="row no-gutters">
                    <!-- Columna Búsqueda -->
                    <div class="col-md-5 border-right bg-light p-4">
                        <h6 class="text-xs font-weight-bold uppercase mb-3"><i class="fas fa-search mr-1"></i> Buscar Productos para Agregar</h6>
                        <form id="formSearchProduct" class="mb-4">
                            <input type="hidden" name="idpaquete" id="manage_kit_id">
                            <div class="input-group input-group-sm mb-3">
                                <input type="text" name="product" id="search_input" class="form-control" placeholder="Nombre o código de barras..." autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                        
                        <div id="search_results" style="max-height: 400px; overflow-y: auto;">
                            <div class="text-center py-5 text-muted opacity-3">
                                <i class="fas fa-search fa-3x mb-2"></i>
                                <p class="text-xs">Realice una búsqueda para ver resultados</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Columna Listado Actual -->
                    <div class="col-md-7 p-4">
                        <h6 class="text-xs font-weight-bold uppercase mb-3 text-primary"><i class="fas fa-list mr-1"></i> Productos en este Kit</h6>
                        <div id="kit_products_list" class="border rounded bg-white" style="min-height: 450px;">
                            <!-- Cargado vía AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">CERRAR GESTIÓN</button>
            </div>
        </div>
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
    .opacity-2 { opacity: 0.2; }
    .opacity-3 { opacity: 0.3; }
    .italic { font-style: italic; }
    #paquetesTable_wrapper .dataTables_filter { padding: 1rem; }
    #paquetesTable_wrapper .dataTables_info { padding: 1rem; font-size: 0.75rem; }
    #paquetesTable_wrapper .dataTables_paginate { padding: 1rem; }
    .custom-file-label::after { content: "Buscar"; }
</style>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        if ($('#paquetesTable').length) {
            $('#paquetesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                "pageLength": 25,
                "ordering": true,
                "responsive": true,
                "dom": '<"d-flex justify-content-between align-items-center"fB>rtip',
                "buttons": []
            });
        }

        // Preview de Imagen (para todos los inputs file)
        $(".inputImage").change(function() {
            const file = this.files[0];
            const preview = $(this).closest('.col-md-4').find('.img-preview, #edit_img_preview');
            const icon = $(this).closest('.col-md-4').find('.icon-preview');
            
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    preview.attr("src", event.target.result).removeClass("d-none");
                    icon.addClass("d-none");
                };
                reader.readAsDataURL(file);
                $(this).next('.custom-file-label').html(file.name);
            }
        });

        // ---------------------------------------------------------
        // GESTIÓN DE EDICIÓN DE INFORMACIÓN BÁSICA
        // ---------------------------------------------------------
        
        $(".btn-edit-kit").click(function() {
            const id = $(this).data("id");
            
            $.get("./?action=getkitinfo&id=" + id, function(response) {
                const res = JSON.parse(response);
                if(res.status === "success") {
                    $("#edit_kit_id").val(res.data.id);
                    $("#edit_barcode").val(res.data.barcode);
                    $("#edit_name").val(res.data.name);
                    $("#edit_description").val(res.data.description);
                    $("#edit_price").val(res.data.price);
                    $("#edit_is_active").prop("checked", res.data.is_active == 1);
                    
                    if(res.data.image) {
                        $("#edit_img_preview").attr("src", "storage/products/" + res.data.image);
                    } else {
                        $("#edit_img_preview").attr("src", "https://via.placeholder.com/180x180?text=Sin+Imagen");
                    }
                    
                    $("#modalEditKit").modal("show");
                }
            });
        });

        $("#formEditKit").on("submit", function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            
            Swal.fire({ title: 'Actualizando...', didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: "./?action=updatekit",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const res = JSON.parse(response);
                    if (res.status === "success") {
                        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: res.message, timer: 1500, showConfirmButton: false }).then(() => { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                }
            });
        });

        // ---------------------------------------------------------
        // GESTIÓN DE PRODUCTOS DEL KIT
        // ---------------------------------------------------------

        $(".btn-manage-products").click(function() {
            const id = $(this).data("id");
            const name = $(this).data("name");
            
            $("#manage_kit_id").val(id);
            $("#manage_kit_name").text(name);
            $("#search_results").html('<div class="text-center py-5 text-muted opacity-3"><i class="fas fa-search fa-3x mb-2"></i><p class="text-xs">Realice una búsqueda para ver resultados</p></div>');
            $("#search_input").val("");
            
            loadKitProducts(id);
            $("#modalManageProducts").modal("show");
        });

        function loadKitProducts(id) {
            $("#kit_products_list").html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="text-xs mt-2">Cargando lista...</p></div>');
            $.get("./?action=getdetkit&id=" + id, function(html) {
                $("#kit_products_list").html(html);
            });
        }

        $("#formSearchProduct").on("submit", function(e) {
            e.preventDefault();
            performSearch();
        });

        $("#search_input").on("keyup", function() {
            if($(this).val().length >= 2) {
                performSearch();
            } else if($(this).val().length == 0) {
                $("#search_results").html('<div class="text-center py-5 text-muted opacity-3"><i class="fas fa-search fa-3x mb-2"></i><p class="text-xs">Realice una búsqueda para ver resultados</p></div>');
            }
        });

        function performSearch() {
            const query = $("#search_input").val();
            const idpaquete = $("#manage_kit_id").val();
            
            $.get("./?action=searchproduct3", { product: query, idpaquete: idpaquete }, function(html) {
                $("#search_results").html(html);
            });
        }

        // Enfocar buscador al abrir modal
        $('#modalManageProducts').on('shown.bs.modal', function () {
            $('#search_input').trigger('focus');
        });

        // Agregar Producto vía AJAX (desde los resultados de búsqueda)
        $(document).on("click", ".btn-add-det", function() {
            const product_id = $(this).data("product");
            const idpaquete = $(this).data("kit");
            const price = $("#price_" + product_id).val();
            const desc = $("#desc_" + product_id).val();
            const qty = $("#qty_" + product_id).val();
            
            $.post("./?action=adddetkit", {
                idpaquete: idpaquete,
                product_id: product_id,
                precio_unitario: price,
                descuento: desc,
                q: qty
            }, function(response) {
                const res = JSON.parse(response);
                if(res.status === "success") {
                    Toast.fire({ icon: 'success', title: res.message });
                    loadKitProducts(idpaquete);
                }
            });
        });

        // Eliminar Producto vía AJAX (desde la lista del kit)
        $(document).on("click", ".btn-del-det", function() {
            const iddet = $(this).data("id");
            const idpaquete = $("#manage_kit_id").val();
            
            Swal.fire({
                title: '¿Eliminar?',
                text: "El producto se quitará de este paquete.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("./?action=deldetkit", { iddet: iddet }, function(response) {
                        const res = JSON.parse(response);
                        if(res.status === "success") {
                            Toast.fire({ icon: 'success', title: res.message });
                            loadKitProducts(idpaquete);
                        }
                    });
                }
            });
        });

        // Configuración de Toast de SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Envío AJAX del Formulario de Nuevo Kit
        $("#formAddKit").on("submit", function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            $.ajax({
                url: "./?action=addkit",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    let res = JSON.parse(response);
                    if (res.status === "success") {
                        Swal.fire({ icon: 'success', title: '¡Registrado!', text: res.message, timer: 2000, showConfirmButton: false }).then(() => { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                }
            });
        });
    });
</script>
<script>
function cambiarEstadoKit(id, est, fecha) {
    var msg = est == 0 ? '¿Desactivar este paquete?' : '¿Activar este paquete?';
    Swal.fire({
        title: msg, icon: 'question',
        showCancelButton: true,
        confirmButtonColor: est == 0 ? '#d33' : '#28a745',
        confirmButtonText: 'Sí', cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.get('./?action=delkit', { id: id, est: est, fecha: fecha }, function(response) {
                if (response.status === 'success') {
                    Swal.fire({ icon: 'success', title: '¡Listo!', showConfirmButton: false, timer: 900, toast: true, position: 'top-end' })
                        .then(function() { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            }, 'json');
        }
    });
}
</script>
