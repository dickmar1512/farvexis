<?php
header('Content-Type: application/json; charset=utf-8');

$num_succ = 0;
$process = false;
$errors = array();

$igv_tipo = $_POST["igv_tipo"] ?? "20";

if (!isset($_SESSION["cart"])) {
	$product = array(
		"product_id" => $_POST["product_id"] ?? null,
		"q" => $_POST["q"] ?? 1,
		"precio_unitario" => $_POST["precio_unitario"] ?? 0,
		"product_name" => $_POST["product_name"] ?? "",
		"descripcion" => $_POST["descripcion"] ?? "",
		"descuento" => $_POST["descuento"] ?? 0,
		"idpaquete" => $_POST["idpaquete"] ?? "X",
		"igv_tipo" => $igv_tipo
	);
	$_SESSION["cart"] = array($product);
	$cart = $_SESSION["cart"];
	foreach ($cart as $c) {
		$product2 = ProductData::getById($c["product_id"]);
		$q = $product2 ? $product2->stock : 0;
		$is_stock = $product2 ? $product2->is_stock : 0;

		if ($c["q"] <= $q or $is_stock == 0) {
			$num_succ++;
		} else {
			$error = array("product_id" => $c["product_id"], "message" => "No hay suficiente cantidad de producto en inventario.");
			$errors[] = $error;
		}
	}

	if ($num_succ == count($cart)) {
		$process = true;
	}

	if ($process == false) {
		unset($_SESSION["cart"]);
		echo json_encode(["status" => "error", "message" => $errors[0]["message"] ?? "Error de inventario"]);
		exit;
	}
} else {
	$found = false;
	$cart = $_SESSION["cart"];
	$index = 0;

	$product_id = $_POST["product_id"] ?? null;
	$product2 = ProductData::getById($product_id);
	$q = $product2 ? $product2->stock : 0;
	$is_stock = $product2 ? $product2->is_stock : 0;
	$can = true;

	$post_q = $_POST["q"] ?? 1;

	if ($post_q <= $q or $is_stock == 0) {
	} else {
		$can = false;
		echo json_encode(["status" => "error", "message" => "No hay suficiente cantidad de producto en inventario."]);
		exit;
	}

	if ($can == true) {
		foreach ($cart as $c) {
			if ($c["product_id"] == $product_id && ($c["igv_tipo"] ?? "20") == $igv_tipo) {
				$found = true;
				break;
			}
			$index++;
		}

		if ($found == true) {
			$q1 = $cart[$index]["q"];
			$q2 = $post_q;
			$cart[$index]["q"] = $q1 + $q2;
			$_SESSION["cart"] = $cart;
		}

		if ($found == false) {
			$nc = count($cart);
			$product = array(
				"product_id" => $product_id,
				"q" => $post_q,
				"precio_unitario" => $_POST["precio_unitario"] ?? 0,
				"descripcion" => $_POST["descripcion"] ?? "",
				"descuento" => $_POST["descuento"] ?? 0,
				"idpaquete" => $_POST["idpaquete"] ?? "X",
				"igv_tipo" => $igv_tipo
			);
			$cart[$nc] = $product;
			$_SESSION["cart"] = $cart;
		}
	}
}

echo json_encode(["status" => "success"]);
exit;
?>
