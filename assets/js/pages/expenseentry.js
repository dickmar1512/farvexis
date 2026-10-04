$(document).ready(function () {
    // Configuración de SweetAlert2
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });

    // Cargar gastos con filtros
    function cargarGastos(fecha_inicio, fecha_fin, usuario_id) {
        $.ajax({
            url: './?action=getexpense',
            type: 'GET',
            dataType: 'json',
            data: {
                fecha_inicio: fecha_inicio,
                fecha_fin: fecha_fin,
                usuario_id: usuario_id
            },
            beforeSend: function () {
                $('#tablaGastos tbody').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
                $('#totalGastos').text('0.00');
            },
            success: function (response) {
                if (response.success) {
                    let html = '';
                    let total = 0;

                    if (response.data.length > 0) {
                        response.data.forEach(gasto => {
                            total += parseFloat(gasto.importe);
                            html += `
                                <tr>
                                    <td>${formatDate(gasto.fecha)}</td>
                                    <td>${escapeHtml(gasto.descripcion)}</td>
                                    <td>${escapeHtml(gasto.comprobante)}</td>
                                    <td>${escapeHtml(gasto.usuario)}</td>
                                    <td class="text-right">${parseFloat(gasto.importe).toFixed(2)}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-action btn-editar-gasto" data-id="${gasto.id}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-action btn-eliminar-gasto" data-id="${gasto.id}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="6" class="text-center">No se encontraron resultados</td></tr>';
                    }

                    $('#tablaGastos tbody').html(html);
                    $('#totalGastos').text(total.toFixed(2));
                    // Agregar la clase 'datatable' después de cargar los datos
                    if (!$.fn.DataTable.isDataTable('#tablaGastos')) {
                        initDataTable('#tablaGastos');
                    }
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Error al cargar los gastos', 'error');
            }
        });
    }

    // Formatear fecha
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-PE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Escapar HTML
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Cargar inicialmente los gastos del día
    //const hoy = new Date().toISOString().split('T')[0];
    const hoy = new Date().toLocaleDateString('en-CA'); // 'en-CA' da formato YYYY-MM-DD
    console.log('Cargando gastos para la fecha:', hoy);
    cargarGastos(hoy, hoy, 0);

    // Aplicar filtros
    $('#formFiltros').submit(function (e) {
        e.preventDefault();
        const fecha_inicio = $('#fecha_inicio').val();
        const fecha_fin = $('#fecha_fin').val();
        const usuario_id = $('#usuario_id').val();

        cargarGastos(fecha_inicio, fecha_fin, usuario_id);
    });

    // Resetear filtros
    // $('#btnReset').click(function () {
    //     const hoy = new Date().toISOString().split('T')[0];
    //     $('#fecha_inicio').val(hoy);
    //     $('#fecha_fin').val(hoy);
    //     $('#usuario_id').val(0);
    //     cargarGastos(hoy, hoy, 0);
    // });

    // Nuevo Gasto
    $('#btnNuevoGasto').click(function () {
        Swal.fire({
            title: 'Registrar Nuevo Gasto',
            html: `
                    <form id="formGasto">
                        <div class="form-group mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" id="descripcion" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">N° Comprobante</label>
                            <input type="text" id="comprobante" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Importe (S/)</label>
                            <input type="number" step="0.01" id="importe" class="form-control" required>
                        </div>
                    </form>
                `,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            focusConfirm: false,
            preConfirm: () => {
                return {
                    descripcion: $('#descripcion').val(),
                    comprobante: $('#comprobante').val(),
                    importe: $('#importe').val()
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                registrarGasto(result.value);
            }
        });
    });

    // Registrar Gasto
    function registrarGasto(data) {
        $.ajax({
            url: './?action=addexpense',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Gasto registrado correctamente'
                    });
                    // Recargar con los mismos filtros
                    const fecha_inicio = $('#fecha_inicio').val();
                    const fecha_fin = $('#fecha_fin').val();
                    const usuario_id = $('#usuario_id').val();
                    cargarGastos(fecha_inicio, fecha_fin, usuario_id);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function (xhr) {
                Swal.fire('Error', 'Ocurrió un error al registrar', 'error');
            }
        });
    }

    // Editar Gasto
    $(document).on('click', '.btn-editar-gasto', function () {
        const id = $(this).data('id');

        $.get('./?action=getexpensebyid', { id: id }, function (response) {
            if (response.success) {
                Swal.fire({
                    title: 'Editar Gasto',
                    html: `
                            <form id="formEditarGasto">
                                <input type="hidden" id="id_gasto" value="${response.data.id}">
                                <div class="form-group mb-3">
                                    <label class="form-label">Descripción</label>
                                    <input type="text" id="edit_descripcion" class="form-control" 
                                           value="${escapeHtml(response.data.descripcion)}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">N° Comprobante</label>
                                    <input type="text" id="edit_comprobante" class="form-control" 
                                           value="${escapeHtml(response.data.comprobante)}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Importe (S/)</label>
                                    <input type="number" step="0.01" id="edit_importe" class="form-control" 
                                           value="${response.data.importe}" required>
                                </div>
                            </form>
                        `,
                    showCancelButton: true,
                    confirmButtonText: 'Actualizar',
                    cancelButtonText: 'Cancelar',
                    focusConfirm: false,
                    preConfirm: () => {
                        return {
                            id: $('#id_gasto').val(),
                            descripcion: $('#edit_descripcion').val(),
                            comprobante: $('#edit_comprobante').val(),
                            importe: $('#edit_importe').val()
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        actualizarGasto(result.value);
                    }
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        }, 'json');
    });

    // Actualizar Gasto
    function actualizarGasto(data) {
        $.ajax({
            url: './?action=updateexpense',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Gasto actualizado correctamente'
                    });
                    // Recargar con los mismos filtros
                    const fecha_inicio = $('#fecha_inicio').val();
                    const fecha_fin = $('#fecha_fin').val();
                    const usuario_id = $('#usuario_id').val();
                    cargarGastos(fecha_inicio, fecha_fin, usuario_id);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Error al actualizar el gasto', 'error');
            }
        });
    }

    // Eliminar Gasto
    $(document).on('click', '.btn-eliminar-gasto', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar este gasto?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'ajax/eliminar_gasto.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { id: id },
                    success: function (response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: 'Gasto eliminado'
                            });
                            // Recargar con los mismos filtros
                            const fecha_inicio = $('#fecha_inicio').val();
                            const fecha_fin = $('#fecha_fin').val();
                            const usuario_id = $('#usuario_id').val();
                            cargarGastos(fecha_inicio, fecha_fin, usuario_id);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Error al eliminar el gasto', 'error');
                    }
                });
            }
        });
    });
});