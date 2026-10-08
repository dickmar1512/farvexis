// function to refresh the cart UI
function refreshCart() {
    $.ajax({
        url: "./?action=cart_table&t=" + new Date().getTime(),
        type: "GET",
        cache: false,
        success: function (data) {
            $(".cart-container").html(data);
            // Get total from the first hidden input with class js_total_val
            const newTotal = $(".js_total_val").first().val() || 0;
            $("#money, #money2, #money3").val(newTotal);

            let hasControlled = $('.cart_has_controlled').first().val() == '1';
            let controlledNames = $('.controlled_names').first().val();
            
            if (hasControlled) {
                $('.recipe-section').slideDown();
                $('.recipe-products-list').text('Aplica para: ' + controlledNames);
                $('.recipe-section input').prop('required', true);
            } else {
                $('.recipe-section').slideUp();
                $('.recipe-products-list').text('');
                $('.recipe-section input').prop('required', false);
            }
        },
        error: function() {
            console.error("Error refreshing cart");
        }
    });
}

// function to remove item
function removeItem(productId) {
    Swal.fire({
        title: '¿Eliminar producto?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get("./?action=clearcart_ajax", { product_id: productId }, function (data) {
                refreshCart();
            });
        }
    });
}

// function to clear cart
function clearCart() {
    Swal.fire({
        title: '¿Vaciar carrito?',
        text: 'Se eliminarán todos los productos seleccionados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, vaciar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get("./?action=clearcart_ajax", function (data) {
                refreshCart();
            });
        }
    });
}

// script modal sweetAlert2
$(document).ready(function() {
    const initialTotal = $(".js_total_val").first().val() || 0;
    $('#money, #money2, #money3').val(initialTotal);

    // Initial check for controlled products
    let hasControlled = $('.cart_has_controlled').first().val() == '1';
    let controlledNames = $('.controlled_names').first().val();
    if (hasControlled) {
        $('.recipe-section').show();
        $('.recipe-products-list').text('Aplica para: ' + controlledNames);
        $('.recipe-section input').prop('required', true);
    }
});

$(document).on('click', '#btnAgregarItem, #btnAgregarItem2, #btnAgregarItem3, #btnAgregarItemCentral', agregarProducto);

function enviarProductoAlCarrito(form) {
    if (form.data('enviando')) {
        return;
    }

    form.data('enviando', true);
    const url = form.attr('action');
    const data = form.serialize();

    $.ajax({
        type: "POST",
        url: url,
        data: data,
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Producto agregado',
                    showConfirmButton: false,
                    timer: 800,
                    position: 'top-end',
                    toast: true
                });
                refreshCart();
                Swal.close();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
            form.data('enviando', false);
        },
        error: function (xhr, status, error) {
            form.data('enviando', false);
            console.error("AJAX Error (addtocart):", status, error, xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error de red',
                text: 'No se pudo agregar el producto.'
            });
        }
    });
}

$(document).on('submit', '.form-add-to-cart', function (e) {
    e.preventDefault();
    enviarProductoAlCarrito($(this));
});

