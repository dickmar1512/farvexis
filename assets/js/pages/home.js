
function chartdonutTop6(ctx, producto1, producto2, producto3, producto4, producto5, producto6, producto1venta, producto2venta, producto3venta, producto4venta, producto5venta, producto6venta) {
    var myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [
                producto1,
                producto2,
                producto3,
                producto4,
                producto5,
                producto6
            ],
            datasets: [{
                label: 'Ventas',
                data: [
                    producto1venta,
                    producto2venta,
                    producto3venta,
                    producto4venta,
                    producto5venta,
                    producto6venta
                ],
                backgroundColor: [
                    '#030340',
                    '#04DBEE',
                    '#FCE205',
                    '#953ACE',
                    '#0BD264',
                    '#D2D6DE'
                ],
                borderColor: '#ccc',
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.label + ': ' + context.raw + ' ventas';
                        }
                    }
                }
            }
        }
    });
}