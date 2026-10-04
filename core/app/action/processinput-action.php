<?php

if(count($_POST)>0){

 $op = new OperationData();
 $op->product_id = $_POST["product_id"] ;
 $op->operation_type_id=OperationTypeData::getByName("entrada")->id;
if(OperationTypeData::getByName("entrada")->name=="entrada"){
	$op->sell_id="NULL";
}
 $product = ProductData::getById($_POST["product_id"]);
 $op->q= $_POST["q"];
 $op->cu = $product->price_in;
 $op->prec_alt = $product->price_in;
 $op->descuento = 0;

if($_POST["is_oficial"]=="1"){
	$op->is_oficial = 1;
}else{	
	$op->is_oficial = 0;
}

$add = $op->add();
if (!$add[0]) {
	throw new RuntimeException('No se pudo registrar la entrada de inventario.');
}

if ((int)$product->is_stock === 1) {
	$numLote = trim($_POST['num_lot'] ?? '');
	if ($numLote === '') {
		throw new InvalidArgumentException('El número de lote es obligatorio para productos con stock.');
	}

	LoteData::registerEntry(
		(int)$product->id,
		$numLote,
		(float)$_POST['q'],
		(int)$_SESSION['user_id'],
		(int)$add[1],
		!empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null,
		null,
		isset($_POST['costo_unitario']) ? (float)$_POST['costo_unitario'] : (float)$product->price_in,
		trim($_POST['ubicacion'] ?? '') ?: null
	);
}
if($op->is_oficial==1){
 print "<script>window.location='./?view=history&product_id=$_POST[product_id]';</script>";
}else{
  //[disabled] print "<script>window.location='./?view=historyn&product_id=$_POST[product_id]';</script>";

}

}

?>