<?php
if (isset($_GET["product_id"])) {
    $cart = $_SESSION["cart"];
    $newcart = array();
    foreach ($cart as $c) {
        if ($c["product_id"] != $_GET["product_id"]) {
            $newcart[] = $c;
        }
    }
    $_SESSION["cart"] = $newcart;
} else {
    unset($_SESSION["cart"]);
}
echo json_encode(["status" => "success"]);
?>
