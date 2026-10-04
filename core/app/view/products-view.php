<!-- Content Header (Page header) -->
<div class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 font-weight-bold">
					<i class="fa fa-list text-primary"></i> Lista de Productos y Servicios
					<button type="button" id="btn_sync_stock" class="btn btn-outline-info btn-sm ml-2" title="Sincronizar Stock Real (Reparar inconsistencias)">
						<i class="fas fa-sync-alt"></i> Sincronizar
					</button>
				</h1>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">Productos - Servicios</a></li>
					<li class="breadcrumb-item active">Lista</li>
				</ol>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<div class="card card-default">
			<div class="card-header">
				<div class="row align-items-center">
					<!-- ===== FILTRO POR TIPO DE PRODUCTO (izquierda) ===== -->
					<div class="col">
						<div class="d-flex align-items-center flex-wrap gap-2" id="filtroTipoProducto">
							<span class="text-xs font-weight-bold text-muted mr-2">
								<i class="fas fa-filter"></i> Filtrar por tipo:
							</span>
							<button class="btn btn-sm btn-primary shadow-sm filtro-btn active" id="filtro-todos" data-filtro="todos">
								<i class="fas fa-list"></i> Todos
							</button>
							<button class="btn btn-sm btn-outline-success shadow-sm filtro-btn" id="filtro-medicamentos" data-filtro="medicamento">
								<i class="fas fa-pills"></i> Medicamentos
								<span class="badge badge-light ml-1" id="badge-medicamentos">-</span>
							</button>
							<button class="btn btn-sm btn-outline-secondary shadow-sm filtro-btn" id="filtro-otros" data-filtro="otro">
								<i class="fas fa-box"></i> Otros / Servicios
								<span class="badge badge-light ml-1" id="badge-otros">-</span>
							</button>
						</div>
					</div>
					<!-- ===== FIN FILTRO ===== -->

					<!-- ===== BOTÓN NUEVO PRODUCTO (derecha) ===== -->
					<div class="col-auto">
						<button id="openModalNuevoProducto" class="btn btn-primary">
							<i class="fas fa-box-open"></i>
							Agregar Producto o Servicio
						</button>
					</div>
					<!-- ===== FIN BOTÓN ===== -->
				</div>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<div class="row" style="display: flex; justify-content: center;">
					<div class="col-md-10">
						<?php
						$products = OperationData::getProductsWithMovement();
						if (count($products) > 0) { ?>
							<table class="table table-bordered table-striped table-hover table-sm datatable" id="gridProducts" style="width:100%">
								<thead class="thead-dark text-xs">
									<tr>
										<th>Cód Digemid</th>
										<!-- <th>Imagen</th> -->
										<th>Nombre / Laboratorio</th>
										<!-- <th class="text-center">P. Entrada</th> -->
										<th class="text-center">P. Caja</th>
										<th class="text-center text-primary">P. Unitario</th>
										<!-- <th class="text-center">Anaquel</th> -->
										<th class="text-center bg-info">Stock Real</th>
										<th class="text-center">Min.</th>
										<th class="text-center">Acciones</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($products as $product): ?>
									<?php
										$cod = trim((string)$product->cod_digemid);
										$esMedicamento = (!empty($cod) && $cod !== '0');
										$tipoProd = $esMedicamento ? 'medicamento' : 'otro';
									?>
									<tr class="text-sm fila-producto" data-tipo="<?= $tipoProd ?>">
										<td class="text-xs font-weight-bold"><?=$product->cod_digemid?></td>
										<!--<td class="text-center">
											<?php if ($product->image != ""): ?>
												<img src="storage/products/<?=$product->image?>" class="img-thumbnail" style="width:35px; height:35px; object-fit: cover;">
											<?php else: ?>
												<i class="fas fa-box text-gray"></i>
											<?php endif; ?>
										</td> -->
										<td>
											<div class="font-weight-bold text-primary"><?=$product->name?></div>
											<small class="badge badge-light border text-muted"><?=$product->laboratorio?></small>
										</td>
										<!-- <td class="text-center"><?=number_format($product->price_in, 2)?></td> -->
										<td class="text-center"><?=number_format($product->price_may, 2)?></td>
										<td class="text-center font-weight-bold text-primary"><?=number_format($product->price_out, 2)?></td>
										<!-- <td class="text-center"><?=$product->anaquel?></td> -->
										<td class="text-center font-weight-bold <?=$product->stock_real <= $product->inventary_min ? 'text-danger' : 'text-dark'?>">
											<?=number_format($product->stock_real, 0)?>
										</td>
										<td class="text-center text-muted"><?=$product->inventary_min?></td>
										<td class="text-center">
											<div class="btn-group">
												<button class="btn btn-xs btn-warning edit-product shadow-sm" data-id="<?=$product->id?>" title="Editar">
													<i class="fas fa-pencil-alt"></i>
												</button>
												<button class="btn btn-xs btn-danger delete-product shadow-sm" data-id="<?=$product->id?>" title="Eliminar">
													<i class="fa fa-trash"></i>
												</button>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
							<div class="clearfix"></div>

							<script>
							$(document).ready(function () {
								window._filtroProductoActivo = 'todos';

								// Registrar el filtro en el motor de DataTables de forma segura
								if (window.jQuery && $.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.search) {
									var filterExists = false;
									for (var i = 0; i < $.fn.dataTable.ext.search.length; i++) {
										if ($.fn.dataTable.ext.search[i].isProductTypeFilter) {
											filterExists = true;
											break;
										}
									}
									if (!filterExists) {
										var productFilter = function(settings, data, dataIndex, rowData, counter) {
											// Solo aplica a la tabla #gridProducts
											if (settings.nTable.id !== 'gridProducts') return true;
											if (window._filtroProductoActivo === 'todos') return true;

											// Leer el data-tipo del <tr> correspondiente o calcularlo de forma segura
											var row = settings.aoData[dataIndex].nTr;
											var tipo = row ? row.getAttribute('data-tipo') : null;
											if (!tipo) {
												var cod = (data[0] || '').trim();
												var esMedicamento = (cod !== '' && cod !== '0');
												tipo = esMedicamento ? 'medicamento' : 'otro';
											}
											return tipo === window._filtroProductoActivo;
										};
										productFilter.isProductTypeFilter = true;
										$.fn.dataTable.ext.search.push(productFilter);
									}
								}

								// Contar por tipo (lectura directa del DOM)
								var contMed = 0, contOtro = 0;
								$('#gridProducts tbody tr.fila-producto').each(function() {
									var tipo = $(this).data('tipo') || $(this).attr('data-tipo');
									if (tipo === 'medicamento') contMed++;
									else contOtro++;
								});
								$('#badge-medicamentos').text(contMed);
								$('#badge-otros').text(contOtro);

								// Manejar clics en los botones de filtro
								$('.filtro-btn').off('click').on('click', function() {
									var filtro = $(this).data('filtro');
									window._filtroProductoActivo = filtro;

									// Estilos activos / inactivos
									$('.filtro-btn').each(function() {
										var f = $(this).data('filtro');
										$(this).removeClass('active btn-primary btn-success btn-secondary btn-outline-primary btn-outline-success btn-outline-secondary');
										if (f === filtro) {
											$(this).addClass('active ' + (
												f === 'todos'       ? 'btn-primary'   :
												f === 'medicamento' ? 'btn-success'   :
												'btn-secondary'
											));
										} else {
											$(this).addClass(
												f === 'todos'       ? 'btn-outline-primary'   :
												f === 'medicamento' ? 'btn-outline-success'   :
												'btn-outline-secondary'
											);
										}
									});

									// Disparar redibujado del DataTable → actualiza paginado automáticamente
									if ($.fn.DataTable && $.fn.DataTable.isDataTable('#gridProducts')) {
										$('#gridProducts').DataTable().draw();
									}
								});
							});
							</script>

							<?php
						} else {
							?>
							<div class="jumbotron">
								<h2>No hay productos/servicios</h2>
								<p>No se han agregado productos/servicios a la base de datos, puedes agregar uno
									dando click
									en el
									boton
									<b>"Agregar Producto"</b>.
								</p>
							</div>
							<?php
						}

						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php
