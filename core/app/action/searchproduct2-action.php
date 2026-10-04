<?php if(isset($_GET["product"]) && $_GET["product"]!=""):?>
	<?php
$products = ProductData::getLike($_GET["product"]);
if(count($products)>0){
	?>
		<h3>Resultados de la Busqueda</h3>
<table class="table table-bordered table-hover">
	<thead>
		<th>Codigo</th>
		<th>Nombre</th>
		<th>Unidad de medida</th>
		<th>Precio unitario</th>
		<th>En inventario</th>
		<th> Precio /Cantidad</th>
		<th></th>
	</thead>
	<?php
	$products_in_cero=0;

	foreach($products as $product):
	 	$q = $product->stock;
	?>
	<?php 
	if($q > 0 or $product->is_stock == 0):?>
		
	<tr class="<?php if($q<=$product->inventary_min){ echo "danger"; }?>">
		<td style="width:80px;"><?php echo $product->barcode; ?></td>
		<td><?php echo $product->name; ?></td>
		<td>
			<?php 
				$unidad = UnidadMedidaData::getById($product->unit);
				echo $unidad->sigla; 
			?>
		</td>
		<td><b>S/ <?php echo $product->price_out; ?></b></td>
		<td><?php echo $q; ?></td>
		<td style="width:350px;">
			<form method="post" action="./?view=addtocart2">
				<input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
				<input type="hidden" name="orden_id" value="<?php echo $_GET["orden_id"] ?>">
				<div class="input-group">
					<input type="number" step="any" class="form-control" required name="precio_unitario" placeholder="Precio Unitario" value="<?php echo $product->price_out?>" style="width: 100px" min="0">
					<input type="number" class="form-control" required name="q" placeholder="Cantidad ..." value="1" style="width: 100px; float: right;">
      				<span class="input-group-btn">
						<button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-plus-sign"></i> Agregar</button>
      				</span>
    			</div>
			</form>
		</td>
		<td>
			<a href="./?view=editproduct&id=<?php echo $product->id; ?>" class="btn btn-xs btn-warning"><i class="glyphicon glyphicon-pencil"></i></a>
		</td>
	</tr>
	
<?php else:$products_in_cero++;
?>
<?php  endif; ?>
	<?php endforeach;?>
</table>
<?php if($products_in_cero>0){ echo "<p class='alert alert-warning'>Se omitieron <b>$products_in_cero productos</b> que no tienen existencias en el inventario. <a href='index.php?module=inventary'>Ir al Inventario</a></p>"; }?>

	<?php
}else{
	echo "<br><p class='alert alert-danger'>No se encontro el producto</p>";
}
?>
<hr><br>
<?php else:
?>
<?php endif; ?>