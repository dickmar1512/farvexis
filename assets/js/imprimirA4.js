const { jsPDF } = window.jspdf;

function getPrintableDataA4() {
    const raw = $('#datosComprobante').val();
    if (!raw) return null;
    try {
        return JSON.parse(raw);
    } catch (error) {
        console.error('No se pudo parsear datosComprobante:', error);
        return null;
    }
}

function addLogoToDocumentA4(doc, empresa, x, y, w, h) {
    const logo = (empresa && empresa.Emp_Logo)
        ? BASE_URL + '/storage/images/' + empresa.Emp_Logo
        : BASE_URL + '/assets/img/logo2.jpg';

    try {
        doc.addImage(logo, 'PNG', x, y, w, h);
    } catch (error) {
        console.warn('No se pudo cargar el logo del documento:', error);
    }
}

$('#imprimirA4').on('click', async () => {
    const data = getPrintableDataA4();
    if (!data) {
        Swal.fire('Sin datos', 'No hay información del comprobante para imprimir en PDF.', 'warning');
        return;
    }

    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const empresa = data.empresa || {};
    const cab = data.comp_cab || {};
    const venta = data.venta || {};
    const detalles = Array.isArray(data.detalles) ? data.detalles : [];

    const total = detalles.reduce((sum, item) => sum + parseFloat(item.mtoValorVentaItem || 0), 0);
    const documentoLabel = (cab.TIPO_DOC == 3) ? 'BOLETA ELECTRÓNICA' : 'FACTURA ELECTRÓNICA';

    addLogoToDocumentA4(doc, empresa, 15, 10, 36, 18);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(16);
    doc.text((empresa.Emp_RazonSocial || 'EMPRESA').toUpperCase(), 60, 18);

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text((empresa.Emp_Direccion || ''), 60, 24);
    doc.text('Cel.: ' + (empresa.Emp_Telefono || ''), 60, 29);
    doc.text('Correo: ' + (empresa.Emp_Celular || ''), 60, 34);

    doc.setDrawColor(20, 20, 20);
    doc.setFillColor(0, 0, 0);
    doc.rect(150, 10, 42, 16, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(9);
    doc.text('RUC: ' + (empresa.Emp_Ruc || ''), 154, 17);
    doc.text(documentoLabel, 154, 23);
    doc.text((venta.SERIE || '') + ' - ' + (venta.COMPROBANTE || ''), 154, 29);
    doc.setTextColor(0, 0, 0);

    doc.setFontSize(10);
    doc.text('Fecha: ' + (cab.fecEmision || '') + ' ' + (cab.horEmision || ''), 15, 48);
    doc.text('Cliente: ' + (cab.rznSocialUsuario || ''), 15, 53);
    doc.text('Doc. Cliente: ' + (cab.numDocUsuario || ''), 15, 58);
    doc.text('Dirección: ' + (cab.desDireccionCliente || ''), 15, 63);

    doc.autoTable({
        startY: 70,
        head: [['CANT', 'DESCRIPCIÓN', 'P.U.', 'TOTAL']],
        body: detalles.map(item => [
            item.ctdUnidadItem || '',
            item.desItem || '',
            Number(item.mtoValorUnitario || 0).toFixed(2),
            Number(item.mtoValorVentaItem || 0).toFixed(2)
        ]),
        theme: 'grid',
        styles: { fontSize: 9, cellPadding: 2 },
        headStyles: { fillColor: [25, 25, 25], textColor: [255, 255, 255] },
        margin: { left: 15, right: 15 },
        columnStyles: {
            0: { cellWidth: 18, halign: 'center' },
            1: { cellWidth: 96 },
            2: { cellWidth: 26, halign: 'right' },
            3: { cellWidth: 26, halign: 'right' }
        }
    });

    const finalY = doc.lastAutoTable.finalY + 10;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.text('OPERACIÓN EXONERADA', 120, finalY);
    doc.text('S/ ' + Number(total || 0).toFixed(2), 180, finalY, { align: 'right' });
    doc.text('IGV (18%)', 120, finalY + 7);
    doc.text('S/ 0.00', 180, finalY + 7, { align: 'right' });
    doc.text('TOTAL', 120, finalY + 14);
    doc.text('S/ ' + Number(total || 0).toFixed(2), 180, finalY + 14, { align: 'right' });

    const filename = (venta.SERIE || 'DOC') + '-' + (venta.COMPROBANTE || '000') + '-A4.pdf';
    doc.save(filename);
});