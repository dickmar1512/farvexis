 // Inicializar jsPDF
 //const { jsPDFA5 } = window.jspdf;

 // Función para aplicar márgenes
 function applyMargins(doc, marginLeft, marginTop, marginRight, marginBottom) {
     // Guardar los márgenes en el objeto doc para usarlos luego
     doc.margins = { left: marginLeft, top: marginTop, right: marginRight, bottom: marginBottom };
     return doc;
 }

 $('#imprimirA5').on('click', async () => {
    /*Swal.fire({
        title: '¡Advertencia!',
        text: 'No tiene acceso a este formato de impresión',
        icon: 'info', // Puede ser 'success', 'error', 'warning', 'info', 'question'
        confirmButtonText: 'Aceptar'
    });*/
    var dstosVenta = JSON.parse($("#datosComprobante").val());      
    // Crear un nuevo documento PDF
    const doc = new jsPDF({
        orientation: 'portrait', // Orientación vertical
        unit: 'mm', // Unidad en milímetros
        format: 'a5' // Tamaño A5 (148mm × 210mm)
    });

    // Aplicar márgenes personalizados (ajustados para A5)
    applyMargins(doc, 10, 15, 10, 15); // Márgenes más amplios para A5

    // Configurar la fuente y el tamaño (aumentado ligeramente para A5)
    doc.setFont("helvetica");
    doc.setFontSize(8); // Tamaño de fuente aumentado

    // const imgLogo = 'assets/img/logo2.jpg';
    // doc.addImage(imgLogo, 'PNG', 50, 5, 50, 15); // Imagen centrada y más grande

    // // Agregar datos empresa (centrados y con más espacio)
    // doc.text(dstosVenta.empresa.Emp_RazonSocial, 74, 25, {align: 'center', fontStyle: 'bold'});
    // doc.text(dstosVenta.empresa.Emp_Direccion+" | Cel.: "+dstosVenta.empresa.Emp_Telefono, 74, 30, {align: 'center', fontStyle: 'bold'});
    // doc.text("Correo: "+dstosVenta.empresa.Emp_Celular, 74, 35, {align: 'center'});

    // // Tipo documento
    // var docLabel = (dstosVenta.comp_cab.TIPO_DOC== 3) ? "BOLETA ELECTRONICA" : "FACTURA ELECTRONICA";

    // // Crear una tabla con 3 filas (más ancha)
    // doc.autoTable({
    //     startY: 40,
    //     startX: 20,
    //     head: [],
    //     body: [
    //         ["RUC: "+dstosVenta.empresa.Emp_Ruc],
    //         [docLabel],
    //         [dstosVenta.venta.SERIE+" - "+dstosVenta.venta.COMPROBANTE]
    //     ],
    //     theme: 'grid',
    //     styles: { 
    //         fontSize: 8, // Fuente más grande
    //         cellPadding: 3, // Más espacio interno
    //         textColor: [0, 0, 0]
    //     },
    //     bodyStyles: {
    //         fillColor: [255, 255, 255],
    //         textColor: [0, 0, 0],
    //         fontStyle: 'bold'
    //     },
    //     columnStyles: {
    //         0: { cellWidth: 100, halign: 'center' } // Columna más ancha
    //     },
    //     didDrawCell: (data) => {
    //         if (data.row.index === 1) {
    //             doc.setFillColor(0, 0, 0);
    //             doc.setTextColor(255, 255, 255);
    //             doc.rect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, 'F');
    //             doc.text(data.cell.raw, data.cell.x + 50, data.cell.y + 5, {align: 'center', fontStyle: 'bold'});
    //         }
    //     },
    //     margin: { left: 20, right: 20 },
    //     tableWidth: 'wrap'
    // });
    const logoEmpresa = (dstosVenta.empresa && dstosVenta.empresa.Emp_Logo)
        ? BASE_URL + '/storage/images/' + dstosVenta.empresa.Emp_Logo
        : BASE_URL + '/assets/img/logo2.jpg';

    // Datos para la tercera columna (documento)
    const docType = (dstosVenta.comp_cab.TIPO_DOC == 3) ? 'BOLETA ELECTRONICA' : 'FACTURA ELECTRONICA';
    const docInfo = [
        'RUC: ' + dstosVenta.empresa.Emp_Ruc,
        docType,
        dstosVenta.venta.SERIE + ' - ' + dstosVenta.venta.COMPROBANTE
    ];

    // Crear una tabla con 1 fila y 3 columnas
    doc.autoTable({
        startY: 20,
        startX: 10,
        head: [],
        body: [
            [
                { content: '', rowSpan: 3 }, // Celda para el logo
                { 
                    content: [
                        { content: dstosVenta.empresa.Emp_RazonSocial, styles: { fontStyle: 'bold' } },
                        dstosVenta.empresa.Emp_Direccion + ' | Cel.: ' + dstosVenta.empresa.Emp_Telefono,
                        'Correo: ' + dstosVenta.empresa.Emp_Celular
                    ],
                    styles: { halign: 'center' }
                },
                { 
                    content: docInfo,
                    styles: { halign: 'center', fontStyle: 'bold' }
                }
            ]
        ],
        theme: 'plain',
        styles: { 
            fontSize: 8,
            cellPadding: 2,
            textColor: [0, 0, 0],
            lineColor: [255, 255, 255]
        },
        bodyStyles: {
            fillColor: [255, 255, 255],
            textColor: [0, 0, 0]
        },
        columnStyles: {
            0: { cellWidth: 40, halign: 'center' },
            1: { cellWidth: 60, halign: 'center' },
            2: { cellWidth: 60, halign: 'center' }
        },
        didDrawCell: function(data) {
            // Dibujar el logo
            if (data.column.index === 0 && data.row.index === 0) {
                doc.addImage(logoEmpresa, 'PNG', data.cell.x + 5, data.cell.y + 5, 30, 15);
            }
            
            // Aplicar estilo al tipo de documento (segundo elemento de la tercera columna)
            if (data.column.index === 2 && data.row.index === 0) {
                // Verificamos si estamos en la línea del tipo de documento
                const lineHeight = 8; // Altura aproximada de cada línea
                const docTypeY = data.cell.y + 5 + (lineHeight * 1); // Posición Y del tipo de doc
                
                doc.setFillColor(0, 0, 0);
                doc.setTextColor(255, 255, 255);
                doc.rect(data.cell.x, docTypeY - 3, data.cell.width, 10, 'F');
                doc.text(docType, data.cell.x + data.cell.width/2, docTypeY + 2, 
                        { align: 'center', fontStyle: 'bold' });
            }
        },
        margin: { left: 10, right: 10 },
        tableWidth: 'wrap'
    });

    var nomLabel = (dstosVenta.comp_cab.TIPO_DOC== 3) ? "SEÑOR(ES)        " : "RAZÓN SOCIAL  ";
    var docLabel = (dstosVenta.comp_cab.TIPO_DOC== 3) ? "DNI N°                  " : "RUC";

    doc.text("FECHA EMISION: " + dstosVenta.comp_cab.fecEmision+" | "+dstosVenta.comp_cab.horEmision, 15, 70);
    doc.text(nomLabel + ": " + dstosVenta.comp_cab.rznSocialUsuario, 15, 75);
    doc.text(docLabel + "                     : " + dstosVenta.comp_cab.numDocUsuario, 15, 80);
    doc.text("DIRECCIÓN        : " + dstosVenta.comp_aca.desDireccionCliente, 15, 85);

    // Datos de la tabla
    const datosTabla = dstosVenta.detalles;

    // Crear la tabla con jspdf-autotable (más ancha)
    doc.autoTable({
        startY: 90,
        startX: 15,
        head: [['CANT.', 'DESCRIPCIÓN', 'IMPORTE', 'TOTAL']],
        body: datosTabla.map(item => [item.ctdUnidadItem, item.desItem, item.mtoValorUnitario, item.mtoValorVentaItem]),
        theme: 'grid',
        styles: { fontSize: 8, fontStyle: 'bold' },
        headStyles: { fillColor: [0, 0, 0], textColor: [255, 255, 255] },
        columnStyles: {
            0: { cellWidth: 15 }, // Más ancho
            1: { cellWidth: 70 }, // Más ancho para descripción
            2: { cellWidth: 20, halign: 'right' },
            3: { cellWidth: 20, halign: 'right' }
        },
        margin: { left: 15, right: 15 },
        tableWidth: 'wrap'
    }); 

    const finalY = doc.lastAutoTable.finalY + 10; // Más espacio después de la tabla

    // Generar el código QR (más grande)
    const qrData = dstosVenta.empresa.Emp_Ruc + "|" + dstosVenta.venta.TIPO + "|" + dstosVenta.venta.SERIE + "-"+dstosVenta.venta.COMPROBANTE + "|0.00|" + dstosVenta.comp_cab.sumImpVenta + "|" + dstosVenta.comp_cab.fecEmision+" "+ dstosVenta.comp_cab.fecEmision + "|";
    const qrCanvas = document.createElement('canvas');

    QRCode.toCanvas(qrCanvas, qrData, { width: 80, margin: 1 }, (error) => {
        if (error) {
            console.error("Error al generar el QR:", error);
            return;
        }

        const qrImage = qrCanvas.toDataURL('image/png');
        
        // Totales
        const datosTablaTotales = [                
            {desc:"OPE.EXONERADA", imp: dstosVenta.comp_cab.sumTotValVenta},
            {desc:"OPE.INAFECTA", imp:0.00},
            {desc:"OPE.GRAVADA", imp:0.00},
            {desc:"IGV", imp:0.00},
            {desc:"IMPORTE TOTAL",imp: dstosVenta.comp_cab.sumImpVenta}
        ];

        // Tabla resumen más ancha
        const datosTablaResumen = [
            [
                { content: '', rowSpan: 5, styles: { cellWidth: 50 } }, // Espacio más grande para QR
                "OPE.EXONERADA", datosTablaTotales[0].imp
            ],
            ["OPE.INAFECTA", datosTablaTotales[1].imp.toFixed(2)],
            ["OPE.GRAVADA", datosTablaTotales[2].imp.toFixed(2)],
            ["IGV", datosTablaTotales[3].imp.toFixed(2)],
            ["IMPORTE TOTAL", datosTablaTotales[4].imp],
            [{ content: "SON: " + dstosVenta.numLetra, colSpan: 3, styles: { halign: 'left', fontStyle: 'bold' } }]
        ];

        doc.autoTable({
            startY: finalY,
            startX: 15,
            body: datosTablaResumen,
            theme: 'grid',
            styles: {
                fontSize: 8,
                cellPadding: 3,
                textColor: [0, 0, 0]
            },
            bodyStyles: {
                fillColor: [255, 255, 255],
                textColor: [0, 0, 0],
                fontStyle: 'bold'
            },
            columnStyles: {
                0: { cellWidth: 50, halign: 'center' }, // Columna del QR más ancha
                1: { cellWidth: 40, halign: 'left' }, // Descripción más ancha
                2: { cellWidth: 25, halign: 'right' } // Monto más ancho
            },
            margin: { left: 15, right: 15 },
            didDrawCell: function (data) {
                if (data.row.index === 0 && data.column.index === 0) {
                    doc.addImage(qrImage, 'PNG', data.cell.x + 5, data.cell.y + 5, 40, 40); // QR más grande
                }
            },
            tableWidth: 'wrap'
        });                

        const finalY2 = doc.lastAutoTable.finalY + 10;
        doc.text("Consulte y/o descargue su comprobante electrónico en \n www.sunat.gob.pe, utilizando su clave SOL", 
                74, finalY2, {align: 'center', fontStyle: 'bold', lineHeightFactor: 1.5, fontSize: 9});
        doc.text("CAJERO: " + dstosVenta.cajero, 15, finalY2+15, {align: 'left', fontStyle: 'bold', fontSize: 12});    

        // Generar el PDF
        const pdfData = doc.output('datauristring');

        // Abrir el modal (ajustado para A5)
        Swal.fire({
            title: '<h3>Comprobante: '+ dstosVenta.venta.SERIE + '-'+dstosVenta.venta.COMPROBANTE + '  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha de Emisión: ' + dstosVenta.comp_cab.fecEmision + ' ' + dstosVenta.comp_cab.horEmision + '</h3>',
            html: `
                <iframe 
                    src="${pdfData}" 
                    width="100%" 
                    height="600px" 
                    style="border: none;"
                ></iframe>
            `,
            showCloseButton: true,
            showConfirmButton: false,
            width: '70%', // Modal más ancho para A5
        });
    });
 });