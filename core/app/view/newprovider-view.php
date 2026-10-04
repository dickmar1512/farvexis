<div class="row">
	<div class="col-md-12">
  	<h1>Nuevo Proveedor</h1>
  	<form class="form-horizontal" method="post" id="addproduct" action="./?view=addprovider" role="form">
      <div class="row text-center">
        <label class="radio-inline"><input type="radio" name="optTipoPersona" checked value="3">Persona</label>
        <label class="radio-inline"><input type="radio" name="optTipoPersona" value="1">Empresa</label>
        <br><br>
      </div>
      <div id="natural">
        <div class="form-group" id="natural">
          <label for="inputEmail1" class="col-lg-2 control-label">DNI*</label>
          <div class="col-md-6">
            <input type="text" name="dni" class="form-control" id="dni" placeholder="Nombre" required="">
          </div>
        </div>
        <div class="form-group">
          <label for="inputEmail1" class="col-lg-2 control-label">Nombre*</label>
          <div class="col-md-6">
            <input type="text" name="name" class="form-control" id="name" placeholder="Nombre" required="">
          </div>
        </div>
        <div class="form-group">
          <label for="inputEmail1" class="col-lg-2 control-label">Apellido*</label>
          <div class="col-md-6">
            <input type="text" name="lastname" required class="form-control" id="lastname" placeholder="Apellido" required="">
          </div>
        </div>
      </div>
      <div id="juridica" style="display: none;">
        <div class="form-group">
          <label for="inputEmail1" class="col-lg-2 control-label">RUC*</label>
          <div class="col-md-6">
            <input type="text" name="ruc" class="form-control" id="ruc" placeholder="RUC">
          </div>
        </div>
        <div class="form-group">
          <label for="inputEmail1" class="col-lg-2 control-label">Razón Social*</label>
          <div class="col-md-6">
            <input type="text" name="razon_social" class="form-control" id="razon_social" placeholder="Razón Social">
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-2 control-label">Direccion*</label>
        <div class="col-md-6">
          <input type="text" name="address1" class="form-control" required id="address1" placeholder="Direccion">
        </div>
      </div>
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-2 control-label">Email*</label>
        <div class="col-md-6">
          <input type="text" name="email1" class="form-control" id="email1" placeholder="Email">
        </div>
      </div>
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-2 control-label">Telefono*</label>
        <div class="col-md-6">
          <input type="text" name="phone1" class="form-control" id="phone1" placeholder="Telefono">
        </div>
      </div>
      <p class="alert alert-info">* Campos obligatorios</p>
      <div class="form-group">
        <div class="col-lg-offset-2 col-lg-10">
          <button type="submit" class="btn btn-primary">Agregar Proveedor</button>
        </div>
      </div>
    </form>
	</div>
</div>
<script>
  $("input[name=optTipoPersona]").click(function () {
    var optTipoPersona =  $('input:radio[name=optTipoPersona]:checked').val();
   
    if(optTipoPersona == 3)
    {
      $("#natural").show("slow");
      $("#juridica").hide("slow");

      $("#dni").attr('required', '');
      $("#name").attr('required', '');
      $("#lastname").attr('required', '');
      $("#ruc").attr('required', false);
      $("#razon_social").attr('required', false);

    }
    else if (optTipoPersona == 1)
    {
      $("#natural").hide("slow");
      $("#juridica").show("slow");

      $("#dni").attr('required', false);
      $("#name").attr('required', false);
      $("#lastname").attr('required', false);
      $("#ruc").attr('required', '');
      $("#razon_social").attr('required', '');
    }
  });
</script>