
<?php
$fecha_inicio = date("Y-m") . "-01";
$fecha_final = date("Y-m-d");
$operventas = SellData::getAllByDateOp($fecha_inicio, $fecha_final, 2);
$opercompras = SellData::getAllByDateOp($fecha_inicio, $fecha_final, 1);
$ventasmes = 0;
$comprasmes = 0;
$cantidadventas = count($operventas);
$cantidadcompras = count($opercompras);
$admin = UserData::getById($_SESSION["user_id"])->is_admin;
$mes = "Enero";

foreach ($operventas as $operation):
  $ventasmes += ($operation->total - $operation->discount);
endforeach;

foreach ($opercompras as $operation2):
  //$comprasmes += ($operation2->total-$operation2->discount);
  $comprasproductos = OperationData::getAllProductsBySellId($operation2->id);
  foreach ($comprasproductos as $prodcompra) {
    $product = $prodcompra->getProduct();
    $comprasmes += $prodcompra->q * $product->price_in;
  }
endforeach;

if (date("m") == 1)
  $mes = "Enero";
if (date("m") == 2)
  $mes = "Febrero";
if (date("m") == 3) {
  $mes = "Marzo";
}
if (date("m") == 4) {
  $mes = "Abril";
}
if (date("m") == 5) {
  $mes = "Mayo";
}
if (date("m") == 6) {
  $mes = "Junio";
}
if (date("m") == 7) {
  $mes = "Julio";
}
if (date("m") == 8) {
  $mes = "Agosto";
}
if (date("m") == 9) {
  $mes = "Setiembre";
}
if (date("m") == 10) {
  $mes = "Octubre";
}
if (date("m") == 11) {
  $mes = "Noviembre";
}
if (date("m") == 12) {
  $mes = "Diciembre";
}

//echo $mes;

