<?php
/**
 * Gestión del carrito temporal para Salidas Diversas
 */
if(!isset($_SESSION["cart_sd"])) {
    $_SESSION["cart_sd"] = array();
}

$op = $_POST["op"] ?? "";

if($op == "add") {
    $product_id = $_POST["product_id"];
    $q = $_POST["q"];
    
    // Si ya existe, actualizar cantidad
    $found = false;
    foreach($_SESSION["cart_sd"] as &$item) {
        if($item["product_id"] == $product_id) {
            $item["q"] += $q;
            $found = true;
            break;
        }
    }
    
    if(!$found) {
        $product = ProductData::getById($product_id);
        $_SESSION["cart_sd"][] = array(
            "product_id" => $product_id,
            "name" => $product->name,
            "laboratorio" => $product->laboratorio,
            "q" => $q
        );
    }
    echo "ok";
}

if($op == "remove") {
    $product_id = $_POST["product_id"];
    $new_cart = array();
    foreach($_SESSION["cart_sd"] as $item) {
        if($item["product_id"] != $product_id) {
            $new_cart[] = $item;
        }
    }
    $_SESSION["cart_sd"] = $new_cart;
    echo "ok";
}

if($op == "clear") {
    $_SESSION["cart_sd"] = array();
    echo "ok";
}
?>
