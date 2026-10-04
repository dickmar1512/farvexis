// Evento para abrir el modal de Agregar Unidad medida
$('#openModalAgregarCategoria').on('click', function () {
    openCategoriaModal('add');
});

// Evento para abrir el modal de edición de categoria (Delegación para DataTables)
$(document).on('click', '.edit-categoria', function () {
    const categoriaId = this.getAttribute('data-id');
    fetch(`./?action=getcategoria&id=${categoriaId}`)
        .then(response => response.json())
        .then(categoriaData => {
            openCategoriaModal('edit', categoriaData);
        })
        .catch(error => {
            console.error('Error al obtener los datos de categoria:', error);
        });
});

// Evento para activar o desactivar categorias (Delegación para DataTables)
$(document).on('click', '.delete-categoria', function () {
	const arrDato = this.getAttribute('data-id').split('|');
    const categoriaId = arrDato[0];
    const action = arrDato[1];
            
    handleCategoriaStatus(categoriaId, action);
});

function openCategoriaModal(action, categoriaData = null) {
    Swal.fire({
        title: action === 'edit' ? 'Editar Categoria' : 'Agregar Categoria',
        html: `     <hr>
                        <form  id="categoriaForm">
                            <div class="form-group">
                                <label for="txtDescripcion" class="control-label" style="display: flex; justify-content: left;">Nombre&nbsp;&nbsp;<span style="color:red;">*</span> </label>
                                <div class="col-md-12">
                                <input type="text" name="txtCategoria" class="form-control" id="txtCategoria" placeholder="Categoria" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="txtsigla" class="control-label" style="display: flex; justify-content: left;">Descripcion</label>
                                <div class="col-md-12">
                                <input type="text" name="txtDescripcion"  class="form-control" id="txtDescripcion" placeholder="Descripcion" value="-">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-">
                                <input type="hidden" name="idcategoria" id="idcategoria">
                                </div>
                            </div>
                        </form>
            `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: action === 'edit' ? 'Editar Categoria' : 'Agregar Categoria',
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
                    $('#txtCategoria').val(categoriaData.name);
                    $('#txtDescripcion').val(categoriaData.description);
                    $('#idcategoria').val(categoriaData.id) 
                }                 
            });
        },
        preConfirm: () => {
            const form = document.getElementById('categoriaForm');
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
            const url = action === 'edit' ? './?action=updatecategoria' : './?action=addcategoria';
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
                    Swal.fire('Éxito!', `El categoria ha sido ${action === 'edit' ? 'actualizado' : 'agregada'} correctamente.`, 'success')
                            .then(() => window.location = '?view=categories');
                } else {
                        Swal.fire('Error!', `Hubo un problema al ${action === 'edit' ? 'actualizar' : 'agregar'} la categoria.`, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', `Hubo un problema al ${action === 'edit' ? 'actualizar' : 'agregar'} la categoria.`, 'error');
            });
        }
    });
}

// Función para eliminar/activar categoria
function handleCategoriaStatus(categoriaId, action) {
    const texto = (action == 'D') ? "¿Desea desactivar Categoria?" : "¿Desea activar este Categoria?";
    const confirText = (action == 'D') ? "Sí, Desactivar" : "Sí, Activar";
    const estText1 = (action == 'D') ? "¡Desactivado!" : "¡Activado!";
    const estText2 = (action == 'D') ? "La categoria ha sido desactivado." : "La categoria ha sido activado.";
    const errorText = (action == 'D') ? "desactivar el categoria." : "activar el categoria.";

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
            fetch(`./?action=delcategoria&id=${categoriaId}&accion=${action}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(estText1, estText2, 'success')
                        .then(() => window.location = './?view=categories');
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