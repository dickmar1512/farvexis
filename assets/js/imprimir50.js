 // Inicializar jsPDF
 //const { jsPDF50 } = window.jspdf;

 // Función para aplicar márgenes
 function applyMargins(doc, marginLeft, marginTop, marginRight, marginBottom) {
     // Guardar los márgenes en el objeto doc para usarlos luego
     doc.margins = { left: marginLeft, top: marginTop, right: marginRight, bottom: marginBottom };
     return doc;
 }

 $('#imprimir50mm').on('click', async () => {
    Swal.fire({
        title: '¡Advertencia!',
        text: 'No tiene acceso a este formato de impresión',
        icon: 'info', // Puede ser 'success', 'error', 'warning', 'info', 'question'
        confirmButtonText: 'Aceptar'
    });
 });