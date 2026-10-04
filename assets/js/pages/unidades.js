// Evento para abrir el modal de Agregar Unidad medida
$('#openModalAgregarUnidad').on('click', function () {
    openUnidadModal('add');
});

// Evento para abrir el modal de edición de unidad d emedida (Delegación para DataTables)
$(document).on('click', '.edit-unidad', function () {
    const unidadId = this.getAttribute('data-id');
    fetch(`./?action=getunidad&id=${unidadId}`)
        .then(response => response.json())
        .then(unidadData => {
            openUnidadModal('edit', unidadData);
        })
        .catch(error => {
            console.error('Error al obtener los datos de unidad de medidad:', error);
        });
});

function openUnidadModal(action, unidadData = null) {
    Swal.fire({
        title: action === 'edit' ? 'Editar Unidad' : 'Agregar Unidad',
        html: `     <hr>
                        <form  id="unidadForm">
                            <div class="form-group">
                                <label for="txtDescripcion" class="control-label" style="display: flex; justify-content: left;">Descripcion&nbsp;&nbsp;<span style="color:red;">*</span> </label>
                                <div class="col-md-12">
                                <input type="text" name="txtDescripcion" class="form-control" id="txtDescripcion" placeholder="Descripcion" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="txtsigla" class="control-label" style="display: flex; justify-content: left;">Sigla</label>
                                <div class="col-md-12">
                                <input type="text" name="txtsigla"  class="form-control" id="txtsigla" placeholder="Siglas" value="-">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-">
                                <input type="hidden" name="idunidad" id="idunidad">
                                </div>
                            </div>
                        </form>
            `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: action === 'edit' ? 'Editar Unidad' : 'Agregar Unidad',
        cancelButtonText: 'Cancelar',
        customClass: {
            container: 'custom-swal-container',
            popup: 'custom-swal-popup',
            header: 'custom-swal-header',
            title: 'custom-swal-title',
            content: 'custom-swal-content',
            closeButton: 'custom-swal-close-button'
        },
        width: '20%', // Ajusta el ancho del modal
        didOpen: () => {
            $(document).ready(function () {
                if(action === 'edit'){
                    $('#txtDescripcion').val(unidadData.name);
                    $('#txtsigla').val(unidadData.sigla);
                    $('#idunidad').val(unidadData.id) 
                }                 
            });
        },
        preConfirm: () => {
            const form = document.getElementById('unidadForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Validación básica
            if (!data.txtDescripcion) {
                Swal.showValidationMessage('Por favor, completa todos los campos obligatorios');
                return false;
            }
            return data;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const url = action === 'edit' ? './?action=updateunidad' : './?action=addunidad';
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(result.value),
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    Swal.fire('Éxito!', `El unidad ha sido ${action === 'edit' ? 'actualizado' : 'agregada'} correctamente.`, 'success')
                            .then(() => window.location = '?view=unidades');
                } else {
                        Swal.fire('Error!', `Hubo un problema al ${action === 'edit' ? 'actualizar' : 'agregar'} la unidad.`, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', `Hubo un problema al ${action === 'edit' ? 'actualizar' : 'agregar'} la unidad.`, 'error');
            });
        }
    });
}