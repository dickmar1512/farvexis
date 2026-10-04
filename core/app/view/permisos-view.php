<div class="row">
	<div class="col-md-12">
		<h1>Configuracion</h1>
		<?php
			$permisos = PermisoData::get_permisos();
		?>
		<div class="table-responsive">
			<table class="table table-compressed table-hover" style="width: 100%;">
			    <thead>
			    	<tr>
			        	<th class="text-center">N°</th>
			        	<th class="text-center">PERMISO</th>
			        	<th class="text-center">HABILITADO</th>
			        	<th class="text-center">DENEGADO</th>
			      	</tr>
			    </thead>
		    	<tbody>
		    		<?php
		    			$nro = 1;
		    			foreach ($permisos as $per)
		    			{
		    				$valor = PermisoData::get_permiso_x_key($per->Per_Key);

		    			 	?>
		    			 		<tr>
									<td class="text-center"><?php echo $nro ?></td>
									<td><?php echo $per->Per_Nombre ?></td>
									<td class="text-center">
                                    	<input type="radio" name="<?php echo $per->Per_IdPermiso ?>" id="opt_1_<?php echo $per->Per_IdPermiso ?>" value="1" <?php if($valor->Pee_Valor == 1){ ?> checked="checked" <?php } ?>>
                                    </td>
                                    <td class="text-center">
                                    	<input type="radio" name="<?php echo $per->Per_IdPermiso ?>" id="opt_1_<?php echo $per->Per_IdPermiso ?>" value="0" <?php if($valor->Pee_Valor == 0){ ?> checked="checked" <?php } ?>>
                                    </td>
								</tr>
		    			 	<?php
		    			 	$nro++;
		    			} 
		    		?>
		    	</tbody>
		  </table>
		</div>
	</div>
</div>