// Venta modal
function openProductModal(action, productData = null) {
    // Construir las opciones del select de categorías
    let categoryOptions = '<option value="">SELECCIONAR</option>';
    categories.forEach(category => {
        categoryOptions += `<option value="${category.id}" ${productData && productData.category_id == category.id ? 'selected' : ''}>${category.name}</option>`;
    });

    // Construir las opciones del select de unidades de medida
    let unidadesOptions = '<option value="">SELECCIONAR</option>';
    unidades.forEach(unidad => {
        unidadesOptions += `<option value="${unidad.id}" ${productData && productData.unit == unidad.id ? 'selected' : ''}>${unidad.name}</option>`;
    });
    
    Swal.fire({
        title: `<div class="text-left"><i class="fas ${action === 'edit' ? 'fa-edit text-warning' : 'fa-plus-circle text-primary'} mr-2"></i> ${action === 'edit' ? 'Editar Producto/Servicio' : 'Nuevo Producto/Servicio'}</div>`,
        html: `<div class="p-2 text-left compact-modal">
                <form id="productForm" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Identificación y Básicos -->
                        <div class="col-md-12 mb-3">
                            <h6 class="text-primary font-weight-bold border-bottom pb-1 text-xs uppercase"><i class="fas fa-id-card mr-1"></i> Identificación del Producto</h6>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <label class="text-xs mb-1">Código DIGEMID</label>
                                    <input type="text" name="cod_digemid" id="cod_digemid" class="form-control form-control-sm" placeholder="DIGEMID" value="${productData ? (productData.cod_digemid || '') : ''}">
                                </div>
                                <div class="col-md-3">
                                    <label class="text-xs mb-1">Código Barras*</label>
                                    <input type="text" name="barcode" id="barcode" class="form-control form-control-sm font-weight-bold" placeholder="EAN-13" value="${productData ? productData.barcode : ''}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-xs mb-1">Categoría</label>
                                    <select name="category_id" class="form-control form-control-sm">
                                        ${categoryOptions}
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-xs mb-1">U. Medida*</label>
                                    <select name="selUnidadMedida" class="form-control form-control-sm" required>
                                        ${unidadesOptions}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Información Principal -->
                        <div class="col-md-12 mb-3">
                            <h6 class="text-primary font-weight-bold border-bottom pb-1 text-xs uppercase"><i class="fas fa-info-circle mr-1"></i> Información Detallada</h6>
                            <div class="row mt-2">
                                <div class="col-md-12 mb-2">
                                    <label class="text-xs mb-1">Nombre Comercial*</label>
                                    <input type="text" name="name" id="name" class="form-control form-control-sm" placeholder="Nombre del Producto" value="${productData ? productData.name : ''}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-xs mb-1">Principio Activo</label>
                                    <input type="text" name="prin_act" id="prin_act" class="form-control form-control-sm" placeholder="Ej: Paracetamol" value="${productData ? (productData.principio_activo || '') : ''}">
                                </div>
                                <div class="col-md-4">
                                    <label class="text-xs mb-1">Presentación / Formas</label>
                                    <input type="text" name="presentacion" id="presentacion" class="form-control form-control-sm" placeholder="Ej: Caja x 100 Tab" value="${productData ? (productData.presentation || '') : ''}">
                                </div>
                                <div class="col-md-4">
                                    <label class="text-xs mb-1 font-weight-bold text-info">Laboratorio</label>
                                    <input type="text" name="laboratorio" id="laboratorio" class="form-control form-control-sm" placeholder="Nombre del Lab" value="${productData ? (productData.laboratorio || '') : ''}">
                                </div>
                            </div>
                        </div>

                        <!-- Precios e Inventario -->
                        <div class="col-md-8 mb-3 border-right">
                            <h6 class="text-primary font-weight-bold border-bottom pb-1 text-xs uppercase"><i class="fas fa-money-bill-wave mr-1"></i> Precios y Almacén</h6>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <label class="text-xs mb-1 text-danger">Precio Compra*</label>
                                    <input type="number" step="any" name="price_in" id="price_in" class="form-control form-control-sm font-weight-bold" value="${productData ? productData.price_in : ''}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-xs mb-1 text-success">Precio Venta*</label>
                                    <input type="number" step="any" name="price_out" id="price_out" class="form-control form-control-sm font-weight-bold" value="${productData ? productData.price_out : ''}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-xs mb-1 text-info">Precio Caja*</label>
                                    <input type="number" step="any" name="price_may" id="price_may" class="form-control form-control-sm" value="${productData ? productData.price_may : ''}" required>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <label class="text-xs mb-1">Anaquel / Ubicación*</label>
                                    <input type="text" name="anaquel" id="anaquel" class="form-control form-control-sm" value="${productData ? productData.anaquel : ''}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-xs mb-1">Inv. Inicial</label>
                                    <input type="number" name="q" id="q" class="form-control form-control-sm" value="${productData ? productData.stock : '0'}">
                                </div>
                                <div class="col-md-4">
                                    <label class="text-xs mb-1">Mínimo Stock</label>
                                    <input type="number" name="inventary_min" id="inventary_min" class="form-control form-control-sm" value="${productData ? productData.inventary_min : '10'}">
                                </div>
                            </div>
                        </div>

                        <!-- Opciones y Foto -->
                        <div class="col-md-4 mb-3">
                            <h6 class="text-primary font-weight-bold border-bottom pb-1 text-xs uppercase"><i class="fas fa-cog mr-1"></i> Ajustes</h6>
                            <div class="mt-2 pl-2">
                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" ${((productData && productData.is_active==1)|| action =='add') ? 'checked' : ''}>
                                    <label class="custom-control-label text-xs" for="is_active">Producto Activo</label>
                                </div>
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="is_stock" name="is_stock" ${((productData && productData.is_stock==1) || action =='add') ? 'checked' : ''}>
                                    <label class="custom-control-label text-xs" for="is_stock">Maneja Stock</label>
                                </div>
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="is_may" name="is_may" ${(productData && productData.is_may==1) ? 'checked' : ''}>
                                    <label class="custom-control-label text-xs" for="is_may">Venta x Mayor</label>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="text-xs d-block mb-1">Imagen del Producto</label>
                                <div class="text-center p-2 border rounded bg-light">
                                    <input type="file" name="image" id="image" class="d-none">
                                    <label for="image" class="btn btn-xs btn-outline-secondary mb-0">
                                        <i class="fas fa-camera mr-1"></i> Cambiar
                                    </label>
                                    <div id="preview-container" class="mt-2">
                                        <img id="img-preview" src="${productData && productData.image ? 'storage/products/'+productData.image : 'assets/img/no-image.png'}" class="img-thumbnail" style="max-height: 60px;">
                                    </div>
                                    <span id="file-name" class="text-xs d-block text-truncate mt-1"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="product_id" value="${productData ? productData.id : ''}">
                    <input type="hidden" name="fecha_venc" value="${productData ? productData.fecha_venc : fechaVencimiento}">
                </form>
               </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: action === 'edit' ? '<i class="fas fa-save mr-1"></i> Actualizar' : '<i class="fas fa-plus mr-1"></i> Registrar',
        cancelButtonText: 'Cancelar',
        width: '850px',
        didOpen: () => {
            if (!productData) {
                $('#barcode').val($('#genbarcode').val());
            }
            document.getElementById('image').addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(ex) {
                        document.getElementById('img-preview').src = ex.target.result;
                    }
                    reader.readAsDataURL(e.target.files[0]);
                    document.getElementById('file-name').textContent = e.target.files[0].name;
                }
            });
        },
        preConfirm: () => {
            const form = document.getElementById('productForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            if (!data.barcode || !data.name || !data.price_in || !data.price_out || !data.anaquel || !data.selUnidadMedida) {
                Swal.showValidationMessage('Por favor, completa todos los campos obligatorios (*)');
                return false;
            }
            return data;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const url = action === 'edit' ? './?action=updateproduct' : './?action=addproduct';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(result.value),
            })
                .then(response => response.json())
                .then(response => { console.log("response==>",response);
                    if (response.success) {
                        Swal.fire('Éxito!', `El producto ha sido ${action === 'edit' ? 'actualizado' : 'agregado'} correctamente.`, 'success')
                            .then(() => window.location = '?view=products');
                    } else {
                        Swal.fire('Error!', `Hubo un problema al ${action === 'edit' ? 'actualizar' : 'agregar'} el producto.`, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', `Hubo un problema al ${action === 'edit' ? 'actualizar' : 'agregar'} el producto.`, 'error');
                });
        }
    });
}

// Evento para abrir el modal de nuevo producto
$('#openModalNuevoProducto').on('click', function () {
    openProductModal('add');
});

// Evento para abrir el modal de edición de producto (usando delegación de eventos para DataTables)
$(document).on('click', '.edit-product', function (e) {
    e.preventDefault();
    const productId = this.getAttribute('data-id');
    fetch(`./?action=getproduct&id=${productId}`)
        .then(response => response.json())
        .then(productData => {
            openProductModal('edit', productData);
        })
        .catch(error => {
            console.error('Error al obtener los datos del producto:', error);
        });
});

// Evento para desactivar producto (usando delegación de eventos para DataTables)
$(document).on('click', '.delete-product', function (e) {
    e.preventDefault();
    const productId = this.getAttribute('data-id');
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: "El producto se marcará como inactivo y no aparecerá en las ventas, pero se conservará su historial.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('./?action=delproduct', { id: productId }, function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        title: '¡Desactivado!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message || 'Error al desactivar', 'error');
                }
            }, 'json').fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error en el servidor', 'error');
            });
        }
    });
});