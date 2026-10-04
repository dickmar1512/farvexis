<?php 
header('Content-Type: application/json');

// $category = Categorydata::getById($_GET["id"]);
// $products = ProductData::getAllByCategoryId($category->id);
// foreach ($products as $product) {
// 	$product->del_category();
// }
//$category->del();

$categoria = new CategoryData();
$categoria->id = $_GET["id"];
$categoria->status = ($_GET["accion"]=='D') ? 0 : 1;
$categoria->del();
echo json_encode(["success" => true]);
exit;
?>