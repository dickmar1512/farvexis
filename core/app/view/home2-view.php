<?php 
$fecha_inicio = date("Y-m")."-01";
$fecha_final  = date("Y-m-d");
$operventas   = SellData::getAllByDateOp($fecha_inicio,$fecha_final,2);
$opercompras   = SellData::getAllByDateOp($fecha_inicio,$fecha_final,1);
$ventasmes   = 0;
$comprasmes   = 0;
$cantidadventas = count($operventas);
$cantidadcompras = count($opercompras);
$mes = "Enero";



foreach($operventas as $operation):
$ventasmes += ($operation->total-$operation->discount);
endforeach; 

foreach($opercompras as $operation2):
//$comprasmes += ($operation2->total-$operation2->discount);
  $comprasproductos = OperationData::getAllProductsBySellId($operation2->id);
  foreach($comprasproductos as $prodcompra){
    $product  = $prodcompra->getProduct();
    $comprasmes += $prodcompra->q*$product->price_in;
  }
 endforeach; 



if(date("m")==1) $mes = "Enero";
if(date("m")==2)   $mes = "Febrero";
if(date("m")==3) { $mes = "Marzo";}
if(date("m")==4) { $mes = "Abril";}
if(date("m")==5) { $mes = "Mayo";}
if(date("m")==6) { $mes = "Junio";}
if(date("m")==7) { $mes = "Julio";}
if(date("m")==8) { $mes = "Agosto";}
if(date("m")==9) { $mes = "Setiembre";}
if(date("m")==10) { $mes = "Octubre";}
if(date("m")==11) { $mes = "Noviembre";}
if(date("m")==12) { $mes = "Diciembre";}

//echo $mes;

?>



<div class="row">
  <div class="col-md-12">
    <h1><b>Bienvenido: <i class='fa fa-rocket'></i> YAQHAPLUS  a tu servicio</b></h1>
    <br>




  </div>
</div>
<div class="row">
  <div class="col-lg-2 col-xs-4">
    <div class="small-box bg-aqua">
      <div class="inner">

        <h3><i class='fa fa-glass'></i> <?php echo count(ProductData::getAll());?></h3>
        <p>Productos / Servicios registrados</p>
      </div>
      <div class="icon">
        <i class="ion ion-bag"></i>
      </div>
      <a href="./?view=products" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-2 col-xs-4">
    <div class="small-box bg-purple">
      <div class="inner">
        <h3><i class='fa fa-user'></i> <?php echo count(PersonData::getClients());?></h3>
        <p>Clientes (Personas / Empresas)</p>
      </div>
      <div class="icon">
        <i class="ion ion-stats-bars"></i>
      </div>
      <a href="./?view=clients" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
    </div>
  </div>        
  <div class="col-lg-2 col-xs-4">
    <div class="small-box bg-yellow">
      <div class="inner">
        <h3><i class='fa fa-truck'></i>  <?php echo count(PersonData::getProviders());?></h3>
        <p>Proveedores de compras</p>
      </div>
      <div class="icon">
        <i class="ion ion-person-add"></i>
      </div>
      <a href="./?view=providers" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-2 col-xs-4">
    <div class="small-box bg-red">
      <div class="inner">
        <h3><i class='fa fa-database'></i> <?php echo count(CategoryData::getAll());?></h3>
        <p>Categorias de los productos o servicios</p>
      </div>
      <div class="icon">
        <i class="ion ion-pie-graph"></i>
      </div>
      <a href="./?view=categories" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
    </div>
  </div>

    <div class="col-lg-2 col-xs-4">
    <div class="small-box bg-green">
      <div class="inner">
        <h3><i class='fa fa-shopping-cart'></i> <?php echo $cantidadcompras;?><p><?php echo "Compras ".$mes; ?></p></h3>
        <p> S/ <?php echo number_format($comprasmes,2,'.',',');; ?></p>
      </div>
      <div class="icon">
        <i class="ion ion-pie-graph"></i>
      </div>
      <a href="./?view=categories" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
    </div>
  </div>

    <div class="col-lg-2 col-xs-4">
    <div class="small-box bg-orange">
      <div class="inner">
        <h3><i class='fa fa-cart-plus'></i> <?php echo $cantidadventas;?><p><?php echo "Ventas ".$mes; ?></p></h3>
        <p> S/ <?php echo number_format($ventasmes,2,'.',','); ?></p>
      </div>
      <div class="icon">
        <i class="ion ion-pie-graph"></i>
      </div>
      <a href="./?view=sellreports&client_id=&sd=<?php echo $fecha_inicio; ?>&ed=<?php echo $fecha_final; ?>" class="small-box-footer">Ver mas <i class="fa fa-arrow-circle-right"></i></a>
    </div>
  </div>




<!-- $ventasmes   = 0;
$comprasmes   = 0
$cantidadcompras = print_r($opercompras);
$cantidadventas = print_r($operventas); -->


</div>
<div class="row">
  
  <div class="col-md-5">

<?php 
    
