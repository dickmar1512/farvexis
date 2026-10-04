<?php
  $empresa = EmpresaData::getDatos();
?>
<div class="row">
	<div class="col-md-12">
		<h1>Agregar repuesto necesarios, para la orden de trabajo</h1>
		<p><b>Buscar producto por nombre o por codigo:</b></p>
		<form id="searchp">
			<div class="row">
				<div class="col-md-6">
					<input type="hidden" name="view" value="repuestosordentrabajo">
					<input type="text" id="product_code" name="product" class="form-control">
					<input type="hidden" id="orden_id" name="orden_id" class="form-control" value="<?php echo $_GET["id"] ?>" >
				</div>
				<div class="col-md-3">
				<button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-search"></i> Buscar</button>
				</div>
			</div>
		</form>
	</div>
<div id="show_search_results"></div>
<script>
	//jQuery.noConflict();

	$(document).ready(function(){
		$("#searchp").on("submit",function(e){
			e.preventDefault();
			
			$.get("./?action=searchproduct2",$("#searchp").serialize(),function(data){
				$("#show_search_results").html(data);
			});
			$("#product_code").val("");
		});
	});

	$(document).ready(function(){
	    $("#product_code").keydown(function(e){
	        if(e.which==17 || e.which==74){
	            e.preventDefault();
	        }else{
	            console.log(e.which);
	        }
	    })
	});
</script>

<?php if(isset($_SESSION["errors"])):?>
<h2>Errores</h2>
<p></p>
<table class="table table-bordered table-hover">
	<tr class="danger">
		<th>Codigo</th>
		<th>Producto</th>
		<th>Mensaje</th>
	</tr>
	<?php foreach ($_SESSION["errors"]  as $error):
	$product = ProductData::getById($error["product_id"]);
	?>
	<tr class="danger">
		<td><?php echo $product->barcode; ?></td>
		<td><?php echo $product->name; ?></td>
		<td><b><?php echo $error["message"]; ?></b></td>
	</tr>

	<?php endforeach; ?>
</table>
<?php
unset($_SESSION["errors"]);
 endif; ?>

<!--- Carrito de compras :) -->
<?php if(isset($_SESSION["cart2"])):
$total = 0;
?>
<div class="col-md-12">
	<hr>
	<h2>Lista de venta</h2>
	<table class="table table-bordered table-hover">
		<thead>
			<th style="width:30px;">Codigo</th>
			<th style="width:30px;">Cantidad</th>
			<th style="width:30px;">Unidad</th>
			<th>Producto</th>
			<th style="width:35px;">Precio Unitario</th>
			<th style="width:70px;">Precio Total</th>
			<th ></th>
		</thead>
		<?php foreach($_SESSION["cart2"] as $p):
			$product = ProductData::getById($p["product_id"]);
			?>
			<tr >
				<td><?php echo $product->barcode; ?></td>
				<td ><?php echo $p["q"]; ?></td>
				<td>
					<?php 
						$unidad = UnidadMedidaData::getById($product->unit);
						echo $unidad->sigla; 
					?>
				</td>
				<td><?php echo $product->name; ?></td>
				<td><b>S/ <?php echo number_format($p["precio_unitario"], 2, '.', ','); ?></b></td>
				<td><b>S/ <?php  $pt = $p["precio_unitario"]*$p["q"]; $total +=$pt; echo number_format($pt, 2, '.', ','); ?></b></td>
				<td style="width:30px;"><button onclick="cancelarRepuesto(<?php echo $product->id; ?>, <?php echo $_GET['id']; ?>)" class="btn btn-danger"><i class="glyphicon glyphicon-remove"></i> Cancelar</button></td>
			</tr>
		<?php endforeach; ?>
		<tfoot>
			<tr>
				<td colspan="4"></td>
				<td><p>Total</p></td>
				<td><p><b>S/ <?php echo number_format($total,2,'.',','); ?></b></p></td>
			</tr>
		</tfoot>
	</table>
	<form action="./?action=addrepuestos" class="form-horizontal" method="post">
		<input type="hidden" name="orden_id" value="<?php echo $_GET['id'] ?>">
		<div class="form-group">
		    <div class="col-lg-offset-2 col-lg-10">
		    	<div class="checkbox">
		        	<label>
						<button type="button" onclick="cancelarTodoRepuesto(<?php echo $_GET['id']; ?>)" class="btn btn-lg btn-danger"><i class="glyphicon glyphicon-remove"></i> Cancelar</button>
		        		<button class="btn btn-lg btn-primary">Agregar Repuestos</button>
		        	</label>
		      	</div>
		    </div>
		</div>
	</form>
</div>

</div>
</div>

<br><br><br><br><br>
<?php endif; ?>

</div>

<script>
function cancelarRepuesto(productId, ordenId) {
    Swal.fire({ title: '¿Quitar repuesto?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí', cancelButtonText: 'Cancelar' })
    .then(function(result) {
        if (result.isConfirmed) {
            $.get('./?action=clearcart2', { product_id: productId, orden_id: ordenId }, function(response) {
                location.reload();
            }, 'json');
        }
    });
}
function cancelarTodoRepuesto(ordenId) {
    Swal.fire({ title: '¿Cancelar todos los repuestos?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí', cancelButtonText: 'No' })
    .then(function(result) {
        if (result.isConfirmed) {
            $.get('./?action=clearcart2', { orden_id: ordenId }, function(response) {
                window.location.href = BASE_URL + '/repuestosordentrabajo/' + ordenId;
            }, 'json');
        }
    });
}
</script>