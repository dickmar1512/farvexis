    const sucursales = window.farvexisSucursales || [];
    const opcionesSucursales = function (seleccionada) {
        return sucursales.map(function (sucursal) {
            return '<option value="' + sucursal.id + '" ' + (String(sucursal.id) === String(seleccionada || 1) ? 'selected' : '') + '>' + sucursal.codigo + ' - ' + sucursal.nombre + '</option>';
        }).join('');
    };
    const bloqueRoles = function (usuario) {
        usuario = usuario || {};
        return '<div class="user-role-section border rounded p-3 mb-3 text-left">' +
            '<div class="user-role-heading font-weight-bold border-bottom pb-2 mb-2"><i class="fas fa-user-shield mr-2"></i>ROLES</div>' +
            '<div class="user-role-options d-flex flex-column">' +
                '<label><input type="checkbox" class="role-check mr-2" name="is_admin" ' + (usuario.is_admin ? 'checked' : '') + '> Administrador</label>' +
                '<label><input type="checkbox" class="role-check mr-2" name="is_dirtec" ' + (usuario.is_dirtec ? 'checked' : '') + '> Dirección técnica</label>' +
                '<label><input type="checkbox" class="role-check mr-2" name="is_caja" ' + (usuario.is_caja ? 'checked' : '') + '> Caja</label>' +
            '</div><small class="text-muted">Selecciona un rol principal para este usuario.</small></div>';
    };

    $(document).on('change', '.role-check', function () {
        if (!this.checked) return;
        $('.role-check').not(this).prop('checked', false);
    });

    $('#newuser').on('click', function () {
        Swal.fire({
            title: 'Agregar Usuario',
            html:
                '<form id="adduserForm">' +
                '<div class="form-group">' +
                '  <label for="name" style="display: flex; justify-content: left;">Nombre*</label>' +
                '  <input type="text" name="name" class="form-control" id="name" placeholder="Nombre" required>' +
                '</div>' +
                '<div class="form-group">' +
                '  <label for="lastname" style="display: flex; justify-content: left;">Apellido*</label>' +
                '  <input type="text" name="lastname" class="form-control" id="lastname" placeholder="Apellido" required>' +
                '</div>' +
                '<div class="form-group">' +
                '  <label for="username" style="display: flex; justify-content: left;">Nombre de usuario*</label>' +
                '  <input type="text" name="username" class="form-control" id="username" placeholder="Nombre de usuario" required>' +
                '</div>' +
                '<div class="form-group">' +
                '  <label for="email" style="display: flex; justify-content: left;">Email*</label>' +
                '  <input type="email" name="email" class="form-control" id="email" placeholder="Email" required>' +
                '</div>' +
                '<div class="form-group">' +
                '  <label for="password" style="display: flex; justify-content: left;">Contraseña</label>' +
                '  <input type="password" name="password" class="form-control" id="password" placeholder="Contraseña">' +
                '</div>' +
                '<div class="form-group">' +
                bloqueRoles() +
                '<div class="form-group"><label for="sucursal_id" style="display: flex; justify-content: left;">Sucursal</label><select name="sucursal_id" id="sucursal_id" class="form-control">' + opcionesSucursales(1) + '</select></div>' +
                '<p class="alert alert-info">* Campos obligatorios</p>' +
                '</form>',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Agregar Usuario',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const form = $('#adduserForm')[0];
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                // Validación básica
                if (!data.name || !data.lastname || !data.username || !data.email) {
                    Swal.showValidationMessage('Por favor, completa todos los campos obligatorios');
                    return false;
                }

                return data;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Hacer la petición AJAX con jQuery
                $.ajax({
                    url: './?action=adduser',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(result.value),
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Éxito!', 'El usuario ha sido agregado correctamente.', 'success')
                                .then(() => window.location = './?view=users'); // Redirigir tras éxito
                        } else {
                            Swal.fire('Error!', 'Hubo un problema al agregar el usuario.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al agregar el usuario:', error);
                        Swal.fire('Error', 'Hubo un problema al agregar el usuario.', 'error');
                    }
                });
            }
        });
    });

    // Evento para abrir el modal de edición de usuario (Delegación para DataTables)
    $(document).on('click', '.edit-user', function (e) {
        e.preventDefault(); // Evita que el enlace redirija
        
        const userId = $(this).data('id'); // Obtiene el ID del usuario
        
        // Hacer una solicitud AJAX para obtener los datos del usuario
        $.ajax({
            url: `./?action=edituser&id=${userId}`,
            type: 'GET',
            dataType: 'json',
            success: function (user) {
                // Mostrar el modal de SweetAlert2 con los datos del usuario
                showEditUserModal(user);
            },
            error: function (xhr, status, error) {
                console.error('Error al obtener los datos del usuario:', error);
                Swal.fire('Error', 'No se pudieron cargar los datos del usuario.', 'error');
            }
        });
    });

	function showEditUserModal(user) {
		Swal.fire({
			title: 'Editar Usuario',
			html:
				`<form id="edituserForm">
					<div class="form-group">
						<label for="name" style="display: flex; justify-content: left;">Nombre*</label>
						<input type="text" name="name" class="form-control" id="name" placeholder="Nombre" value="${user.name}" required>
					</div>
					<div class="form-group">
						<label for="lastname" style="display: flex; justify-content: left;">Apellido*</label>
						<input type="text" name="lastname" class="form-control" id="lastname" placeholder="Apellido" value="${user.lastname}" required>
					</div>
					<div class="form-group">
						<label for="username" style="display: flex; justify-content: left;">Nombre de usuario*</label>
						<input type="text" name="username" class="form-control" id="username" placeholder="Nombre de usuario" value="${user.username}" required>
					</div>
					<div class="form-group">
						<label for="email" style="display: flex; justify-content: left;">Email*</label>
						<input type="email" name="email" class="form-control" id="email" placeholder="Email" value="${user.email}" required>
					</div>
					<div class="form-group">
						<label for="password" style="display: flex; justify-content: left;">Contraseña</label>
						<input type="password" name="password" class="form-control" id="password" placeholder="Contraseña">
						<p class="help-block">La contraseña solo se modificará si escribes algo, en caso contrario no se modifica.</p>
					</div>
                    ${bloqueRoles(user)}
					<div class="form-group">
                        <label for="sucursal_id" style="display: flex; justify-content: left;">Sucursal</label>
                        <select name="sucursal_id" id="sucursal_id" class="form-control">${opcionesSucursales(user.sucursal_id)}</select>
                    </div>
                    <div class="form-group">
						<label for="montomax" style="display: flex; justify-content: left;">Monto maximo efectivo en caja</label>
						<input type="number" name="montomax" class="form-control" id="montomax" placeholder="Monto Maximo" value="${user.montomax}">
					</div>
					<div class="form-group">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="is_active" ${user.is_active ? 'checked' : ''}> Está activo
							</label>
						</div>
					</div>
					<div class="form-group">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="is_desc" ${user.is_desc ? 'checked' : ''}> Descuentos
							</label>
						</div>
					</div>
					<p class="alert alert-info">* Campos obligatorios</p>
					<input type="hidden" name="user_id" value="${user.id}">
				</form>`,
			focusConfirm: false,
			showCancelButton: true,
			confirmButtonText: 'Actualizar Usuario',
			cancelButtonText: 'Cancelar',
			preConfirm: () => {
				const form = document.getElementById('edituserForm');
				const formData = new FormData(form);
				const data = Object.fromEntries(formData.entries());

				// Validación básica
				if (!data.name || !data.lastname || !data.username || !data.email) {
					Swal.showValidationMessage('Por favor, completa todos los campos obligatorios');
					return false;
				}

				return data;
			}
		}).then((result) => {
			if (result.isConfirmed) {
				// Enviar los datos actualizados al servidor
				fetch('./?action=updateuser', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(result.value),
				})
				.then(response => response.json())
				.then(response => {
					if (response.success) {
						Swal.fire('Éxito', 'El usuario ha sido actualizado correctamente.', 'success')
						.then(() => window.location = './?view=users'); // Redirigir tras éxito;
					} else {
						Swal.fire('Error', 'Hubo un problema al actualizar el usuario.', 'error');
					}
				})
				.catch(error => {
					console.error('Error al actualizar el usuario:', error);
					Swal.fire('Error', 'Hubo un problema al actualizar el usuario.', 'error');
				});
			}
		});
	}

    // Gestionar Permisos
    $(document).on('click', '.manage-permissions', function (e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        Swal.fire({
            title: 'Cargando permisos...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `./?action=getuserpermissions&id=${userId}`,
            type: 'GET',
            dataType: 'json',
            success: function (modules) {
                Swal.close();
                showPermissionsModal(userId, userName, modules);
            },
            error: function() {
                Swal.fire('Error', 'No se pudieron cargar los permisos.', 'error');
            }
        });
    });

    function showPermissionsModal(userId, userName, modules) {
        let html = '<div class="text-left" style="max-height: 450px; overflow-y: auto; padding: 10px;">';
        
        // Agrupar por padres
        const parents = modules.filter(m => m.parent_id === null);
        
        parents.forEach(parent => {
            const children = modules.filter(m => m.parent_id == parent.id);
            html += `<div class="card card-outline card-info mb-3 shadow-sm">
                        <div class="card-header py-2 bg-light">
                            <h3 class="card-title text-sm">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input module-check" id="mod_${parent.id}" data-id="${parent.id}" ${parent.has_access ? 'checked' : ''}>
                                    <label class="custom-control-label font-weight-bold" for="mod_${parent.id}">${parent.name}</label>
                                </div>
                            </h3>
                        </div>`;
            if (children.length > 0) {
                html += `<div class="card-body py-2 pl-4">
                            <div class="row">`;
                children.forEach(child => {
                    html += `<div class="col-md-6 mb-1">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input module-check" id="mod_${child.id}" data-id="${child.id}" ${child.has_access ? 'checked' : ''}>
                                    <label class="custom-control-label text-xs" for="mod_${child.id}">${child.name}</label>
                                </div>
                             </div>`;
                });
                html += `   </div>
                         </div>`;
            }
            html += `</div>`;
        });
        html += '</div>';

        Swal.fire({
            title: `<i class="fas fa-shield-alt mr-2 text-primary"></i> Permisos: ${userName}`,
            html: html,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save mr-2"></i> Guardar Cambios',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            preConfirm: () => {
                const selected = [];
                $('.module-check:checked').each(function() {
                    selected.push($(this).data('id'));
                });
                return selected;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Guardando...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: './?action=updateuserpermissions',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        user_id: userId,
                        permissions: result.value
                    }),
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('¡Éxito!', 'Permisos actualizados correctamente.', 'success');
                        } else {
                            Swal.fire('Error', response.message || 'No se pudieron actualizar los permisos.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un fallo en la conexión con el servidor.', 'error');
                    }
                });
            }
        });
    }