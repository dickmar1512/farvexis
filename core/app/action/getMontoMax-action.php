<?php
header('Content-Type: application/json');
// Recibir y decodificar los datos JSON
$products = SellData::getSellsUnBoxed();
if (count($products) > 0) {
    $total_total = 0;

    foreach ($products as $sell):
        $operations = OperationData::getAllProductsBySellId($sell->id);
		$total = 0;

        foreach ($operations as $operation) {
            $product = $operation->getProduct();
			$total += $operation->q * ($operation->prec_alt - $operation->descuento);
		}

        $notacomprobar = $sell->serie . "-" . $sell->comprobante; 
		$probar = NotData::getByIdComprobado($notacomprobar);
        $impNotaCredito = $probar->sumImpVenta ?? 0;
        $total -= $impNotaCredito;
		$total_total += $total;
    endforeach;    
}
echo json_encode(["success" => true, "montocaja" => $total_total]);
exit;
?>
