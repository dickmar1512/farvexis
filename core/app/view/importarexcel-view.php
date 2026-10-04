<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class='fas fa-file-excel text-success mr-2'></i> Importar Nueva compra vía
                    Excel</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="./">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="./?view=importarexcel">Compras</a></li>
                    <li class="breadcrumb-item active">Nueva Compra Excel</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-9">

                <!-- Mensajes de Estado -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible shadow-sm border-0 mb-4">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
                        Se importaron <strong><?php echo $_GET['rows'] ?></strong> productos correctamente a la base de
                        datos.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-4">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Error en la Importación</h5>
                        <?php echo htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Instrucciones -->
                    <div class="col-md-5">
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header py-2 text-center">
                                <h3 class="card-title text-sm font-weight-bold uppercase"><i
                                        class="fas fa-info-circle mr-1"></i> Instrucciones de Formato</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-striped text-xs mb-0">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th class="pl-3">COL</th>
                                                <th>DATO REQUERIDO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">A (0)</td>
                                                <td>Cód. DIGEMID</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">B (1)</td>
                                                <td>Nombre del Producto*</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">C (2)</td>
                                                <td>Principio Activo</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">D (3)</td>
                                                <td>Presentación</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">E (4)</td>
                                                <td>Laboratorio</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">F (5)</td>
                                                <td>Registro Sanitario</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">G (6)</td>
                                                <td>F. Vencimiento (dd/mm/aaaa)</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">H (7)</td>
                                                <td class="text-muted italic">Cant. x Caja (Opcional)</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">I (8)</td>
                                                <td>Stock Inicial / Unid.*</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">J (9)</td>
                                                <td>Número de Lote</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold">K (10)</td>
                                                <td class="text-muted italic">Precio Prov. (Opcional)</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">L (11)</td>
                                                <td>Precio Unit. Costo*</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">M (12)</td>
                                                <td>Nro. Factura (F001-001)*</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">N (13)</td>
                                                <td>Nro. de Guía</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">O (14)</td>
                                                <td>Precio Venta Público*</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">P (15)</td>
                                                <td>Precio Venta Mayor</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">Q (16)</td>
                                                <td>Código de Barras*</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">R (17)</td>
                                                <td>Nombre Proveedor</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">S (18)</td>
                                                <td>RUC Proveedor*</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">T (19)</td>
                                                <td>Sede / Observaciones</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">U (20)</td>
                                                <td>F. Compra (dd/mm/aaaa)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3 bg-light border-top">
                                    <p class="text-xs text-muted mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>
                                        Los campos con (*) son altamente recomendados para la consistencia del
                                        inventario.</p>
                                    <a href="./?action=downloadtemplate" class="btn btn-info btn-block btn-xs shadow-sm"
                                        target="_blank">
                                        <i class="fas fa-download mr-1"></i> DESCARGAR PLANTILLA EXCEL
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de Carga -->
                    <div class="col-md-7">
                        <div class="card card-outline card-success shadow-sm h-100">
                            <div class="card-header py-2">
                                <h3 class="card-title text-sm font-weight-bold uppercase">Subir Archivo Excel</h3>
                            </div>
                            <div class="card-body py-4">
                                <form class="form-horizontal" method="post" id="importForm"
                                    action="./?action=addproductxls" role="form" enctype="multipart/form-data">
                                    <div class="upload-zone border-dashed p-5 text-center mb-4" id="drop-zone">
                                        <div class="mb-3">
                                            <i class="fas fa-cloud-upload-alt fa-4x text-success opacity-3"></i>
                                        </div>
                                        <p class="text-muted font-weight-bold mb-1">Arrastre el archivo aquí o haga clic
                                            para buscar</p>
                                        <p class="text-xs text-muted">Formatos permitidos: .xls, .xlsx</p>

                                        <input type="file" name="image" id="excelFile" class="custom-file-input"
                                            style="display:none;" required>
                                        <label class="btn btn-success mt-3" id="fileLabel">
                                            <i class="fas fa-file-excel mr-2"></i> Seleccionar archivo
                                        </label>
                                        <div id="file-info" class="mt-2 font-weight-bold text-success text-sm"
                                            style="display:none;"></div>
                                    </div>

                                    <button type="submit"
                                        class="btn btn-primary btn-lg btn-block shadow-sm py-3 font-weight-bold"
                                        id="btnImport" disabled>
                                        <i class="fas fa-upload mr-2"></i> INICIAR PROCESAMIENTO
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .border-dashed {
        border: 2px dashed #28a745;
        border-radius: 10px;
        background-color: rgba(40, 167, 69, 0.02);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .border-dashed:hover {
        background-color: rgba(40, 167, 69, 0.05);
        border-color: #218838;
    }

    .opacity-3 {
        opacity: 0.3;
    }

    .uppercase {
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
<script>
    const fileInput = document.getElementById('excelFile');
    const fileInfo = document.getElementById('file-info');
    const fileLabel = document.getElementById('fileLabel');
    const btnImport = document.getElementById('btnImport');
    const dropZone = document.getElementById('drop-zone');

    function updateUI(file) {
        if (file) {
            fileInfo.innerHTML = `<i class="fas fa-check-circle mr-1"></i> Archivo listo: ${file.name}`;
            fileInfo.style.display = 'block';
            fileLabel.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Cambiar archivo';
            btnImport.disabled = false;
            dropZone.classList.add('bg-light');
        }
    }

    fileInput.addEventListener('change', function (e) {
        updateUI(e.target.files[0]);
    });

    // Click to select
    dropZone.onclick = (e) => {
        if (e.target !== fileInput) {
            fileInput.click();
        }
    };

    // Drag & Drop logic
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
            dropZone.style.borderColor = '#218838';
        }, false);
    });

    ['dragleave'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.backgroundColor = '';
            dropZone.style.borderColor = '#28a745';
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.backgroundColor = '';
        dropZone.style.borderColor = '#28a745';

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files; // Assign dropped files to input
            updateUI(files[0]);
        }
    }, false);

    // AJAX submit para importar productos
    document.getElementById('importForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        Swal.fire({ title: 'Importando productos...', html: 'Por favor espere, esto puede tomar unos momentos.', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
        fetch('./?action=addproductxls', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(function(response) {
                if (response.status === 'success') {
                    Swal.fire({ icon: 'success', title: '¡Importación exitosa!', html: '<b>' + response.message + '</b>', confirmButtonText: 'Ver productos' })
                        .then(function() { window.location.href = response.redirect; });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error en importación', text: response.message });
                }
            })
            .catch(function() {
                Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo comunicar con el servidor.' });
            });
    });
</script>