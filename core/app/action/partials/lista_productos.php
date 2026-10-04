<div style="max-height: 200px;overflow-y: scroll">
	<table class="table">
		<thead>
			<tr>
				<th>Código</th>
				<th>Nombre</th>
				<th>Precio Unitario</th>
				<th>En Inventario</th>
				<th>Cantidad</th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($productos as $pro)
				{
					?>
						<tr>
							<td><?php echo $pro['barcode'] ?></td>
							<td><?php echo $pro['name'] ?></td>
							<td><?php echo $pro['price_out'] ?></td>
							<td><?php echo $pro['stock'] ?></td>
							<td>
								<input type="number" name="cantidad" class="form-control" value="1" id="cantidad_<?php echo $pro['id']?>">
							</td>
							<td>
								<button class="btn btn-success btn-sm">Agregar</button>
							</td>
						</tr>
					<?php
				}
			?>
		</tbody>
	</table>
</div>