function agregarProducto() {
    Swal.fire({
        title: '<h4 style="margin: 0; font-size: 1.1em;">Buscar Producto</h4>',
        html: `
            <div style="text-align: left; margin-top: 10px;">
                <form id="searchp">
                    <input type="hidden" name="view" value="sell">
                    <div class="input-icon-container">
                        <input type="text" id="product_code2" name="product" 
                               class="form-control form-control-sm" 
                               placeholder="Nombre o código del producto..."
                               style="padding: 6px 12px; font-size: 0.9em;"
                               autocomplete="off">
                        <div class="icon-wrapper">
                            <i class="icon fas fa-search" style="color: #6c757d; font-size: 0.8em;"></i>
                        </div>
                    </div>
                </form>
                <div id="show_search_results" style="margin-top: 10px;"></div>
            </div>
        `,
        showCloseButton: true,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Cerrar',
        customClass: {
            container: 'custom-swal-container',
            popup: 'custom-swal-popup compact-modal',
            header: 'custom-swal-header',
            title: 'custom-swal-title',
            content: 'custom-swal-content',
            closeButton: 'custom-swal-close-button',
            actions: 'custom-swal-actions',
            cancelButton: 'btn btn-sm btn-secondary'
        },
        width: '75%',
        padding: '15px',
        didOpen: () => {
            const style = document.createElement('style');
            style.textContent = `
                .compact-modal {
                    font-size: 0.9em !important;
                }
                .compact-modal .swal2-header {
                    padding: 10px 15px 5px !important;
                }
                .compact-modal .swal2-content {
                    padding: 5px 15px !important;
                }
                .compact-modal .swal2-actions {
                    padding: 10px 15px 15px !important;
                    margin: 0 !important;
                }
                .compact-modal hr {
                    margin: 8px 0 !important;
                }
            `;
            document.head.appendChild(style);
            
            // Focus manually after a short timeout to avoid focus collision
            setTimeout(() => {
                const input = document.getElementById('product_code2');
                if (input) input.focus();
            }, 100);

            $("#product_code2").on("input", function () {
                let searchTerm = $(this).val().trim();
                if (searchTerm == "") {
                    $("#show_search_results").html("");
                    return;
                }
                if (searchTerm.length >= 3) {
                    $.get("./?action=searchproduct", $("#searchp").serialize(), function (data) {
                        $("#show_search_results").html(data);
                    });
                } else {
                    $("#show_search_results").html("");
                }
            });

            $("#searchp").on("submit", function (e) {
                e.preventDefault();
                $.get("./?action=searchproduct", $("#searchp").serialize(), function (data) {
                    $("#show_search_results").html(data);
                });
                $("#product_code2").val("");
            });
        }
    });
}

$("input[name=optTipoComprobante]").click(function () {
    var optTipoComprobante = $('input:radio[name=optTipoComprobante]:checked').val();
    if (optTipoComprobante == 3) {
        $("#comprobante_boleta").show("slow");
        $("#comprobante_factura").hide("slow");
        $("#comprobante_orden").hide("slow");
    }
    else if (optTipoComprobante == 1) {
        $("#comprobante_boleta").hide("slow");
        $("#comprobante_factura").show("slow");
        $("#comprobante_orden").hide("slow");
    }
    else if (optTipoComprobante == 0) {
        $("#comprobante_boleta").hide("slow");
        $("#comprobante_factura").hide("slow");
        $("#comprobante_orden").show("slow");
    }
});

