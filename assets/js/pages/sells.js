const fechaDefault = new Date();
const opciones = {day: '2-digit', month: '2-digit', year: 'numeric'};

$(document).ready(function() {
    $('input[name="sd"]').val(fechaDefault.toLocaleDateString('es-ES', opciones));
    $('input[name="ed"]').val(fechaDefault.toLocaleDateString('es-ES', opciones));
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        loadSellsData();
    });

    // Cargar datos iniciales    
    loadSellsData();
    loadUsers();
});

// Función para cargar datos en la tabla
function loadSellsData() {
    // Mostrar carga
    $('#loading').show();
    
    // Obtener parámetros del formulario
    const params = {
        sd: ($('input[name="sd"]').val() == undefined) ? fechaDefault.toLocaleDateString('es-ES', opciones) : $('input[name="sd"]').val(),
        ed: ($('input[name="ed"]').val() == undefined) ? fechaDefault.toLocaleDateString('es-ES', opciones) : $('input[name="ed"]').val(),
        user_id: $('select[name="user_id"]').val()
    };
    
    // Validar fechas
    if(!params.sd || !params.ed) {
        console.log('Por favor seleccione ambas fechas');
        $('#loading').hide();
        return;
    }
    
    // Verificar si DataTable ya existe y destruirlo si es necesario
    if ($.fn.DataTable.isDataTable('#sellsnew')) {
        $('#sellsnew').DataTable().destroy();
    }
    
    // Inicializar DataTable
    let dataTable = $('#sellsnew').DataTable({
        "responsive": true, 
        "lengthChange": true,
        "autoWidth": false,
        "processing": true,
        "serverSide": false,
        "destroy": true, // Permite reinicializar la tabla
        "dom": '<"row"<"col-md-3"l><"col-md-6 text-center"B><"col-md-3"f>>rtip',
        "buttons": [
            { 
                extend: "copy", 
                text: "Copiar",
                className: "btn btn-primary btn-sm"
            }, 
            { 
                extend: "csv", 
                text: "CSV",
                className: "btn btn-primary btn-sm"
            }, 
            { 
                extend: "excel", 
                text: "Excel",
                className: "btn btn-primary btn-sm",
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 7, 9] // Mismas columnas que PDF
                },
                filename: function() {
                    return "Reporte_Ventas_" + new Date().toLocaleDateString().replace(/\//g, '-');
                },
                title: "Reporte de Ventas - " + new Date().toLocaleDateString()
            }, 
            { 
                extend: "pdf", 
                text: "PDF",
                className: "btn btn-primary btn-sm",
                orientation: 'landscape', // Orientación horizontal
                exportOptions: {
                    columns: [ 1, 2, 3, 4, 5, 7, 9], // Excluye la columna 3 (índice 3)
                    // Otra alternativa:
                    // columns: ':not(:nth-child(4))' // (Usando selector jQuery)
                },
                pageSize: 'A4', // Tamaño de página A4  
                customize: function (doc) {
                    // Estilo para las celdas de cabecera en el PDF
                    doc.styles.tableHeader = {
                        fillColor: '#000000', // Fondo negro
                        color: '#ffffff', // Texto blanco
                        bold: true,
                        fontSize: 10
                    };
                    // Configuración de márgenes (en puntos)
                    doc.pageMargins = [40, 60, 40, 60]; // [izq, arriba, der, abajo]
                    
                    // Estilo para el título (si usas title)
                    doc.content[0].text = "Reporte de Ventas - " + new Date().toLocaleDateString();
                    doc.content[0].alignment = 'center';
                    doc.content[0].fontSize = 16;
                    doc.content[0].margin = [0, 0, 0, 20];
                    doc.content[1].table.widths = ['*', 'auto', '*', '*', '*', '*', '*']; // Ajustar anchos de columnas
                    doc.content[1].layout = {
                        hLineWidth: function(i, node) { return 0.5; }, // Líneas horizontales
                        vLineWidth: function(i, node) { return 0.5; }, // Líneas verticales
                    };
                    doc.content[1].table.body.forEach(function (row) {
                        row.forEach(function (cell) {
                            cell.style = 'fontSize: 10;'; // Estilo de fuente
                        });
                    });
                }
            }, 
            { 
                extend: "print", 
                text: "Imprimir",
                className: "btn btn-primary btn-sm"
            }, 
            { 
                extend: "colvis", 
                text: "Columnas",
                className: "btn btn-primary btn-sm"
            }
        ],
        "columnDefs": [
            {
                "targets": 3, // Columna de importe (index 3)
                "className": "dt-body-right" // Alinea a la derecha
            },
            {
                "targets": 8, // Columna de acciones (index 8)
                "className": "dt-body-center" // Centra el contenido
            }
        ],
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "No se encontraron resultados",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "copyTitle": "Copiado al portapapeles",
                "copySuccess": {
                    _: "Se copiaron %d filas",
                    1: "Se copió 1 fila"
                },
                "print": "Imprimir",
                "csv": "CSV",
                "excel": "Excel",
                "pdf": "PDF",
                "colvis": "Columnas visibles"
            }
        }
    });

    // Realizar petición AJAX
    $.ajax({
        url: './?action=getSells',
        type: 'GET',
        dataType: 'json',
        data: params,
        beforeSend: function () {
                $('#sellsnew tbody').html('<tr><td colspan="10" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
                $('#total-ventas').text('0.00'); 
                $('#total-plin').text('0.00');
                $('#total-yape').text('0.00');
                $('#total-tdebito').text('0.00');
                $('#total-tcredito').text('0.00');
                $('#total-efectivo').text('0.00');
                $('#subtotal-otros').text('0.00');
                $('#subtotal-efectivo').text('0.00');
                $('#total-capital').text('0.00');
                $('#total-ganancia').text('0.00');
            },
        success: function(response) {           
            // Agregar nuevos datos
            if(response.success){
                // Limpiar y agregar nuevos datos
                dataTable.clear();
                
                if(response.data && response.data.length > 0) {
                    response.data.forEach(venta => {
                        const importeFormateado = parseFloat(venta.importe || 0).toFixed(2);
                        const verBtns = [venta.verComprobante].filter(Boolean).join(' ');
                        const acciones = [venta.descargarXML, venta.descargarCDR, venta.enviarSunat].filter(Boolean).join(' ');

                        var rowNode = dataTable.row.add([
                            verBtns,
                            venta.comprobante || '',
                            venta.cliente || '',
                            importeFormateado,
                            venta.medioPago || '',
                            venta.fecha || '',
                            venta.fechaEnvio || '',
                            venta.estado || '',
                            acciones,
                            venta.usuario || ''
                        ]).node();
                        
                        if (venta.background && venta.background !== '#FFFFFF' && venta.background !== '') {
                            $(rowNode).css('background-color', venta.background);
                        }
                    });
                 }
                
                // Redibujar la tabla con los nuevos datos
                dataTable.draw(false);
                
                var totalotros = parseFloat(response.total_plin)+parseFloat(response.total_yape)+parseFloat(response.total_tdebito)+parseFloat(response.total_tcredito);
                // Actualizar totales
                $('#total-ventas').text(parseFloat(response.total_ventas).toFixed(2));
                $('#total-plin').text(response.total_plin);
                $('#total-yape').text(response.total_yape);
                $('#total-tdebito').text(response.total_tdebito);
                $('#total-tcredito').text(response.total_tcredito);
                $('#total-efectivo').text((parseFloat(response.total_ventas)-totalotros).toFixed(2));
                $('#subtotal-otros').text(totalotros.toFixed(2));
                $('#subtotal-efectivo').text((parseFloat(response.total_ventas)-totalotros).toFixed(2));
                $('#total-capital').text(response.total_capital);
                $('#total-ganancia').text(response.total_ganancia);

                if(parseFloat(response.total_ventas)>0){
                    $('#resumenIngresos').show();
                }else{
                    $('#resumenIngresos').hide();
                }

            }else{
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            Swal.fire('Error', 'Error al cargar las ventas', 'error');
        },
        complete: function() {
            $('#loading').hide();
        }
    });
}

function loadUsers() {
    $.ajax({
        url: './?action=getallusers',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            console.log('Users loaded successfully:', data);
            var users = data;
            var usersSelect = $('#user_id');
            usersSelect.empty();
            usersSelect.append('<option value="0">TODOS</option>');
            users.forEach(function (user) {
                usersSelect.append($('<option>', {
                    value: user.id,
                    text: user.name
                }));
            });
        },
        error: function (xhr, status, error) {
            console.error('Error loading products:', error);
        }
    });
}

function enviarSunatVenta(sellId) {
    Swal.fire({
        title: '¿Enviar a SUNAT?',
        text: 'Se enviará el comprobante seleccionado a SUNAT.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) {
            return;
        }

        Swal.fire({
            title: 'Enviando a SUNAT...',
            text: 'Por favor espere un momento',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        $.post('./?action=send_sunat_ajax', { id: sellId }, function (response) {
            if (response.exito) {
                Swal.fire('¡Éxito!', response.descripcion || 'Comprobante enviado a SUNAT.', 'success')
                    .then(function () { loadSellsData(); });
            } else {
                Swal.fire('Atención', response.descripcion || 'No se pudo enviar a SUNAT.', 'warning')
                    .then(function () { loadSellsData(); });
            }
        }, 'json').fail(function () {
            Swal.fire('Error', 'Error de comunicación con el servidor.', 'error');
        });
    });
}