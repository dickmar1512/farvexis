<?php
if(isset($_POST["clear_all"])){
    unset($_SESSION["traspaso"]);
} else if(isset($_POST["index"])) {
    $idx = intval($_POST["index"]);
    $cart = $_SESSION["traspaso"];
    if(isset($cart[$idx])){
        array_splice($cart, $idx, 1);
        $_SESSION["traspaso"] = $cart;
    }
}
?>
