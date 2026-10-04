<div class="row">
	<div class="col-md-12">
		<h1><i class='glyphicon glyphicon-shopping-cart'></i> Lista de Ventas</h1>
		<div class="clearfix"></div>

	<?php
		$products = SellData::getProformas();

		if(count($products)>0)
		{
			?>
				<br>
				<table class="table table-bordered table-hover	">
					<thead>
						<th></th>
						<th>Producto</th>
						<th>Total</th>
						<th>Fecha</th>
						<th></th>
					</thead>
					<?php foreach($products as $sell):?>

					<tr>
						<td style="width:30px;">
							<a href="<?= url('proforma', ['id' => $sell->id]) ?>" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-eye-open"></i></a>
						</td>
						<td>
							<?php
								$operations = OperationData::getAllProductsBySellId($sell->id);
								echo count($operations);
							?>
						<td>
							<?php
								$total= $sell->total-$sell->discount;
								echo "<b>S/ ".number_format($total)."</b>";
							?>
						</td>
						<td style="width:30px;"><button type="button" data-id="<?php echo $sell->id; ?>" class="btn btn-xs btn-danger btn-delsell"><i class="fa fa-trash"></i></button></td>
					</tr>
				<?php endforeach; ?>

				</table>

<script>
$(document).on('click', '.btn-delsell', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var row = $(this).closest('tr');
    Swal.fire({
        title: '¿Cancelar proforma/venta?',
        text: 'Esta acción cancelará el registro.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('./?action=delsell', { id: id }, function(res) {
                if (res.status === 'success') {
                    Swal.fire('Cancelado', res.message, 'success');
                    row.fadeOut(400, function() { $(this).remove(); });
                } else {
                    Swal.fire('Error', res.message || 'Error al cancelar', 'error');
                }
            }, 'json').fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error en la petición', 'error');
            });
        }
    });
});
</script>

<div class="clearfix"></div>

	<?php
}else{
	?>
	<div class="jumbotron">
		<h2>No hay ventas</h2>
		<p>No se ha realizado ninguna venta.</p>
	</div>
	<?php
}

?>
<br><br><br><br><br><br><br><br><br><br>
	</div>
</div>