//LOS 5 PRODUCTOS MAS VENDIDOS
$ventasproductos   = OperationData::getAllByDateVendido($fecha_final,$fecha_final);
$producto1="";
$producto2="";
$producto3="";
$producto4="";
$producto5="";
$producto1venta=0;
$producto2venta=0;
$producto3venta=0;
$producto4venta=0;
$producto5venta=0;
$contar=0;

foreach($ventasproductos as $productotop):
  $contar++;
  if ($contar==1) {
    $ppp = ProductData::getById($productotop->product_id);

    $cantidadproductos   = 
    OperationData::getAllByDateVendidoProducto($fecha_final,$fecha_final,$productotop->product_id);
    foreach($cantidadproductos as $qqq){$producto1venta +=$qqq->q; }
    $productotop->product_id;
    $producto1 = $ppp->name;
  }
  if ($contar==2) {
    $ppp = ProductData::getById($productotop->product_id);

    $cantidadproductos   = 
    OperationData::getAllByDateVendidoProducto($fecha_final,$fecha_final,$productotop->product_id);
    foreach($cantidadproductos as $qqq){$producto2venta +=$qqq->q; }
    $producto2 = $ppp->name;
  }
  if ($contar==3) {
    $ppp = ProductData::getById($productotop->product_id);
    $producto3 = $ppp->name;

    $cantidadproductos   = 
    OperationData::getAllByDateVendidoProducto($fecha_final,$fecha_final,$productotop->product_id);
    foreach($cantidadproductos as $qqq){$producto3venta +=$qqq->q; }
  }
  if ($contar==4) {
    $ppp = ProductData::getById($productotop->product_id);
    $producto4 = $ppp->name;


    $cantidadproductos   = 
    OperationData::getAllByDateVendidoProducto($fecha_final,$fecha_final,$productotop->product_id);
    foreach($cantidadproductos as $qqq){$producto4venta +=$qqq->q; }
  }
  if ($contar==5) {
    $ppp = ProductData::getById($productotop->product_id);
    $producto5 = $ppp->name;


    $cantidadproductos   = 
    OperationData::getAllByDateVendidoProducto($fecha_final,$fecha_final,$productotop->product_id);
    foreach($cantidadproductos as $qqq){$producto5venta +=$qqq->q; }
  }
  
endforeach;


 ?>

<h1><center><b>¿Que vendí más hoy?</b></center></h1>
<div id="graph"></div>
<pre id="code" class="prettyprint linenums hide">
Morris.Donut({
  element: 'graph',
  data: [
    {value: <?php echo $producto1venta ?>, label: '<?php echo $producto1 ?>'},
    {value: <?php echo $producto2venta ?>, label: '<?php echo $producto2 ?>'},
    {value: <?php echo $producto3venta ?>, label: '<?php echo $producto3 ?>'},
    {value: <?php echo $producto4venta ?>, label: '<?php echo $producto4 ?>'}
  ],
  backgroundColor: '#ccc',
  labelColor: '#030340',
  colors: [
    '#030340',
    '#0D0975',
    '#17157D',
    '#3932C7'
  ],
  formatter: function (x) { return x + " ventas"}
});
</pre>

  </div>
  <div class="col-md-7">

    <h1><center><b>¿Necesitas abastecer?</b></center></h1>
  <?php
  
    $products = ProductData::getAlertasInventario();

    if(count($products)>0)
    {
      ?>
        <br>
        <table class="table table-bordered table-hover">
          <thead>
            <th >Codigo</th>
            <th>Nombre del producto</th>
            <th>En Stock</th>
            <th></th>
          </thead>
    	    <?php
            foreach($products as $product)
            { 
              ?>
                <tr class="<?php if($q==0){ echo "danger"; }else if($q<=$product->inventary_min/2){ echo "danger"; } else if($q<=$product->inventary_min){ echo "warning"; } ?>">
                  <td><?php echo $product->barcode; ?></td>
                  <td><?php echo $product->name; ?></td>
                  <td><?php echo $product->stock; ?></td>
                  <td>
                    <?php 
                      if($product->stock == 0)
                      { 
                        echo "<span class='label label-danger'>No hay existencias.</span>";
                      }
                      else if($product->stock <= $product->inventary_min/2)
                      { 
                        echo "<span class='label label-danger'>Quedan muy pocas existencias.</span>";
                      } 
                      else if($product->stock <= $product->inventary_min)
                      { 
                        echo "<span class='label label-warning'>Quedan pocas existencias.</span>";
                      } 
                    ?>
                  </td>
                </tr>
              <?php              
            }
          ?>
        </table>
        <div class="clearfix"></div>
      <?php
    }
    else
    {
      ?>
        <div class="jumbotron">
	        <h2>No hay alertas</h2>
          <p>Por el momento no hay alertas de inventario, estas se muestran cuando el inventario ha alcanzado el nivel minimo.</p>
        </div>
     <?php
    }
  ?>
  <br><br><br><br><br><br><br><br><br><br>

  </div>

</div>
</div>