<input type="hidden" name="person_id" value="<?php echo $dato['id']?>">
<div class="form-group">
  <label for="inputEmail1" class="col-lg-2 control-label">CLIENTE:</label>
  <div class="col-md-8">
    <input type="text" name="rznSocialUsuario" class="form-control" id="rznSocialUsuario" placeholder="Cliente(Opcional)" value="<?php echo $dato['name'] ?>" required="">
  </div>
</div>
<div class="form-group">
  <label for="inputEmail1" class="col-lg-2 control-label">Distrito</label>
  <div class="col-md-6">
    <select name="codUbigeoCliente" class="form-control" id="distrito_cliente">
      <option value="">::Seleccione::</option>
      <option value="160101" <?php if ($dato['ubigeo'] == 160101){ ?> selected <?php }?>>Iquitos</option>
      <option value="160108" <?php if ($dato['ubigeo'] == 160108){ ?> selected <?php }?>>Punchana</option>
      <option value="160112" <?php if ($dato['ubigeo'] == 160112){ ?> selected <?php }?>>Belén</option>
      <option value="160113" <?php if ($dato['ubigeo'] == 160113){ ?> selected <?php }?>>San Juan Bautista</option>
    </select>
  </div>
</div>
<div class="form-group">
  <label for="inputEmail1" class="col-lg-2 control-label">Dirección</label>
  <div class="col-md-8">
    <input type="text" name="desDireccionCliente" class="form-control" id="direccion_cliente" placeholder="Direccion" value="<?php echo $dato['address1']?>">
  </div>
</div>