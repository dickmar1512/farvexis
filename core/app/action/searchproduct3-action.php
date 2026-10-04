<?php 
header("Content-Type: text/html;charset=utf-8");
  $idpaquete = $_GET["idpaquete"];
if(isset($_GET["product"]) && $_GET["product"]!=""):?>
	<?php
//$products = ProductData::getLike(utf8_decode($_GET["product"]));
$products = ProductData::getLike(htmlspecialchars($_GET["product"],ENT_NOQUOTES,"UTF-8"));
//htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
if(count($products)>0){
	?>
		<h3>Resultados de la Busqueda</h3>
<table class="table table-bordered table-hover">
	<thead>
		<th>Producto</th>
		<th style="width:450px;">
		<div class="label-group d-flex">
			<label style="width: 100px;" class="text-xs uppercase mb-0">Precio S/</label>
			<label style="width: 100px;" class="text-xs uppercase mb-0">Desc. S/</label>
			<label style="width: 80px;" class="text-xs uppercase mb-0">Cant.</label>
		</div>
		</th>
	</thead>
	<?php
	foreach($products as $product):
	?>
	<tr class="text-sm">
		<td class="font-weight-bold"><?php echo $product->name; ?></td>
		<td style="width:450px;">
				<div class="input-group input-group-sm">
                    <input type="number" step="any" class="form-control font-weight-bold" id="price_<?php echo $product->id; ?>" placeholder="Precio" value="<?=$product->price_out?>" style="width: 80px;">				
                    <input type="number" step="any" class="form-control text-danger" id="desc_<?php echo $product->id; ?>" placeholder="Desc." value="0.00" style="width: 80px;">				
                    <input type="number" class="form-control font-weight-bold" id="qty_<?php echo $product->id; ?>" placeholder="Cant." value="1" style="width: 70px;">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary btn-add-det shadow-sm" 
                                data-product="<?php echo $product->id; ?>" 
                                data-kit="<?php echo $idpaquete; ?>">
                            <i class="fas fa-plus"></i> AGREGAR
                        </button>
                    </div>
      			</div>
			</td>
	</tr>
	<?php endforeach;?>
</table>
<?php
}else{
	echo "<br><p class='alert alert-danger'>No se encontro el producto</p>";
}
?>
<hr><br>
<?php else:
?>
<?php endif; ?>