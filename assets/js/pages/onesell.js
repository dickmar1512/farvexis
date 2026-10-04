$(document).ready(function() {
	// Simular el clic automáticamente al cargar la página
	const botonImprimir = $('#imprimir80mm');
	if (botonImprimir.length) {
		botonImprimir.click();
	}

    $('#actualizarTipoPago').on('click', function() {
        $.ajax({
            url: './?action=updatePaymentType',
            type: 'POST',
            data: {
                paymentId: $('#sellid').val(),
                paymentType: $('#selTipoPago').val(),
                importepp : $('#importeParcial').val()
            },
            success: function(response) {
                response = JSON.parse(response);
                if(response.success === false){
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                    return;
                }else   if(response.success === true){
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message
                    }).then(() => {
                        location.reload(); // Recargar la página después de cerrar la alerta    
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar tipo de pago:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar el tipo de pago. Intente nuevamente.'
                });
            }
        });
    });
});