function generarCodigoBarras() {
    $primerDigito = '7';
    $resto = '';
    for ($i = 0; $i < 12; $i++) {
        $resto .= mt_rand(0, 9);
    }
    
    $codigo = $primerDigito . $resto;
    
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += ($i % 2 === 0) ? $codigo[$i] * 1 : $codigo[$i] * 3;
    }
    $digitoControl = (10 - ($sum % 10)) % 10;
    
    return $codigo . $digitoControl;
}

$categories = CategoryData::getAll();
$unidades = UnidadMedidaData::getAll();

$codigo = ProductData::getbarcode();
do {
    $barcodegen = generarCodigoBarras();
} while (in_array($barcodegen, $codigo));


$fecha_actual = date("Y-m-d");
?>
<input type="hidden" id="genbarcode" value="<?= $barcodegen?>">
<script>
    var categories = <?php echo json_encode($categories); ?>;
	var unidades = <?php echo json_encode($unidades); ?>;
	var fechaVencimiento = '<?= date("Y-m-d", strtotime($fecha_actual . "+ 1 year")) ?>';

    $('#btn_sync_stock').click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Sincronizar Stock?',
            text: 'Se recalculará el stock de todos los productos en base al kardex de operaciones.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, sincronizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sincronizando',
                    text: 'Recalculando inventario...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                $.ajax({
                    url: './?action=actstock',
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        Swal.fire({
                            title: '¡Completado!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al sincronizar inventario', 'error');
                    }
                });
            }
        });
    });
</script>