$(document).ready(function() {
    // Función para calcular e ilustrar el arqueo de caja en tiempo real
    function calculateTotal() {
        var b200 = parseInt($('#b200').val()) || 0;
        var b100 = parseInt($('#b100').val()) || 0;
        var b50  = parseInt($('#b50').val()) || 0;
        var b20  = parseInt($('#b20').val()) || 0;
        var b10  = parseInt($('#b10').val()) || 0;
        
        var m5   = parseInt($('#m5').val()) || 0;
        var m2   = parseInt($('#m2').val()) || 0;
        var m1   = parseInt($('#m1').val()) || 0;
        
        var c50  = parseInt($('#c50').val()) || 0;
        var c20  = parseInt($('#c20').val()) || 0;
        var c10  = parseInt($('#c10').val()) || 0;
        
        var yape     = parseFloat($('#yape').val()) || 0;
        var plin     = parseFloat($('#plin').val()) || 0;
        var tdebito  = parseFloat($('#tdebito').val()) || 0;
        var tcredito = parseFloat($('#tcredito').val()) || 0;
        
        var totalBilletes = (b200 * 200) + (b100 * 100) + (b50 * 50) + (b20 * 20) + (b10 * 10);
        var totalMonedas = (m5 * 5) + (m2 * 2) + (m1 * 1) + (c50 * 0.5) + (c20 * 0.2) + (c10 * 0.1);
        
        var totalEfectivo = totalBilletes + totalMonedas;
        var totalDigital = yape + plin + tdebito + tcredito;
        var totalArqueo = totalEfectivo + totalDigital;
        
        var expectedTotal = parseFloat($('#totalVentas').val()) || 0;
        var difference = totalArqueo - expectedTotal;
        
        // Actualizar la interfaz con los valores formateados
        $('#totalEfectivoText').text('S/ ' + totalEfectivo.toFixed(2));
        $('#totalDigitalText').text('S/ ' + totalDigital.toFixed(2));
        $('#totalArqueoText').text('S/ ' + totalArqueo.toFixed(2));
        
        var diffText = $('#differenceText');
        diffText.text('S/ ' + difference.toFixed(2));
        
        // Validar si la diferencia cuadra perfectamente (Diferencia = 0.00)
        if (Math.abs(difference) < 0.01 && expectedTotal > 0) {
            diffText.removeClass('text-danger text-warning').addClass('text-success font-weight-bold');
            $('#differenceContainer').css({
                'border-color': '#28a745',
                'background-color': '#e8f5e9',
                'border-style': 'solid'
            });
            $('#procesarVentasBtn').prop('disabled', false).removeClass('disabled');
        } else {
            diffText.removeClass('text-success text-warning').addClass('text-danger font-weight-bold');
            $('#differenceContainer').css({
                'border-color': '#dc3545',
                'background-color': '#fde8e8',
                'border-style': 'dashed'
            });
            $('#procesarVentasBtn').prop('disabled', true).addClass('disabled');
        }
    }
    
    // Listeners para los inputs de denominaciones
    $('#denominations input').on('input change', function() {
        calculateTotal();
    });
    
    // Ejecutar inicialmente
    calculateTotal();
    
    // Manejo de la acción de procesar ventas
    $('#procesarVentasBtn').click(function(e) {
        e.preventDefault();
        
        if ($(this).hasClass('disabled') || $(this).prop('disabled')) {
            return;
        }
        
        const originalText = $(this).html();
        $(this).html('<i class="fa fa-spinner fa-spin mr-1"></i> Procesando...');
        $(this).prop('disabled', true).addClass('disabled');
        
        // Construir datos de denominación y acción para enviar al backend
        const postData = {
            denominations: {
                b200: parseInt($('#b200').val()) || 0,
                b100: parseInt($('#b100').val()) || 0,
                b50: parseInt($('#b50').val()) || 0,
                b20: parseInt($('#b20').val()) || 0,
                b10: parseInt($('#b10').val()) || 0,
                m5: parseInt($('#m5').val()) || 0,
                m2: parseInt($('#m2').val()) || 0,
                m1: parseInt($('#m1').val()) || 0,
                c50: parseInt($('#c50').val()) || 0,
                c20: parseInt($('#c20').val()) || 0,
                c10: parseInt($('#c10').val()) || 0,
                yape: parseFloat($('#yape').val()) || 0,
                plin: parseFloat($('#plin').val()) || 0,
                tdebito: parseFloat($('#tdebito').val()) || 0,
                tcredito: parseFloat($('#tcredito').val()) || 0
            },
            action: 'process_box'
        };
        
        // Envío AJAX
        $.ajax({
            url: './?action=processbox',
            type: 'POST',
            dataType: 'json',
            data: postData,
            success: function(response) {
                if (response.success) {
                    window.location.href = BASE_URL + '/box/' + response.box_id;
                } else {
                    alert('Error: ' + response.message);
                    $('#procesarVentasBtn').html(originalText).prop('disabled', false).removeClass('disabled');
                }
            },
            error: function(xhr, status, error) {
                alert('Error al procesar: ' + error);
                $('#procesarVentasBtn').html(originalText).prop('disabled', false).removeClass('disabled');
            }
        });
    });
});