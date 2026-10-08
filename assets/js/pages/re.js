let currentReType = '1'; // Valor por defecto: Factura

function refreshReCart() {
    $.ajax({
        url: "./?action=re_cart_table&t=" + new Date().getTime(),
        type: "GET",
        cache: false,
        success: function (data) {
            $("#re-cart-container").html(data);
            
            // Re-aplicar el tipo de comprobante seleccionado arriba
            $(`#opt${currentReType}`).prop('checked', true).trigger('change');
            
            // Inicializar Select2
            if ($.fn.select2) {
                $('.select2bs4').select2({
                    theme: 'bootstrap4'
                });
            }
        }
    });
}

function removeItemRe(productId) {
    Swal.fire({
        title: '¿Eliminar del reabastecimiento?',
        text: 'Se quitará el producto de la lista actual.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get("./?action=clearre", { product_id: productId }, function (data) {
                refreshReCart();
            });
        }
    });
}

$(document).ready(function() {
    // Sincronizar selección de comprobante
    $(document).on('change', 'input[name="optTipoRe"]', function() {
        currentReType = $(this).val();
        $(`#opt${currentReType}`).prop('checked', true).trigger('change');
    });

    // Live Search for Restock
    $("#product_code2").on("input", function () {
        let searchTerm = $(this).val().trim();

        if (searchTerm == "") {
            $("#search-results-container").html("");
            return;
        }

        if (searchTerm.length >= 2) {
            $.get("./?action=searchproduct_re", { product: searchTerm }, function (data) {
                $("#search-results-container").html(data);
            });
        } else {
            $("#search-results-container").html("");
        }
    });

    // Intercept click to add to re-cart
    $(document).on('click', '.btn-add-to-re', function (e) {
        const btn = $(this);
        const row = btn.closest('.row-re-product');
        
        const data = {
            product_id: row.data('id'),
            f_price_in: row.find('.re-price-in').val(),
            q: row.find('.re-q').val(),
            rs: row.find('.re-rs').val(),
            nl: row.find('.re-nl').val(),
            fec_fab: row.find('.re-fec-fab').val(),
            fec_venc: row.find('.re-fec-venc').val(),
            labo: row.find('.re-labo').val(),
            anaquel: row.find('.re-anaquel').val(),
            is_controlled: row.find('.re-controlled').is(':checked') ? 1 : 0
        };

        if (!data.q || data.q <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Ingrese una cantidad válida mayor a cero.'
            });
            row.find('.re-q').focus();
            return;
        }

        $.ajax({
            type: "POST",
            url: "./?action=addtore_ajax",
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
                    refreshReCart();
                    // Clear search in modal
                    if ($("#product_code_re").length) {
                        $("#product_code_re").val("").focus();
                        $("#show_search_results_re").html("");
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red',
                    text: 'No se pudo comunicar con el servidor.'
                });
            }
        });
    });

    $("#searchForm").on("submit", function(e) {
        e.preventDefault();
    });

});