    <style>
        /* Estilos específicos para impresión térmica */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            @page {
                size: 80mm auto;
                margin: 0;
                padding: 0;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: none !important;
                font-size: 12pt !important;
                line-height: 1.2 !important;
                width: 80mm !important;
            }
            
            .ticket {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 !important;
                padding: 2mm !important;
                box-shadow: none !important;
                font-size: 12pt !important;
                background: white !important;
            }
            
            .header {
                font-size: 14pt !important;
                margin-bottom: 3mm !important;
                padding-bottom: 2mm !important;
            }
            
            .business-name {
                font-size: 16pt !important;
                font-weight: bold !important;
                margin-bottom: 1mm !important;
            }
            
            .business-info {
                font-size: 10pt !important;
                margin-bottom: 0.5mm !important;
            }
            
            .ticket-info {
                font-size: 10pt !important;
                margin-bottom: 3mm !important;
            }
            
            .items-table {
                font-size: 9pt !important;
                margin-bottom: 3mm !important;
                border-collapse: collapse !important;
            }
            
            .items-table th {
                font-size: 8pt !important;
                padding: 1mm !important;
                background-color: #000 !important;
                color: #fff !important;
                border: 1px solid #000 !important;
            }
            
            .items-table td {
                font-size: 9pt !important;
                padding: 1mm !important;
                border: 1px solid #333 !important;
            }
            
            .total-section {
                font-size: 12pt !important;
                margin-bottom: 3mm !important;
            }
            
            .total-final {
                font-size: 14pt !important;
                font-weight: bold !important;
            }
            
            .payment-info {
                font-size: 10pt !important;
                margin-bottom: 3mm !important;
            }
            
            .footer {
                font-size: 9pt !important;
                margin-top: 3mm !important;
                padding-top: 2mm !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .separator {
                border-top: 1px dashed #000 !important;
                margin: 2mm 0 !important;
            }
        }
        
        /* Estilos para vista en pantalla */
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 10px;
            background-color: #f5f5f5;
        }
        
        .ticket {
            width: 80mm;
            max-width: 80mm;
            background: white;
            margin: 0 auto;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-size: 12px;
            line-height: 1.2;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .business-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .business-info {
            font-size: 10px;
            margin-bottom: 2px;
        }
        
        .ticket-info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-family: 'Courier New', monospace;
            font-size: 9px;
        }
        
        .items-table thead {
            background-color: #000;
            color: #fff;
        }
        
        .items-table th {
            padding: 4px 2px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            border: 1px solid #000;
        }
        
        .items-table td {
            padding: 3px 2px;
            border: 1px solid #ccc;
            font-size: 9px;
            vertical-align: top;
        }
        
        .items-table .qty-col {
            width: 15%;
            text-align: center;
        }
        
        .items-table .desc-col {
            width: 45%;
            text-align: left;
        }
        
        .items-table .price-col {
            width: 20%;
            text-align: right;
        }
        
        .items-table .total-col {
            width: 20%;
            text-align: right;
        }
        
        .product-name {
            font-weight: bold;
        }
        
        .product-desc {
            font-size: 8px;
            color: #666;
            font-style: italic;
        }
        
        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        
        .total-section {
            margin-bottom: 15px;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .total-final {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        
        .payment-info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .print-button {
            margin: 20px auto;
            display: block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Encabezado -->
        <div class="header">
            <img src="assets/img/logo.jpg" alt="Logo" style="height: 60px; width: auto; max-width: 70mm;">
            <div class="business-name">EMPRESA DEMO S.A.C.</div>
            <div class="business-info">Av. Principal 123, Lima - Perú</div>
            <div class="business-info">Telf.: (01) 234-5678</div>
            <div class="business-info">Correo: info@empresa.com</div>
        </div>
        
        <!-- Información del ticket -->
        <div class="ticket-info">
            <div><center><b>RUC: 20123456789</b></center></div>
            <div style="background:#000; color: #FFF; padding: 2px;"><center><b>ORDEN DE VENTA</b></center></div>
            <div><center><b>OV001-000123</b></center></div>
        </div>

        <div class="ticket-info">
            <div>Fecha emision: 04/06/2025 14:30</div>
            <div>DNI: 12345678</div>
            <div>SEÑOR(ES): Juan Pérez García</div>
        </div>
        
        <!-- Productos/Servicios -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="qty-col">CANT.</th>
                    <th class="desc-col">DESCRIPCION</th>
                    <th class="price-col">P. UNIT.</th>
                    <th class="total-col">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="qty-col"><b>2</b></td>
                    <td class="desc-col">
                        <b>Producto A</b> | Descripción adicional
                    </td>
                    <td class="price-col"><b>12.50</b></td>
                    <td class="total-col"><b>25.00</b></td>
                </tr>
                <tr>
                    <td class="qty-col"><b>1</b></td>
                    <td class="desc-col">
                        <b>Producto B Premium</b> | Extra información
                    </td>
                    <td class="price-col"><b>45.50</b></td>
                    <td class="total-col"><b>45.50</b></td>
                </tr>
                <tr>
                    <td class="qty-col"><b>1</b></td>
                    <td class="desc-col">
                        <b>Servicio C</b>
                    </td>
                    <td class="price-col"><b>15.00</b></td>
                    <td class="total-col"><b>15.00</b></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Totales -->
        <div class="total-section">
            <div class="total-line total-final">
                <span>TOTAL:</span>
                <span>S/ 85.50</span>
            </div>
        </div>
        
        <!-- Información de pago -->
        <div class="payment-info">
            <div>Método de Pago: Efectivo</div>
        </div>
        
        <!-- Pie de página -->
        <div class="footer">
            <div>¡Gracias por su compra!</div>
            <div>Conserve su ticket</div>
            <div style="margin-top: 10px; font-size: 8px;">
                Representación impresa de la<br>
                ORDEN DE VENTA
            </div>
        </div>
    </div>
    
    <button class="print-button no-print" onclick="printTicket()">
        🖨️ Imprimir Ticket
    </button>
    
    <script>
        function printTicket() {
            // Ocultar elementos que no deben imprimirse
            const elements = document.querySelectorAll('.no-print');
            elements.forEach(el => el.style.display = 'none');
            
            // Configurar ventana de impresión
            window.print();
            
            // Restaurar elementos después de imprimir
            setTimeout(() => {
                elements.forEach(el => el.style.display = 'block');
            }, 1000);
        }
        
        // Función alternativa para impresión directa en impresora térmica
        function printThermal() {
            const printWindow = window.open('', '_blank');
            const ticketContent = document.querySelector('.ticket').outerHTML;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <style>
                        @page { size: 80mm auto; margin: 0; }
                        body { 
                            margin: 0; 
                            padding: 2mm; 
                            font-family: 'Courier New', monospace; 
                            font-size: 12pt;
                            width: 80mm;
                        }
                        .ticket { width: 100%; margin: 0; padding: 0; }
                        .header { text-align: center; margin-bottom: 3mm; }
                        .business-name { font-size: 16pt; font-weight: bold; }
                        .business-info { font-size: 10pt; }
                        .ticket-info { font-size: 10pt; margin-bottom: 3mm; }
                        .items-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
                        .items-table th { background: #000; color: #fff; padding: 1mm; font-size: 8pt; }
                        .items-table td { padding: 1mm; font-size: 9pt; border: 1px solid #333; }
                        .total-section { font-size: 12pt; }
                        .total-final { font-size: 14pt; font-weight: bold; }
                        .footer { text-align: center; font-size: 9pt; margin-top: 3mm; }
                    </style>
                </head>
                <body>${ticketContent}</body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>