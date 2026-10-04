<?php 
 $codigo = KitData::getbarcode();
  foreach ($codigo as $cod) {
      $barcode = $cod->barcode;
     }
?>
<div class="row">
	<div class="col-md-12">
	<h1>Nuevo Kit de Productos</h1>
	<br>
		<form class="form-horizontal" method="post" enctype="multipart/form-data" id="addkit" action="./?action=addkit" role="form">

  <div class="form-group">
    <label for="inputEmail1" class="col-lg-2 control-label">Imagen</label>
    <div class="col-md-6">
      <input type="file" name="image" id="image" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail1" class="col-lg-2 control-label">Codigo de Barras*</label>
    <div class="col-md-6">
      <input type="text" name="barcode" id="product_code" class="form-control" id="barcode" placeholder="Codigo de Barras del Producto" value="<?=$barcode?>">
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail1" class="col-lg-2 control-label">Nombre*</label>
    <div class="col-md-6">
      <input type="text" name="name" required class="form-control" id="name" placeholder="Nombre del Producto">
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail1" class="col-lg-2 control-label">Descripcion</label>
    <div class="col-md-6">
      <textarea name="description" class="form-control" id="description" placeholder="Descripcion del Producto"></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail1" class="col-lg-2 control-label">Precio de Salida*</label>
    <div class="col-md-6">
      <input type="text" name="price_out" required class="form-control" id="price_out" placeholder="Precio de salida">
    </div>
  </div>

  <div class="form-group">
    <div class="col-lg-offset-2 col-lg-10">
      <button type="submit" class="btn btn-primary">Agregar Producto</button>
    </div>
  </div>
</form>

	</div>
</div>

<script>
  $(document).ready(function(){
    $("#product_code").keydown(function(e){
        if(e.which==17 || e.which==74 ){
            e.preventDefault();
        }else{
            console.log(e.which);
        }
    });

    // AJAX submit para crear nuevo kit
    $('#addkit').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        Swal.fire({ title: 'Guardando paquete...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
        $.ajax({
            type: 'POST', url: './?action=addkit', data: formData, processData: false, contentType: false, dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({ icon: 'success', title: '¡Registrado!', text: response.message, timer: 1200, showConfirmButton: false })
                        .then(function() { window.location.href = './?view=paquetes'; });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() { Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo guardar.' }); }
        });
    });
});

</script>