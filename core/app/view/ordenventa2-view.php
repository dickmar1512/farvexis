<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta</title>
    <style>
        body {
            width: 80mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .ticket {
            width: 80mm;
            padding: 10px;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .items th, .items td {
            padding: 2px 0;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
        }
        .items th {
            border-bottom: 1px dashed #000;
        }
        .total {
            border-top: 1px dashed #000;
            font-weight: bold;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="center bold">
            <div>Farmacia Ejemplo</div>
            <div>RUC: 12345678901</div>
            <div>Av. Principal 123, Ciudad</div>
            <div>Tel: (01) 234-5678</div>
        </div>
        <hr>
        <div>
            <span class="bold">Fecha:</span> <?= date('d/m/Y H:i') ?><br>
            <span class="bold">Ticket N°:</span> 000123
        </div>
        <hr>
        <table class="items">
            <thead>
                <tr>
                    <th>Cant</th>
                    <th>Descripción</th>
                    <th>P.Unit</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>
                <!-- Ejemplo de items, reemplazar por datos dinámicos -->
                <tr>
                    <td>2</td>
                    <td>Paracetamol 500mg</td>
                    <td>2.50</td>
                    <td>5.00</td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Ibuprofeno 400mg</td>
                    <td>3.00</td>
                    <td>3.00</td>
                </tr>
            </tbody>
        </table>
        <table style="width:100%; margin-top:5px;">
            <tr>
                <td class="bold">Total:</td>
                <td class="bold" style="text-align:right;">S/ 8.00</td>
            </tr>
            <tr>
                <td>Efectivo:</td>
                <td style="text-align:right;">S/ 10.00</td>
            </tr>
            <tr>
                <td>Vuelto:</td>
                <td style="text-align:right;">S/ 2.00</td>
            </tr>
        </table>
        <div class="footer">
            ¡Gracias por su compra!<br>
            <span>www.farmaciaejemplo.com</span>
        </div>
    </div>
    <script>
        window.print();
    </script>
</body>
</html>