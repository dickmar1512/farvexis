<?php
if(!isset($_SESSION["traspaso"])){
	$_SESSION["traspaso"] = array();
}

$cart = $_SESSION["traspaso"];
$product_id = $_POST["product_id"];
$q = $_POST["q"];

$found = false;
for($i=0; $i<count($cart); $i++){
    if($cart[$i]["product_id"] == $product_id){
        $cart[$i]["q"] += $q;
        $found = true;
        break;
    }
}

if(!$found){
    $item = array("product_id" => $product_id, "q" => $q);
    array_push($cart, $item);
}

$_SESSION["traspaso"] = $cart;
?>
