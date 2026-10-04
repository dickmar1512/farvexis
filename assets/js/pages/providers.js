// Botón para agregar nuevo proveedor
$('#openModalNuevoProveedor').on('click', function() {
    showProviderModal();
});

// Evento para abrir el modal de edición de proveedor (Delegación para DataTables)
$(document).on('click', '.edit-provider', function () {
	const providerId = this.getAttribute('data-id');
            
    fetch(`./?action=getprovider&id=${providerId}`)
        .then(response => response.json())
        .then(provider => showProviderModal(provider))
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudieron cargar los datos del proveedor.', 'error');
    });
});

// Evento para activar o desactivar proveedores
$(document).on('click', '.delete-provider', function () {
	const arrDato = this.getAttribute('data-id').split('|');
    const providerId = arrDato[0];
    const action = arrDato[1];
            
    handleProviderStatus(providerId, action);
});

// Función unificada para mostrar modal de proveedor (agregar/editar)
function showProviderModal(provider = null) {
    const isEditMode = provider !== null;
    const title = isEditMode ? '<i class="fas fa-edit mr-2"></i> Editar Proveedor' : '<i class="fas fa-truck mr-2"></i> Nuevo Proveedor';
    const confirmButtonText = isEditMode ? 'Actualizar' : 'Guardar';
    
    const initialType = Number(provider?.tipo_persona || 1); // Default to Jurídica for providers
    const initialDocType = initialType == 3 ? 1 : 6;
    
    const formHtml = `
        <style>
            .swal2-popup.corporate-modal { padding: 1rem; border-radius: 8px; font-family: 'Source Sans Pro', sans-serif; }
            .corporate-modal .swal2-title { font-size: 1.2rem; color: #1f2d3d; margin-bottom: 0.8rem; border-bottom: 1px solid #ebedef; padding-bottom: 0.5rem; font-weight: 600; }
            .corporate-modal .form-group { margin-bottom: 0.6rem; }
            .corporate-modal .form-control-sm { border-radius: 3px; border: 1px solid #ced4da; height: calc(1.8125rem + 2px); }
            .corporate-modal label { font-weight: 600; font-size: 0.8rem; color: #495057; margin-bottom: 0.2rem; display: block; text-align: left; }
            .corporate-modal .icheck-primary label { font-weight: 400; font-size: 0.85rem; }
            .corporate-modal hr { margin: 0.8rem 0; border-color: #eee; }
            .corporate-modal .alert-info { font-size: 0.7rem; padding: 0.4rem 0.6rem; margin-top: 0.5rem; border-radius: 3px; border: none; background-color: #e7f3f5; color: #31708f; }
        </style>
        <form id="providerForm" class="text-left px-2">
            <div class="d-flex justify-content-center align-items-center mb-3" style="gap: 20px;">
                <div class="icheck-primary d-inline">
                    <input type="radio" id="optTipoPersona1" name="optTipoPersona" 
                        ${initialType == 3 ? 'checked' : ''} 
                        ${isEditMode ? 'disabled' : ''} value="3">
                    <label for="optTipoPersona1" class="ml-1"> Persona Natural</label>
                </div>
                <div class="icheck-success d-inline ml-3">    
                    <input type="radio" id="optTipoPersona2" name="optTipoPersona" 
                        ${initialType == 1 ? 'checked' : ''} 
                        ${isEditMode ? 'disabled' : ''} value="1"> 
                    <label for="optTipoPersona2" class="ml-1"> Persona Jurídica</label>
                </div>									
                <input type="hidden" id="tipodoc" name="tipodoc" value="${initialDocType}">
                ${isEditMode ? `<input type="hidden" name="optTipoPersona" value="${initialType}">` : ''}
            </div>
            <hr class="mt-0 mb-3">
            
            <div id="natural" style="${initialType == 1 ? 'display: none;' : ''}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="dni">RUC*</label>
                            <input type="text" name="dni" class="form-control form-control-sm" id="dni" 
                                maxlength="8" ${initialType == 3 ? 'required' : ''}
                                value="${initialType == 3 ? provider?.numero_documento : ''}">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Nombre(s)*</label>
                            <input type="text" name="name" class="form-control form-control-sm" id="name" 
                                ${initialType == 3 ? 'required' : ''}
                                value="${initialType == 3 ? provider?.name : ''}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="lastname">Apellidos*</label>
                    <input type="text" name="lastname" class="form-control form-control-sm" id="lastname" 
                        ${initialType == 3 ? 'required' : ''}
                        value="${provider?.lastname || ''}">
                </div>
            </div>
            
            <div id="juridica" style="${initialType == 3 ? 'display: none;' : ''}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ruc">RUC*</label>
                            <input type="text" name="ruc" class="form-control form-control-sm" id="ruc" 
                                maxlength="11" ${initialType == 1 ? 'required' : ''}
                                value="${initialType == 1 ? provider?.numero_documento : ''}">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="razon_social">Razón Social*</label>
                            <input type="text" name="razon_social" class="form-control form-control-sm" id="razon_social" 
                                ${initialType == 1 ? 'required' : ''}
                                value="${initialType == 1 ? provider?.name : ''}">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address1">Dirección*</label>
                <input type="text" name="address1" class="form-control form-control-sm" required id="address1" 
                    value="${provider?.address1 || ''}">
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Departamento</label>
                        <select id="dep" class="form-control form-control-sm"></select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Provincia</label>
                        <select id="prov" class="form-control form-control-sm" disabled></select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Distrito</label>
                        <select id="dist" name="ubigeo" class="form-control form-control-sm" disabled></select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="form-group">
                        <label for="email1">Email</label>
                        <input type="email" name="email1" class="form-control form-control-sm" id="email1" 
                            value="${provider?.email1 || ''}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="phone1">Teléfono</label>
                        <input type="text" name="phone1" class="form-control form-control-sm" id="phone1" 
                            value="${provider?.phone1 || ''}">
                    </div>
                </div>
            </div>

            <div class="alert alert-info mb-0 mt-2"><i class="fas fa-info-circle mr-1"></i> Los campos con (*) son obligatorios</div>
            ${isEditMode ? `<input type="hidden" name="provider_id" id="provider_id" value="${provider.id}">` : ''}
        </form>
    `;

    Swal.fire({
        title: title,
        html: formHtml,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'corporate-modal',
            confirmButton: 'btn btn-primary btn-sm px-4',
            cancelButton: 'btn btn-secondary btn-sm px-4 ml-2'
        },
        buttonsStyling: false,
        width: '520px',
        didOpen: () => {
            const depSelect = $('#dep');
            const provSelect = $('#prov');
            const distSelect = $('#dist');

            // Sequential Ubigeo Loading
            const loadDepartments = async (selectedDep = null) => {
                const response = await fetch('./?action=get_ubigeo&type=departments');
                const deps = await response.json();
                depSelect.empty().append('<option value="">Seleccione...</option>');
                deps.forEach(d => depSelect.append(`<option value="${d}" ${d === selectedDep ? 'selected' : ''}>${d}</option>`));
            };

            const loadProvinces = async (dep, selectedProv = null) => {
                if (!dep) {
                    provSelect.empty().append('<option value="">Seleccione...</option>').attr('disabled', true);
                    distSelect.empty().append('<option value="">Seleccione...</option>').attr('disabled', true);
                    return;
                }
                const response = await fetch(`./?action=get_ubigeo&type=provinces&dep=${encodeURIComponent(dep)}`);
                const provs = await response.json();
                provSelect.empty().append('<option value="">Seleccione...</option>').attr('disabled', false);
                provs.forEach(p => provSelect.append(`<option value="${p}" ${p === selectedProv ? 'selected' : ''}>${p}</option>`));
                distSelect.empty().append('<option value="">Seleccione...</option>').attr('disabled', true);
            };

            const loadDistricts = async (dep, prov, selectedDistId = null) => {
                if (!dep || !prov) {
                    distSelect.empty().append('<option value="">Seleccione...</option>').attr('disabled', true);
                    return;
                }
                const response = await fetch(`./?action=get_ubigeo&type=districts&dep=${encodeURIComponent(dep)}&prov=${encodeURIComponent(prov)}`);
                const dists = await response.json();
                distSelect.empty().append('<option value="">Seleccione...</option>').attr('disabled', false);
                dists.forEach(d => distSelect.append(`<option value="${d.id}" ${d.id == selectedDistId ? 'selected' : ''}>${d.name}</option>`));
            };

            depSelect.on('change', function() { loadProvinces($(this).val()); });
            provSelect.on('change', function() { loadDistricts(depSelect.val(), $(this).val()); });

            // Initialize Ubigeo
            if (isEditMode && provider.ubigeo) {
                fetch(`./?action=get_ubigeo&type=details&id=${provider.ubigeo}`)
                    .then(r => r.json())
                    .then(async details => {
                        if (details) {
                            await loadDepartments(details.departamento);
                            await loadProvinces(details.departamento, details.provincia);
                            await loadDistricts(details.departamento, details.provincia, provider.ubigeo);
                        } else {
                            loadDepartments();
                        }
                    });
            } else {
                loadDepartments();
            }

            // Auto-fetch RUC
            const fillProviderData = (numDoc, tipo) => {
                if (numDoc.length === (tipo === 3 ? 8 : 11)) {
                    Swal.showLoading();
                    fetch('./?action=generar_nombre_ajax', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `numDocUsuario=${numDoc}&tipo=${tipo}`
                    })
                    .then(response => response.json())
                    .then(rpta => {
                        Swal.hideLoading();
                        if (rpta.success && rpta.data) {
                            if (tipo === 3) {
                                $("#name").val(rpta.data.name);
                            } else {
                                $("#razon_social").val(rpta.data.name);
                            }
                            $("#address1").val(rpta.data.address1);
                            
                            if (rpta.data.ubigeo) {
                                fetch(`./?action=get_ubigeo&type=details&id=${rpta.data.ubigeo}`)
                                .then(r => r.json())
                                .then(async details => {
                                    if (details) {
                                        await loadDepartments(details.departamento);
                                        await loadProvinces(details.departamento, details.provincia);
                                        await loadDistricts(details.departamento, details.provincia, rpta.data.ubigeo);
                                    }
                                });
                            }
                        }
                    })
                    .catch(() => Swal.hideLoading());
                }
            };

            // Auto-fetch RUC/DNI and Auto-detect Type
            const handleDocInput = (e) => {
                const numDoc = e.target.value;
                if (numDoc.length === 11 && !isEditMode) {
                    if (numDoc.startsWith('10')) {
                        // Switch to Natural
                        $('#optTipoPersona1').prop('checked', true);
                        $("#natural").show(); $("#juridica").hide();
                        $("#dni, #name, #lastname").attr('required', true);
                        $("#ruc, #razon_social").attr('required', false);
                        $("#tipodoc").val(1);
                        if (e.target.id === 'ruc') {
                            $('#dni').val(numDoc);
                            $('#ruc').val('');
                        }
                        fillProviderData(numDoc, 3);
                    } else if (numDoc.startsWith('20')) {
                        // Switch to Juridical
                        $('#optTipoPersona2').prop('checked', true);
                        $("#natural").hide(); $("#juridica").show();
                        $("#dni, #name, #lastname").attr('required', false);
                        $("#ruc, #razon_social").attr('required', true);
                        $("#tipodoc").val(6);
                        if (e.target.id === 'dni') {
                            $('#ruc').val(numDoc);
                            $('#dni').val('');
                        }
                        fillProviderData(numDoc, 1);
                    } else {
                        fillProviderData(numDoc, $("input[name=optTipoPersona]:checked").val() == 3 ? 3 : 1);
                    }
                } else if (numDoc.length === 8 && !isEditMode) {
                    fillProviderData(numDoc, 3);
                }
            };

            $("#dni, #ruc").on('change', handleDocInput);

            // Toggle Natural/Jurídica
            if (!isEditMode) {
                $("input[name=optTipoPersona]").click(function() {
                    const type = $(this).val();
                    if (type == 3) {
                        $("#natural").show(); $("#juridica").hide();
                        $("#dni, #name, #lastname").attr('required', true);
                        $("#ruc, #razon_social").attr('required', false);
                        $("#tipodoc").val(1);
                    } else {
                        $("#natural").hide(); $("#juridica").show();
                        $("#dni, #name, #lastname").attr('required', false);
                        $("#ruc, #razon_social").attr('required', true);
                        $("#tipodoc").val(6);
                    }
                });
            }
        },
        preConfirm: () => {
            const form = document.getElementById('providerForm');
            if (!form.checkValidity()) {
                Swal.showValidationMessage('Por favor completa los campos requeridos');
                return false;
            }
            const formData = new FormData(form);
            return Object.fromEntries(formData.entries());
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const endpoint = isEditMode ? './?action=updateprovider' : './?action=addprovider';
            const successMessage = isEditMode ? 'Actualizado correctamente' : 'Agregado correctamente';
            
            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(result.value)
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    Swal.fire('Éxito!', successMessage, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', response.message || 'Error al procesar', 'error');
                }
            })
            .catch(error => Swal.fire('Error', 'Error en la solicitud', 'error'));
        }
    });
}

// Función para eliminar/activar proveedor
function handleProviderStatus(providerId, action) {
    const texto = (action == 'D') ? "¡Desea desactivar este Proveedor!" : "¡Desea activar este Proveedor!";
    const confirText = (action == 'D') ? "Sí, Desactivar" : "Sí, Activar";
    const estText1 = (action == 'D') ? "¡Desactivado!" : "¡Activado!";
    const estText2 = (action == 'D') ? "El proveedor ha sido desactivado." : "El proveedor ha sido activado.";
    const errorText = (action == 'D') ? "desactivar el proveedor." : "activar el proveedor.";

    Swal.fire({
        title: '¿Estás seguro?',
        text: texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirText,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`./?action=delprovider&id=${providerId}&accion=${action}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(estText1, estText2, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', 'No se pudo ' + errorText, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Hubo un problema al ' + errorText, 'error');
            });
        }
    });
}