 // Inicializar jsPDF
 const { jsPDF } = window.jspdf;

 // Función para aplicar márgenes
 function applyMargins(doc, marginLeft, marginTop, marginRight, marginBottom) {
     doc.margins = { left: marginLeft, top: marginTop, right: marginRight, bottom: marginBottom };
     return doc;
 }

 // Función para calcular la altura dinámica del PDF
function calcularAlturaDinamica(datosVenta) {
    let alturaEstimada = 80;
    alturaEstimada += (datosVenta.detalles.length * 5) + 20;
    alturaEstimada += 45;
    alturaEstimada += 50;
    return Math.max(200, alturaEstimada);
}

 // Función para generar el ticket
 $('#imprimir80mm').on('click', async () => {
    var dstosVenta = JSON.parse($("#datosComprobante").val());

    // Calcular altura dinámica
    const alturaCalculada = calcularAlturaDinamica(dstosVenta);
     const doc = new jsPDF({
         orientation: 'portrait',
         unit: 'mm',
         format: [80, alturaCalculada]
     });

     applyMargins(doc, 2, 5, 2, 5);

     doc.setFont("helvetica");
     doc.setFontSize(6.5);

     const logoEmpresa = (dstosVenta.empresa && dstosVenta.empresa.Emp_Logo)
         ? BASE_URL + '/storage/images/' + dstosVenta.empresa.Emp_Logo
         : BASE_URL + '/assets/img/logo2.jpg';
     doc.addImage(logoEmpresa, 'PNG', 15, 2, 50, 15);

     // Agregar datos empresa
     doc.text(dstosVenta.empresa.Emp_RazonSocial, 40, 20,{ align: 'center', fontStyle: 'bold'});
     doc.text(dstosVenta.empresa.Emp_Direccion+" | Cel.: "+dstosVenta.empresa.Emp_Telefono, 40, 23, { align: 'center', fontStyle: 'bold'});
     doc.text("Correo: "+dstosVenta.empresa.Emp_Celular, 27, 26);
     
     //Tipo documento
     var docLabel = (dstosVenta.comp_cab.TIPO_DOC == 3) ? "BOLETA ELECTRONICA" : "FACTURA ELECTRONICA";

     doc.autoTable({
         startY: 29,
         startX: 10,
         head: [],
         body: [
             ["RUC: "+dstosVenta.empresa.Emp_Ruc],
             [docLabel],
             [dstosVenta.venta.SERIE+" - "+dstosVenta.venta.COMPROBANTE]
         ],
         theme: 'grid',
         styles: { 
             fontSize: 6.5,
             cellPadding: 2,
             textColor: [0, 0, 0]
         },
         bodyStyles: {
             fillColor: [255, 255, 255],
             textColor: [0, 0, 0],
             fontStyle: 'bold'
         },
         columnStyles: {
             0: { cellWidth: 60, halign: 'center' }
         },
         didDrawCell: (data) => {
             if (data.row.index === 1) {
                 doc.setFillColor(0, 0, 0);
                 doc.setTextColor(255, 255, 255);
                 doc.rect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, 'F');
                 doc.text(data.cell.raw, data.cell.x + 30, data.cell.y + 4,{ align: 'center', fontStyle: 'bold'});
             }
         },
         margin: { left: 10, right: 10 },
         tableWidth: 'wrap'
     });

     var nomLabel = (dstosVenta.comp_cab.TIPO_DOC == 3) ? "SEÑOR(ES)        " : "RAZÓN SOCIAL  ";
     var docLabel = (dstosVenta.comp_cab.TIPO_DOC == 3) ? "DNI N°" : "RUC";

     let fechaEmisionFormateada = formatearFecha(dstosVenta.comp_cab.fecEmision);

     doc.text("FECHA EMISION: " + fechaEmisionFormateada+"  "+dstosVenta.comp_cab.horEmision, 5, 55);
     doc.text(nomLabel + ": " + dstosVenta.comp_cab.rznSocialUsuario, 5, 58);
     doc.text(docLabel + "                     : " + dstosVenta.comp_cab.numDocUsuario, 5, 61);
     doc.text("DIRECCIÓN        : " + (dstosVenta.comp_aca.desDireccionCliente || '-'), 5, 64);

     // Datos de la tabla
     const datosTabla = dstosVenta.detalles;

     doc.autoTable({
         startY: doc.margins.top + 63,
         startX: doc.margins.left,
         head: [['CANT.', 'DESCRIPCIÓN', 'IMPORTE', 'TOTAL']],
         body: datosTabla.map(item => [item.ctdUnidadItem, item.desItem, Number(item.mtoValorUnitario).toFixed(2), item.mtoValorVentaItem]),
         theme: 'grid',
         styles: { fontSize: 6.5, fontStyle: 'bold' },
         headStyles: { fillColor: [0, 0, 0], textColor: [255, 255, 255] },
         columnStyles: {
             0: { cellWidth: 10 },
             1: { cellWidth: 35 },
             2: { cellWidth: 14, halign: 'right' },
             3: { cellWidth: 14, halign: 'right' }
         },
         margin: { left: 3, right: 3 },
         tableWidth: 'wrap'
     }); 

     const finalY = doc.lastAutoTable.finalY + 5;

     // Generar código QR
     const qrData = dstosVenta.empresa.Emp_Ruc + "|" + dstosVenta.venta.TIPO + "|" + dstosVenta.venta.SERIE + "-"+dstosVenta.venta.COMPROBANTE + "|0.00|" + dstosVenta.comp_cab.sumImpVenta + "|" + dstosVenta.comp_cab.fecEmision+" "+ dstosVenta.comp_cab.horEmision + "|";
     const qrCanvas = document.createElement('canvas');

     QRCode.toCanvas(qrCanvas, qrData, { width: 50, margin: 1 }, (error) => {
         if (error) {
             console.error("Error al generar el QR:", error);
             return;
         }

         const qrImage = qrCanvas.toDataURL('image/png');

         // Impuestos dinámicos
         const impExo = dstosVenta.totales_impuestos ? parseFloat(dstosVenta.totales_impuestos.exonerado) : parseFloat(dstosVenta.comp_cab.sumTotValVenta || 0);
         const impIna = dstosVenta.totales_impuestos ? parseFloat(dstosVenta.totales_impuestos.inafecto) : 0.00;
         const impGra = dstosVenta.totales_impuestos ? parseFloat(dstosVenta.totales_impuestos.gravado) : 0.00;
         const impIgv = dstosVenta.totales_impuestos ? parseFloat(dstosVenta.totales_impuestos.igv) : 0.00;

         const datosTablaTotales = [                
             {desc:"OPE.EXONERADA", imp: impExo.toFixed(2)},
             {desc:"OPE.INAFECTA", imp: impIna.toFixed(2)},
             {desc:"OPE.GRAVADA", imp: impGra.toFixed(2)},
             {desc:"IGV", imp: impIgv.toFixed(2)},
             {desc:"IMPORTE TOTAL", imp: parseFloat(dstosVenta.comp_cab.sumImpVenta || dstosVenta.sell.total).toFixed(2)}
         ];

         const datosTablaResumen = [
             [
                 { content: '', rowSpan: 5, styles: { cellWidth: 35 } },
                 "OPE.EXONERADA", datosTablaTotales[0].imp
             ],
             ["OPE.INAFECTA", datosTablaTotales[1].imp],
             ["OPE.GRAVADA", datosTablaTotales[2].imp],
             ["IGV", datosTablaTotales[3].imp],
             ["IMPORTE TOTAL", datosTablaTotales[4].imp],
             [{ content: "SON: " + dstosVenta.numLetra, colSpan: 3, styles: { halign: 'left', fontStyle: 'bold' } }]
         ];

         doc.autoTable({
             startY: finalY,
             startX: doc.margins.left,
             body: datosTablaResumen,
             theme: 'grid',
             styles: {
                 fontSize: 6.5,
                 cellPadding: 2,
                 textColor: [0, 0, 0]
             },
             bodyStyles: {
                 fillColor: [255, 255, 255],
                 textColor: [0, 0, 0],
                 fontStyle: 'bold'
             },
             columnStyles: {
                 0: { cellWidth: 35, halign: 'center' },
                 1: { cellWidth: 25, halign: 'left' },
                 2: { cellWidth: 15, halign: 'right' }
             },
             margin: { left: doc.margins.left, right: doc.margins.right },
             didDrawCell: function (data) {
                 if (data.row.index === 0 && data.column.index === 0) {
                     doc.addImage(qrImage, 'PNG', data.cell.x + 2, data.cell.y + 2, 30, 30);
                 }
             },
             tableWidth: 'wrap'
         });                

         const finalY2 = doc.lastAutoTable.finalY + 5;
         doc.setFont("helvetica", "bold");
         doc.setFontSize(8);
         doc.text("Consulte y/o descargue su comprobante electrónico en \n www.sunat.gob.pe, utilizando su clave SOL", 40, finalY2,{ align: 'center', fontStyle: 'bold', lineHeightFactor: 1, fontSize: 7 });
         doc.text("CAJERO: " + (dstosVenta.cajero).toUpperCase(), 2, finalY2+7,{ align: 'left', fontStyle: 'bold', fontSize: 10 }); 

         let condicionPago = (dstosVenta.sell.forma_pago == 2) ? "FORMA DE PAGO: CRÉDITO" : "FORMA DE PAGO: CONTADO";
         if (dstosVenta.sell.forma_pago == 2 && dstosVenta.sell.fec_vencimiento) {
             condicionPago += " (Vence: " + dstosVenta.sell.fec_vencimiento + ")";
         }
         doc.text(condicionPago, 2, finalY2+10, { align: 'left', fontStyle: 'bold', fontSize: 8 });
         
         let medioPago = '';
         switch (dstosVenta.sell.tipo_pago){
            case '1':
                medioPago = "EFECTIVO";
                break;
            case '2':
                medioPago = "PLIN";
                break;
			case '3':
				medioPago = "YAPE";
				break;
			case '4':
				medioPago = "TARJETA DEBITO";
				break;
			case '5':
				medioPago = "TARJETA CREDITO";
				break;	
			default:
				medioPago = "OTRO MEDIO DE PAGO";
				break;				
		}

         if(dstosVenta.pagoParcial.id == 0){
           doc.setFont("helvetica", "bold");
           doc.setFontSize(8);
           doc.text("MEDIO " + medioPago +": " + parseFloat(dstosVenta.sell.total).toFixed(2), 2, finalY2+13);  
         }
         else{
            doc.setFont("helvetica", "bold");
            doc.setFontSize(8);
            doc.text("MEDIO " + medioPago +": " + (parseFloat(dstosVenta.sell.total) - parseFloat(dstosVenta.pagoParcial.importepp)).toFixed(2), 2, finalY2+13);
            doc.text("PAGO EFECTIVO: " + (dstosVenta.pagoParcial.importepp), 2, finalY2+16); 
         }

         const pdfData = doc.output('datauristring');

        Swal.fire({
            title: '<h5>Comprobante: '+ dstosVenta.venta.SERIE + '-'+dstosVenta.venta.COMPROBANTE + '  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha de Emisión: ' + fechaEmisionFormateada + ' ' + dstosVenta.comp_cab.horEmision + '</h5>',
            html: `
                <iframe 
                    src="${pdfData}" 
                    width="100%" 
                    height="550px" 
                    style="border: none;"
                ></iframe>
            `,
            showCloseButton: true,
            showConfirmButton: false,
            width: '60%',
        });
     }); 
 });