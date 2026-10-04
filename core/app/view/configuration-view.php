<br><br><br><br><div class="row">
	<div class="col-md-3">

	</div>
	<div class="col-md-6">
	<h2>Cambiar Contraseña</h2>
<br>	<form class="form-horizontal" id="changepasswd" role="form">
  <div class="form-group">
    <label for="inputEmail1" class="col-lg-4 control-label">Contraseña Actual</label>
    <div class="col-lg-8">
      <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña Actual" required>
    </div>
  </div>

  <div class="form-group">
    <label for="inputPassword1" class="col-lg-4 control-label">Nueva Contraseña</label>
    <div class="col-lg-8">
      <input type="password" class="form-control" id="newpassword" name="newpassword" placeholder="Nueva Contraseña" required>
    </div>
  </div>

  <div class="form-group">
    <label for="inputPassword1" class="col-lg-4 control-label">Confirmar Nueva Contraseña</label>
    <div class="col-lg-8">
      <input type="password" class="form-control" id="confirmnewpassword" name="confirmnewpassword" placeholder="Confirmar Nueva Contraseña" required>
    </div>
  </div>

  <div class="form-group">
    <div class="col-lg-offset-4 col-lg-8">
      <button type="submit" class="btn btn-success">Cambiar Contraseña</button>
    </div>
  </div>
</form>

<script>
$("#changepasswd").submit(function(e){
    e.preventDefault();
    var pass = $("#password").val();
    var newpass = $("#newpassword").val();
    var confpass = $("#confirmnewpassword").val();

    if (!pass || !newpass || !confpass) {
        Swal.fire('Campos requeridos', 'No debes dejar espacios vacíos.', 'warning');
        return;
    }

    if (newpass !== confpass) {
        Swal.fire('Error', 'La nueva contraseña no coincide con la confirmación.', 'error');
        return;
    }

    Swal.fire({
        title: 'Actualizando contraseña',
        text: 'Por favor espere...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: './?action=changepasswd',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                Swal.fire({
                    title: '¡Éxito!',
                    text: res.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = res.redirect || './?action=logout';
                });
            } else {
                Swal.fire('Error', res.message || 'No se pudo actualizar la contraseña.', 'error');
            }
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error en el servidor al cambiar contraseña.';
            Swal.fire('Error', msg, 'error');
        }
    });
});
</script>
	</div>
</div>
<br><br><br><br><br><br><br><br><br>