async function enviado2(tip,event) {
    if(event) event.preventDefault(); 
    let discount = 0;
    let money = 0;
    const optTipoComprobante = $('input:radio[name=optTipoComprobante]:checked').val();
    const total = parseFloat($(".js_total_val").first().val() || 0);

    if (total <= 0) {
        Swal.fire('Error', 'El carrito está vacío', 'error');
        return false;
    }

    if (optTipoComprobante == 1) {
        const ruc = $("#formfactura #ruc").val();
        const razonSocial = $("#formfactura #rznSocialUsuario").val();
        if (!ruc || ruc == "00000000000" || ruc.length !== 11) {
            await Swal.fire({
                title: 'Error en RUC',
                text: 'Ingrese un RUC válido (11 dígitos)',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
            $("#formfactura #ruc").focus();
            return false;
        }

        if (!razonSocial || razonSocial === "Cliente General") {
            await Swal.fire({
                title: 'Error en Razón Social',
                text: 'Ingrese la razón social del cliente',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
            $("#formfactura #rznSocialUsuario").focus();
            return false;
        }
    }

    if (optTipoComprobante == 3) {
        money = parseFloat($("#money").val()) || 0;
        discount = parseFloat($("#discount").val()) || 0;
    } 
    else if (optTipoComprobante == 1) {
        money = parseFloat($("#money2").val()) || 0;
        discount = parseFloat($("#discount2").val()) || 0;
    } 
    else if (optTipoComprobante == 0) {
        money = parseFloat($("#money3").val()) || 0;
        discount = 0;
    }

    if (money < (total - discount)) {
        await Swal.fire({
            title: '¡Advertencia!',
            text: 'No se puede efectuar la operación. Ingrese monto entregado por el cliente',
            icon: 'warning',
            confirmButtonText: 'Aceptar'
        });
        return false;
    }

    const result = await Swal.fire({
        title: '¿El cambio es correcto?',
        html: `Cambio S/: <b>${(money - (total - discount)).toFixed(2)}</b>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false
    });

    if (result.isConfirmed) {
        let actionUrl = '';
        let formData = '';

        if (optTipoComprobante == 3) {
            actionUrl = './?action=addboleta';
            formData = $('#formboleta').serialize();
        } else if (optTipoComprobante == 1) {
            actionUrl = './?action=addfactura';
            formData = $('#formfactura').serialize();
        } else {
            actionUrl = './?action=processsell';
            formData = $('#formnotaventa').serialize();
        }

        Swal.fire({
            title: 'Procesando venta',
            html: 'Generando comprobante y comunicando con SUNAT...<br><span class="text-muted" style="font-size:0.9rem;">Por favor, espere un momento.</span>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' || response.success) {
                    Swal.fire({
                        title: '¡Venta Realizada!',
                        text: response.message || 'Comprobante registrado con éxito.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    Swal.fire({
                        title: 'Error al procesar venta',
                        text: response.message || 'Ocurrió un error inesperado.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(xhr, status, error) {
                let msg = 'Error en la conexión con el servidor.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const json = JSON.parse(xhr.responseText);
                        if (json.message) msg = json.message;
                    } catch (e) {
                        msg = 'Error del servidor: ' + (xhr.statusText || error);
                    }
                }
                Swal.fire({
                    title: 'Error en la operación',
                    text: msg,
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });

        return true;
    }
    return false;
}

$(document).ready(function () {
    $("#searchk").on("submit", function (e) {
        e.preventDefault();
        $.get("./?action=searchkit", $("#searchk").serialize(), function (data) {
            $("#show_search_results").html(data);
        });
        $("#kit_code2").val("");
    });
});

// Handler AJAX para agregar kit al carrito
$(document).on('click', '.btn-add-kit-to-cart', function() {
    var btn = $(this);
    var idpaquete = btn.data('idpaquete');
    Swal.fire({ title: 'Agregando paquete...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
    $.ajax({
        type: 'POST',
        url: './?action=addtocartkit',
        data: { idpaquete: idpaquete },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Paquete agregado', showConfirmButton: false, timer: 800, position: 'top-end', toast: true });
                refreshCart();
                Swal.close();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
            }
        },
        error: function() {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo agregar el paquete.' });
        }
    });
});

$(document).on('keydown', function(event) {
    switch(event.key) {
        case 'F1':
            event.preventDefault();
            agregarProducto();
            break;
        case 'F3':
            event.preventDefault();
            enviado2();
            break;        
        case 'F4':
            event.preventDefault(); 
            document.getElementById('optTipoComprobante1').click();
            break;
        case 'F5':
            event.preventDefault();
            document.getElementById('optTipoComprobante2').click();
            break;
        case 'F6':
            event.preventDefault();
            document.getElementById('optTipoComprobante3').click();
            break;
    }
});

//Para validar documentos
var cuenta = 0;
	function enviado() {
		if (cuenta == 0) {
			cuenta++;
			return true;
		}
		else {
			alert("El formulario ya está siendo enviado, por favor aguarde un instante.");
			return false;
		}
	}

	function validar_dni() {
		valor = document.getElementById("numDocUsuario").value;
		cantidad_digitos = valor.trim().length;

		if (valor != '') {
			if (cantidad_digitos == 8) {
				if (!/^([0-9])*$/.test(valor)) {
					alert('Dni inválido');
					document.getElementById("numDocUsuario").value = "";
				}
				else {
					generar_nombre(valor, 3);
				}
			}
			else {
				alert('Dni inválido');
			}
		}
	}
	
	function validar_no_dni() {
		valor = document.getElementById("numDocUsuario").value;
		cantidad_digitos = valor.trim().length;

		if (valor != '') {
			if (cantidad_digitos >= 3) {
				if (!/^([0-9])*$/.test(valor)) {
					alert('Dni inválido');
					document.getElementById("numDocUsuario").value = "";
				}
				else {
					generar_nombre(valor, 4);
				}
			}
			else {
				alert('Dni inválido');
			}
		}
	}

	function validar_ruc() {
		valor = document.getElementById("ruc").value;
		cantidad_digitos = valor.trim().length;

		if (valor != '') {
			if (cantidad_digitos == 11) {
				if (!/^([0-9])*$/.test(valor)) {
					alert('RUC inválido');
					document.getElementById("ruc").value = "";
				}
				else {
					generar_nombre(valor, 1);
				}
			}
			else {
				alert('RUC inválido');
			}
		}
	}

	function generar_nombre(numDocUsuario, tipo) {
		$.ajax({
			type: "POST",
			data: {
				"numDocUsuario": numDocUsuario,
				"tipo": tipo
			},
			url: './?action=generar_nombre_ajax',

			success: function (rpta) {
				console.log("generar_nombre_ajax: ", rpta);
                console.log("generar_nombre_ajax: ", rpta.data);
				var objDato = rpta.data;
                console.log("objDato: ", objDato.name);
				if (tipo == 1) {					
					$("#comprobante_factura #rznSocialUsuario").val(objDato.name);
					$("#comprobante_factura #codUbigeoCliente").val(objDato.ubigeo);
					$("#comprobante_factura #desDireccionCliente").val(objDato.address1);
				}
				else if (tipo == 3) {
					$("#comprobante_boleta #rznSocialUsuario").val(objDato.name);
					$("#comprobante_boleta #codUbigeoCliente").val(objDato.ubigeo);
					$("#comprobante_boleta #desDireccionCliente").val(objDato.address1);
				}

			},
		});
	}

function toggleCredito(val, tipo) {
    if (val == '2') {
        $('#vencimiento_' + tipo).slideDown();
    } else {
        $('#vencimiento_' + tipo).slideUp();
    }
}

function openModalPaciente(idx) {
    $('#modalPersonaTitle').text('Nuevo Paciente');
    $('#modal_role').val('cliente');
    $('#modal_target_idx').val(idx);
    $('#group_doc').show();
    $('#group_cmp').hide();
    $('#modal_nombres').val($('#paciente_nombre_' + idx).val());
    $('#modal_documento').val($('#paciente_dni_' + idx).val());
    $('#modal_apellidos').val('');
    $('#modalCrearPersona').modal('show');
}

function openModalMedico(idx) {
    $('#modalPersonaTitle').text('Nuevo Médico');
    $('#modal_role').val('medico');
    $('#modal_target_idx').val(idx);
    $('#group_doc').hide();
    $('#group_cmp').show();
    $('#modal_nombres').val($('#medico_nombre_' + idx).val());
    $('#modal_cmp').val($('#medico_cmp_' + idx).val());
    $('#modal_apellidos').val('');
    $('#modalCrearPersona').modal('show');
}

function openModalClienteNV() {
    $('#modalPersonaTitle').text('Nuevo Cliente');
    $('#modal_role').val('cliente');
    $('#modal_target_idx').val('nv');
    $('#group_doc').show();
    $('#group_cmp').hide();
    $('#modal_nombres').val('');
    $('#modal_documento').val('');
    $('#modal_apellidos').val('');
    $('#modalCrearPersona').modal('show');
}

function savePersonaAjax(e) {
    e.preventDefault();
    var formData = $('#formCrearPersona').serialize();
    $.post('./?action=addpersonajax', formData, function(response) {
        if (response.success) {
            var role = $('#modal_role').val();
            var idx = $('#modal_target_idx').val();
            
            if (role === 'cliente') {
                if (idx === 'nv') {
                    $('#nv_client_id').append(new Option(response.name, response.id, true, true));
                } else {
                    $('#paciente_nombre_' + idx).val(response.name);
                    $('#paciente_dni_' + idx).val(response.doc);
                }
            } else if (role === 'medico') {
                $('#medico_nombre_' + idx).val(response.name);
                $('#medico_cmp_' + idx).val(response.cmp);
            }
            
            $('#modalCrearPersona').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Guardado correctamente',
                showConfirmButton: false,
                timer: 1000
            });
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    }, 'json');
}