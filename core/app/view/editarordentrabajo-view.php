<?php
  $orden_id = $_GET["id"];
  $orden = OrdenTrabajoData::getById($orden_id);
  $activo = ActivoData::getById($orden->activo_id);
?>
<div class="row">
	<div class="col-md-8">
	<h1><small>Editar Orden Trabajo</small></h1>
	<br><br>
		<form class="form-horizontal" action="./?action=updateordentrabajo" method="post">
      <input type="hidden" name="orden_id" value="<?php echo $orden->id ?>">
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-3 control-label">Tipo de Servicio *</label>
        <div class="col-lg-6">
          <select name="selTipoServicio" class="form-control" required="required">
            <option value="">-- NINGUNO --</option>
            <option value="1" <?php if($orden->tipo_servicio == 1){ ?> selected="selected" <?php }?>>Garantía</option>
            <option value="2" <?php if($orden->tipo_servicio == 2){ ?> selected="selected" <?php }?>>Presupuesto</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-3 control-label">Cliente *</label>
        <div class="col-lg-6">
          <?php $clients = PersonData::getClients(); ?>
          <select name="selCliente" class="form-control" required="required">
            <option value="">-- NINGUNO --</option>
            <?php foreach($clients as $client):?>
              <option value="<?php echo $client->id;?>" <?php if($client->id == $orden->person_id){ ?> selected="selected" <?php }?>><?php echo $client->name." ".$client->lastname;?></option>
            <?php endforeach;?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-3 control-label">Fecha de Evaluación *</label>
        <div class="col-lg-3">
          <input type="date" name="fecha_evaluacion" value="<?php echo convertir_fecha2($orden->created_at)?>" class="form-control" required>
        </div>
      </div>
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-3 control-label">Técnico Evaluador</label>
        <div class="col-lg-3">
          <input type="text" name="tecnico_evaluador" class="form-control" value="<?php echo $orden->tecnico_evaluador ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-3 control-label">Descripción del Producto/Servicio *</label>
        <div class="col-lg-9">
          <textarea name="descripcion" class="form-control" rows="2" required="required"><?php echo $orden->descripcion?></textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-3 control-label">Diagnostico y Fallas</label>
        <div class="col-lg-9">
          <textarea name="diagnostico" class="form-control" rows="2"><?php echo $orden->diagnostico?></textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-3 control-label">Mano de Obra</label>
        <div class="col-lg-9">
          <input type="number" name="mano_obra" value="<?php echo $orden->mano_obra ?>" class="form-control">
        </div>
      </div>
      <h5 class="text-center"><b>Datos del Equipo</b></h5>
      <input type="hidden" name="activo_id" value="<?php echo $orden->activo_id ?>">
      <div class="form-group">
        <label for="inputEmail1" class="col-lg-3 control-label">Nombre *</label>
            <div class="col-lg-6">
              <input type="text" name="nombre_equipo" class="form-control" value="<?php echo $activo->nombre ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail1" class="col-lg-3 control-label">Modelo</label>
            <div class="col-lg-6">
              <input type="text" name="modelo_equipo" class="form-control" value="<?php echo $activo->modelo ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail1" class="col-lg-3 control-label">Serie</label>
            <div class="col-lg-6">
              <input type="text" name="serie_equipo" class="form-control" value="<?php echo $activo->serie ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail1" class="col-lg-3 control-label">Tipo</label>
            <div class="col-lg-3">
              <input type="text" name="tipo_equipo" class="form-control" value="<?php echo $activo->tipo ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail1" class="col-lg-3 control-label">Fecha de Fabricación</label>
            <div class="col-lg-6">
              <input type="date" name="fecha_fabricacion" class="form-control" value="<?php echo $activo->fecha_fabricacion ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputEmail1" class="col-lg-3 control-label">Fecha de Compra</label>
            <div class="col-lg-6">
              <input type="date" name="fecha_compra" class="form-control" value="<?php echo $activo->fecha_compra ?>">
            </div>
        </div>
        <p class="alert alert-info">* Campos obligatorios</p>
          <div class="form-group">
              <div class="col-lg-offset-3 col-lg-9">
                <button type="submit" class="btn btn-primary">Editar Orden de Trabajo</button>
              </div>
          </div>
      </form>
	</div>
</div>

<?php
  function convertir_fecha2($fecha)
  {
    $date = date_create($fecha);
    return date_format($date, 'Y-m-d');
  }
?>