?>
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><i class="nav-icon fas fa-tachometer-alt"></i> Dashboard</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Inicio</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-2 col-xs-4">
        <div class="small-box bg-cyan">
          <div class="inner">

            <h3><i class='fa fa-glass'></i> <?php echo count(ProductData::getAll()); ?></h3>
            <p>Productos / Servicios</p>
          </div>
          <div class="icon">
            <i class="ion ion-bag"></i>
          </div>
          <?php if ($admin == 1) { ?>
            <a href="./?view=products" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
          <?php } ?>
        </div>
      </div>
      <div class="col-lg-2 col-xs-4">
        <div class="small-box bg-purple">
          <div class="inner">
            <h3><i class='fa fa-user'></i> <?php echo count(PersonData::getClients()); ?></h3>
            <p>Clientes</p>
          </div>
          <div class="icon">
            <i class="ion ion-stats-bars"></i>
          </div>
          <?php if ($admin == 1) { ?>
            <a href="./?view=clients" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
          <?php } ?>
        </div>
      </div>
      <div class="col-lg-2 col-xs-4">
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3><i class='fa fa-truck'></i> <?php echo count(PersonData::getProviders()); ?></h3>
            <p>Proveedores</p>
          </div>
          <div class="icon">
            <i class="ion ion-person-add"></i>
          </div>
          <?php if ($admin == 1) { ?>
            <a href="./?view=providers" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
          <?php } ?>
        </div>
      </div>
      <div class="col-lg-2 col-xs-4">
        <div class="small-box bg-red">
          <div class="inner">
            <h3><i class='fa fa-database'></i> <?php echo count(CategoryData::getAll()); ?></h3>
            <p>Categorias</p>
          </div>
          <div class="icon">
            <i class="ion ion-pie-graph"></i>
          </div>
          <?php if ($admin == 1) { ?>
            <a href="./?view=categories" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
          <?php } ?>
        </div>
      </div>

      <div class="col-lg-2 col-xs-4">
        <div class="small-box bg-green">
          <div class="inner">
            <h3><i class='fa fa-shopping-cart'></i> <?php echo $cantidadcompras; ?></h3>
            <p><?php echo "Compras " . $mes . ": S/ " . number_format($comprasmes, 2, '.', ','); ?></p>
          </div>
          <div class="icon">
            <i class="ion ion-pie-graph"></i>
          </div>
          <?php if ($admin == 1) { ?>
            <a href="./?view=res" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
          <?php } ?>
        </div>
      </div>

      <div class="col-lg-2 col-xs-4">
        <div class="small-box bg-orange">
          <div class="inner">
            <h3><i class='fa fa-cart-plus'></i> <?php echo $cantidadventas; ?></h3>
            <p><?php echo "Ventas " . $mes . ": S/ " . number_format($ventasmes, 2, '.', ','); ?></p>
          </div>
          <div class="icon">
            <i class="ion ion-pie-graph"></i>
          </div>
          <?php if ($admin == 1) { ?>
            <a href="./?view=sellreports&client_id=&sd=<?php echo $fecha_inicio; ?>&ed=<?php echo $fecha_final; ?>"
              class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="row">
      <!-- Left col -->
      <section class="col-lg-4 connectedSortable">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">
              <b>¿Que vendí más hoy?</b>
            </h3>
          </div>
          <div class="card-body">
            <?php
            //LOS 5 PRODUCTOS MAS VENDIDOS
            $ventasproductos = OperationData::getAllByDateVendido($fecha_inicio, $fecha_final);
            $producto1 = "";
            $producto2 = "";
            $producto3 = "";
            $producto4 = "";
            $producto5 = "";
            $producto6 = "";
            $producto1venta = 0;
            $producto2venta = 0;
            $producto3venta = 0;
            $producto4venta = 0;
            $producto5venta = 0;
            $producto6venta = 0;
            $contar = 0;

            foreach ($ventasproductos as $productotop):
              $contar++;

              $ppp = ProductData::getById($productotop->product_id);

              $cantidadproductos =
                OperationData::getAllByDateVendidoProducto($fecha_inicio, $fecha_final, $productotop->product_id);

              if ($contar == 1) {
                $producto1 = $ppp->name;

                foreach ($cantidadproductos as $qqq) {
                  $producto1venta += $qqq->q;
                }
              }
              if ($contar == 2) {
                $producto2 = $ppp->name;

                foreach ($cantidadproductos as $qqq) {
                  $producto2venta += $qqq->q;
                }
              }
              if ($contar == 3) {
                $producto3 = $ppp->name;

                foreach ($cantidadproductos as $qqq) {
                  $producto3venta += $qqq->q;
                }
              }
              if ($contar == 4) {
                $producto4 = $ppp->name;

                foreach ($cantidadproductos as $qqq) {
                  $producto4venta += $qqq->q;
                }
              }
              if ($contar == 5) {
                $producto5 = $ppp->name;

                foreach ($cantidadproductos as $qqq) {
                  $producto5venta += $qqq->q;
                }
              }

              if ($contar == 6) {
                $producto5 = $ppp->name;

                foreach ($cantidadproductos as $qqq) {
                  $producto5venta += $qqq->q;
                }
              }
            endforeach;
            ?>
            <canvas id="top6" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;">
            </canvas>
          </div>
      </section>
      <!-- end left col -->
      <!-- right col -->
      <section class="col-lg-8 connectedSortable">
        <div class="card card-success">
          <div class="card-header">
            <h3 class="card-title"><b>¿Necesitas abastecer?</b></h3>
          </div>

          <div class="card-body">
            <?php
            $products = ProductData::getAlertasInventario();
            $item = 0;

            if (count($products) > 0) {
              ?>
              <table id="gridAbastecer" class="table table-bordered table-striped datatable">
                <thead class="thead-dark">
                  <th>#</th>
                  <th>Nombre del producto</th>
                  <th>En Stock</th>
                  <th>Fecha Vencimiento</th>
                  <th>Estado</th>
                </thead>
                <tbody>
                  <?php
                  foreach ($products as $product) {
                    $q = $product->stock;
                    $item++;
                    // Verificar si la fecha es nula o vacía
                    if (empty($product->fecha_venc)) {
                        $fecha_venc = strtotime('2099-01-31 00:00:00');
                        $dias_restantes = ceil(($fecha_venc - time()) / (60 * 60 * 24));
                        $badge_class = 'secondary';
                        $mensaje = 'Sin fecha de vencimiento';
                        $fecha_formateada = '31/01/2099';
                    } else {
                        $fecha_venc = strtotime($product->fecha_venc);
                        $dias_restantes = ceil(($fecha_venc - time()) / (60 * 60 * 24));
                        $fecha_formateada = date('d/m/Y', $fecha_venc);
                        
                        // Determinar clase del badge y mensaje según días restantes
                        if($dias_restantes <= 0) {
                            $badge_class = 'danger';
                            $mensaje = 'Vence: ' . $fecha_formateada. ' - ¡Vencido!, hace '.abs($dias_restantes).' dias';
                        }elseif ($dias_restantes <= 90) {
                            $badge_class = 'danger';
                            $mensaje = 'Vence: ' . $fecha_formateada. ' - ¡Faltan ' . $dias_restantes . ' días para vencer!';
                        } elseif ($dias_restantes <= 120) {
                            $badge_class = 'warning';
                            $mensaje = 'Vence: ' . $fecha_formateada. ' - ¡Faltan ' . $dias_restantes . ' días para vencer!';
                        } else {
                            $badge_class = 'success';
                            $mensaje = 'Vence: ' . $fecha_formateada;
                        }
                    }
                    ?>
                    <tr class="<?php if ($q == 0) {
                      echo "bg-gradient-danger";
                    } else if ($q <= $product->inventary_min / 2) {
                      echo "bg-gradient-warning";
                    } else if ($q <= $product->inventary_min) {
                      echo "bg-gradient-info";
                    } ?>">
                      <td><?php echo $item; ?></td>
                      <td><?php echo $product->name; ?></td>
                      <td><?php echo $product->stock; ?></td>
                      <td>
                        <small class="badge badge-<?php echo $badge_class; ?> border text-<?php echo ($badge_class == 'warning' ? 'dark' : 'white'); ?>">
                          <?php echo $mensaje; ?>
                        </small>
                      </td>
                      <td>
                        <?php
                        if ($product->stock == 0) {
                          echo "<span class='badge bg-danger'>No hay existencias.</span>";
                        } else if ($product->stock <= $product->inventary_min / 2) {
                          echo "<span class='badge bg-warning'>Quedan muy pocas existencias.</span>";
                        } else if ($product->stock <= $product->inventary_min) {
                          echo "<span class='badge bg-info'>Quedan pocas existencias.</span>";
                        }
                        ?>
                      </td>
                    </tr>
                    <?php
                  }
                  ?>
                </tbody>
              </table>
              <div class="clearfix"></div>
              <?php
            } else {
              ?>
              <div class="jumbotron">
                <h2>No hay alertas</h2>
                <p>Por el momento no hay alertas de inventario, estas se muestran cuando el inventario ha alcanzado el
                  nivel minimo.</p>
              </div>
              <?php
            }
            ?>
          </div>
        </div>
      </section>
      <!-- end right col -->
    </div>
  </div>
  </div>
  </div>
  <script>
    window.onload = function () {
      var ctx = document.getElementById('top6').getContext('2d');
      chartdonutTop6(
        ctx,
        '<?= $producto1; ?>',
        '<?= $producto2; ?>',
        '<?= $producto3; ?>',
        '<?= $producto4; ?>',
        '<?= $producto5; ?>',
        '<?= $producto6; ?>',
        <?= $producto1venta; ?>,
        <?= $producto2venta; ?>,
        <?= $producto3venta; ?>,
        <?= $producto4venta; ?>,
        <?= $producto5venta; ?>,
        <?= $producto6venta; ?>);
    }
  </script>