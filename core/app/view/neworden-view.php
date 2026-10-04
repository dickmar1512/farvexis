<div class="row">
  <div class="col-md-12">
	<h3>Generar Nueva Orden</h3>
	<form class="form-horizontal" action="./?action=addordentrabajo" method="post">
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Tipo de Servicio *</label>
      <div class="col-lg-6">            
        <select name="selTipoServicio" class="form-control" required="required">
          <option value="">-- NINGUNO --</option>
          <option value="1">Garantía</option>
          <option value="2">Presupuesto</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Cliente *</label>
      <div class="col-lg-6">
      <?php
        $clients = PersonData::getClients();
      ?>
      <select name="selCliente" class="form-control" required="required">
        <option value="">-- NINGUNO --</option>
        <?php foreach($clients as $client):?>
        <option value="<?php echo $client->id;?>"><?php echo $client->name." ".$client->lastname;?></option>
        <?php endforeach;?>
      </select>
      </div>
    </div>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Fecha de Evaluación *</label>
      <div class="col-lg-3">
        <input type="date" name="fecha_evaluacion" value="<?php echo date("Y-m-d")?>" class="form-control" required>
      </div>
    </div>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Técnico Evaluador *</label>
      <div class="col-lg-3">
        <input type="text" name="tecnico_evaluador" class="form-control">
      </div>
    </div>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Observación *</label>
      <div class="col-lg-9">
        <textarea name="descripcion" class="form-control" rows="2" required="required"></textarea>
      </div>
    </div>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Diagnostico y Fallas</label>
      <div class="col-lg-9">
        <textarea name="diagnostico" class="form-control" rows="2"></textarea>
      </div>
    </div>
    <div class="form-group hide">
      <label for="inputEmail1" class="col-lg-3 control-label">Costo de mano de obra *</label>
      <div class="col-lg-4">
        <input type="number" name="mano_obra" step="any" class="form-control" value="0">
      </div>
    </div>
    <h5 class="text-center"><b>Datos del Equipo</b></h5>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Nombre *</label>
      <div class="col-lg-6">
        <input type="text" name="nombre_equipo" class="form-control" required="">
      </div>
    </div>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Modelo</label>
      <div class="col-lg-6">
        <input type="text" name="modelo_equipo" class="form-control">
      </div>
    </div>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Serie</label>
      <div class="col-lg-6">
        <input type="text" name="serie_equipo" class="form-control">
      </div>
    </div>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Tipo</label>
      <div class="col-lg-3">
        <input type="text" name="tipo_equipo" class="form-control">
      </div>
    </div>
    <div class="form-group">
      <label for="inputEmail1" class="col-lg-3 control-label">Fecha de Fabricación</label>
      <div class="col-lg-3">
        <input type="date" name="fecha_fabricacion" class="form-control">
      </div>
    </div>
    <div class="form-group">
        <label for="inputEmail1" class="col-lg-3 control-label">Fecha de Compra</label>
        <div class="col-lg-3">
          <input type="date" name="fecha_compra" class="form-control">
        </div>
    </div>
    <p class="alert alert-info">* Campos obligatorios</p>
      <div class="form-group">
          <div class="col-lg-offset-3 col-lg-9">
            <button type="submit" class="btn btn-primary">Generar Orden de Trabajo</button>
          </div>
      </div>
  </form>

	</div>
</div>